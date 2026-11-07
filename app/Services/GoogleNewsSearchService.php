<?php

namespace App\Services;

use App\Models\News;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleNewsSearchService
{
    protected Client $httpClient;

    /**
     * Category mappings for Google News RSS sections.
     */
    protected array $categoryTopicMap = [
        'technology' => 'TECHNOLOGY',
        'technology-bn' => 'TECHNOLOGY',
        'business' => 'BUSINESS',
        'business-bn' => 'BUSINESS',
        'sports' => 'SPORTS',
        'sports-bn' => 'SPORTS',
        'health' => 'HEALTH',
        'health-bn' => 'HEALTH',
        'climate' => 'SCIENCE',
        'climate-bn' => 'SCIENCE',
        'national' => 'WORLD',
        'national-bn' => 'NATION',
        'culture' => 'ENTERTAINMENT',
        'culture-bn' => 'ENTERTAINMENT',
    ];

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
            ],
        ]);
    }

    /**
     * Fetch trending news stories from Google News RSS for a category or custom search query.
     *
     * @param string $categorySlug
     * @param string $language 'en' or 'bn'
     * @param string|null $customQuery
     * @param int $limit
     * @return Collection
     */
    public function fetchNews(string $categorySlug = 'technology', string $language = 'en', ?string $customQuery = null, int $limit = 10): Collection
    {
        $url = $this->buildRssUrl($categorySlug, $language, $customQuery);

        try {
            $response = $this->httpClient->get($url);
            $xmlString = $response->getBody()->getContents();

            return $this->parseRssFeed($xmlString, $language, $limit);
        } catch (\Exception $e) {
            Log::warning("GoogleNewsSearchService: Failed to fetch from {$url}. Error: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Fetch the best candidate story that hasn't been published yet.
     */
    public function fetchTopUniqueCandidate(string $categorySlug = 'technology', string $language = 'en', ?string $customQuery = null): ?array
    {
        $stories = $this->fetchNews($categorySlug, $language, $customQuery, 15);

        if ($stories->isEmpty()) {
            return null;
        }

        // Check against existing news in DB
        $recentSlugs = News::latest()->take(100)->pluck('slug')->toArray();
        $recentTitles = News::latest()->take(100)->pluck('title')->toArray();

        foreach ($stories as $story) {
            $candidateSlug = Str::slug($story['clean_title']);

            // Check if slug or title is very similar to already published stories
            $isDuplicate = false;
            foreach ($recentSlugs as $existingSlug) {
                if (Str::contains($candidateSlug, $existingSlug) || Str::contains($existingSlug, $candidateSlug)) {
                    $isDuplicate = true;
                    break;
                }
            }

            if (!$isDuplicate) {
                foreach ($recentTitles as $existingTitle) {
                    similar_text(mb_strtolower($story['clean_title']), mb_strtolower($existingTitle), $percent);
                    if ($percent > 70) {
                        $isDuplicate = true;
                        break;
                    }
                }
            }

            if (!$isDuplicate) {
                return $story;
            }
        }

        // If all candidates somewhat matched, return the first one as fallback
        return $stories->first();
    }

    /**
     * Build the Google News RSS endpoint URL.
     */
    protected function buildRssUrl(string $categorySlug, string $language, ?string $customQuery): string
    {
        $cleanSlug = strtolower(str_replace('-bn', '', $categorySlug));

        if ($language === 'bn') {
            $hl = 'bn';
            $gl = 'BD';
            $ceid = 'BD:bn';
        } else {
            $hl = 'en-US';
            $gl = 'US';
            $ceid = 'US:en';
        }

        if (!empty($customQuery)) {
            $encodedQuery = urlencode(trim($customQuery));
            return "https://news.google.com/rss/search?q={$encodedQuery}&hl={$hl}&gl={$gl}&ceid={$ceid}";
        }

        $topic = $this->categoryTopicMap[$cleanSlug] ?? null;

        if ($topic) {
            return "https://news.google.com/rss/headlines/section/topic/{$topic}?hl={$hl}&gl={$gl}&ceid={$ceid}";
        }

        return "https://news.google.com/rss?hl={$hl}&gl={$gl}&ceid={$ceid}";
    }

    /**
     * Parse the RSS XML string into a structured Collection.
     */
    protected function parseRssFeed(string $xmlString, string $language, int $limit): Collection
    {
        $items = collect();

        // Suppress XML parsing errors and handle gracefully
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false || !isset($xml->channel->item)) {
            return $items;
        }

        $count = 0;
        foreach ($xml->channel->item as $entry) {
            if ($count >= $limit) {
                break;
            }

            $rawTitle = trim((string) $entry->title);
            $link = trim((string) $entry->link);
            $pubDate = trim((string) $entry->pubDate);
            $source = isset($entry->source) ? trim((string) $entry->source) : 'Verified News Desk';
            $descriptionHtml = (string) $entry->description;

            // Separate title and source suffix (e.g. "Headline - Source Name")
            $cleanTitle = $rawTitle;
            if (preg_match('/^(.*?)\s*-\s*([^-]+)$/u', $rawTitle, $matches)) {
                $cleanTitle = trim($matches[1]);
                if (empty($source) || $source === 'Verified News Desk') {
                    $source = trim($matches[2]);
                }
            }

            // Extract plain text snippet from description if available
            $snippet = trim(strip_tags($descriptionHtml));
            $snippet = preg_replace('/\s+/', ' ', $snippet);

            $items->push([
                'raw_title' => $rawTitle,
                'clean_title' => $cleanTitle,
                'source' => $source ?: 'Popular News Wire',
                'link' => $link,
                'pub_date' => $pubDate,
                'snippet' => Str::limit($snippet, 300),
                'language' => $language,
            ]);

            $count++;
        }

        return $items;
    }
}
