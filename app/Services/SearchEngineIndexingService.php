<?php

namespace App\Services;

use App\Models\News;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SearchEngineIndexingService
{
    protected Client $httpClient;
    protected string $host;
    protected string $indexNowKey;
    protected string $keyLocation;

    public function __construct()
    {
        $appUrl = config('app.url', 'https://extremis.top');
        $this->host = parse_url($appUrl, PHP_URL_HOST) ?: 'extremis.top';
        $this->indexNowKey = config('services.indexnow.key', env('INDEXNOW_KEY', 'extremis2026indexnow'));
        $this->keyLocation = rtrim($appUrl, '/') . "/{$this->indexNowKey}.txt";

        $this->httpClient = new Client([
            'timeout' => 6,
            'connect_timeout' => 4,
            'headers' => [
                'User-Agent' => 'ExtremisNews-SearchIndexer/1.0',
                'Accept' => 'application/json, text/plain, */*',
            ],
            'http_errors' => false,
        ]);
    }

    /**
     * Notify all search engines about a newly created or updated news article.
     * Submits directly to IndexNow (Bing, Yandex, Seznam, Naver) and pings Google & Bing sitemaps.
     */
    public function notifySearchEngines(News $news): array
    {
        $postUrl = route('news-details', $news->slug);

        $results = [
            'post_id' => $news->id,
            'url' => $postUrl,
            'indexnow' => $this->submitToIndexNow([$postUrl]),
            'google_ping' => $this->pingGoogleSitemaps(),
            'bing_ping' => $this->pingBingSitemaps(),
        ];

        Log::info("Search engine indexing notification completed for News #{$news->id} ({$news->slug})", [
            'indexnow_success' => $results['indexnow']['success'] ?? false,
            'google_ping_status' => $results['google_ping']['status_code'] ?? null,
        ]);

        return $results;
    }

    /**
     * Batch submit multiple URLs to the IndexNow protocol API.
     */
    public function submitToIndexNow(array $urls): array
    {
        if (empty($urls)) {
            return ['success' => false, 'message' => 'No URLs provided'];
        }

        $payload = [
            'host' => $this->host,
            'key' => $this->indexNowKey,
            'keyLocation' => $this->keyLocation,
            'urlList' => array_values(array_unique($urls)),
        ];

        try {
            // IndexNow API shared endpoint
            $response = $this->httpClient->post('https://api.indexnow.org/indexnow', [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            // HTTP 200 (OK) or 202 (Accepted) indicates successful submission
            $isSuccess = in_array($statusCode, [200, 202]);

            return [
                'success' => $isSuccess,
                'status_code' => $statusCode,
                'urls_submitted' => count($urls),
                'endpoint' => 'https://api.indexnow.org/indexnow',
            ];
        } catch (\Exception $e) {
            Log::warning("IndexNow API error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'urls_submitted' => count($urls),
            ];
        }
    }

    /**
     * Ping Google with the production XML sitemaps to trigger immediate recrawl.
     */
    public function pingGoogleSitemaps(): array
    {
        $sitemapUrl = route('sitemap');
        $newsSitemapUrl = route('sitemap.news');

        $results = [];

        foreach ([$sitemapUrl, $newsSitemapUrl] as $url) {
            try {
                $pingEndpoint = 'https://www.google.com/ping?sitemap=' . urlencode($url);
                $response = $this->httpClient->get($pingEndpoint);
                $results[] = [
                    'sitemap' => $url,
                    'status_code' => $response->getStatusCode(),
                    'success' => $response->getStatusCode() < 400,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'sitemap' => $url,
                    'error' => $e->getMessage(),
                    'success' => false,
                ];
            }
        }

        return [
            'success' => !empty($results[0]['success']) || !empty($results[1]['success']),
            'details' => $results,
        ];
    }

    /**
     * Ping Bing with the main production XML sitemap.
     */
    public function pingBingSitemaps(): array
    {
        $sitemapUrl = route('sitemap');

        try {
            $pingEndpoint = 'https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl);
            $response = $this->httpClient->get($pingEndpoint);
            return [
                'success' => $response->getStatusCode() < 400,
                'status_code' => $response->getStatusCode(),
                'sitemap' => $sitemapUrl,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'sitemap' => $sitemapUrl,
            ];
        }
    }
}
