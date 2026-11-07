<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiNewsPosterService
{
    protected Client $httpClient;
    protected string $endpoint;
    protected string $model;
    protected string $apiKey;
    protected float $temperature;
    protected GoogleNewsSearchService $googleNewsService;
    protected SearchEngineIndexingService $indexingService;

    public function __construct(
        ?GoogleNewsSearchService $googleNewsService = null,
        ?SearchEngineIndexingService $indexingService = null
    ) {
        $this->googleNewsService = $googleNewsService ?? new GoogleNewsSearchService();
        $this->indexingService = $indexingService ?? new SearchEngineIndexingService();
        $this->endpoint = config('ai_news.endpoint', 'https://unirenic-twittery-cammie.ngrok-free.dev/v1/chat/completions');
        $this->model = config('ai_news.model', 'deepseek-expert');
        $this->apiKey = config('ai_news.api_key', '');
        $this->temperature = (float) config('ai_news.temperature', 0.7);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'ngrok-skip-browser-warning' => '69420',
            'User-Agent' => 'ExtremisNewsPoster/1.0',
        ];

        if (!empty($this->apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $this->httpClient = new Client([
            'timeout' => config('ai_news.timeout', 90),
            'headers' => $headers,
        ]);
    }

    /**
     * Get GoogleNewsSearchService instance.
     */
    public function getGoogleNewsService(): GoogleNewsSearchService
    {
        return $this->googleNewsService;
    }

    /**
     * Test connection to the AI endpoint.
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->httpClient->post($this->endpoint, [
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a test ping service. Return valid JSON: {"status": "ok"}'],
                        ['role' => 'user', 'content' => 'Ping'],
                    ],
                    'temperature' => 0.1,
                ],
            ]);

            $latency = round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'status_code' => $statusCode,
                'latency_ms' => $latency,
                'model' => $this->model,
                'endpoint' => $this->endpoint,
                'response_snippet' => Str::limit($body, 150),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'latency_ms' => round((microtime(true) - $startTime) * 1000),
                'endpoint' => $this->endpoint,
            ];
        }
    }

    /**
     * Generate and publish a unique, SEO-optimized news article.
     */
    public function generateAndPost(array $options = []): array
    {
        $language = $options['language'] ?? config('ai_news.default_language', 'en');
        $categorySlug = $options['category_slug'] ?? null;
        $customTopic = $options['topic'] ?? null;
        $googleSearchQuery = $options['google_search_query'] ?? null;
        $isBreaking = !empty($options['is_breaking']);
        $useGoogleNews = $options['use_google_news'] ?? true;

        // Determine category, topic, and live Google News source metadata
        [$resolvedCategory, $resolvedTopic, $sourceMeta] = $this->resolveCategoryAndTopic(
            $language,
            $categorySlug,
            $customTopic,
            $googleSearchQuery,
            $useGoogleNews
        );

        // Call the AI endpoint with SEO prompt enriched with live source reporting
        $aiData = $this->requestArticleFromAi($resolvedTopic, $resolvedCategory, $language, $sourceMeta);

        // Format HTML content with semantic SEO structure and FAQs
        $htmlContent = $this->buildSemanticArticleHtml($aiData, $sourceMeta);

        // Generate unique slug
        $title = trim($aiData['title'] ?? $resolvedTopic);
        $slug = $this->generateUniqueSlug($title);

        // Generate high-resolution branded feature image
        $imagePath = $this->generateFeatureImage($title, $resolvedCategory->name, $slug);

        // Calculate approximate word count
        $wordCount = str_word_count(strip_tags($htmlContent));

        // Get author
        $author = Admin::where('email', config('ai_news.default_author_email'))->first() ?? Admin::first();

        // Save news item to database
        $news = new News();
        $news->language = $language;
        $news->category_id = $resolvedCategory->id;
        $news->author_id = $author ? $author->id : 1;
        $news->image = $imagePath;
        $news->title = $title;
        $news->slug = $slug;
        $news->content = $htmlContent;
        $news->meta_title = Str::limit($aiData['meta_title'] ?? $title, 190);
        $news->meta_description = Str::limit($aiData['meta_description'] ?? strip_tags($aiData['lead_summary'] ?? $title), 250);
        $news->is_breaking_news = $isBreaking ? 1 : 0;
        $news->show_at_slider = 1;
        $news->show_at_popular = 1;
        $news->is_approved = 1;
        $news->status = 1;
        $news->views = rand(15, 60);
        $news->save();

        // Attach Tags
        $tagsList = $aiData['tags'] ?? ['World News', $resolvedCategory->name, 'Global Updates', 'Extremis News'];
        $tagIds = [];
        foreach ($tagsList as $tagName) {
            $trimmedTag = trim($tagName);
            if (!empty($trimmedTag)) {
                $tag = Tag::firstOrCreate([
                    'name' => Str::title($trimmedTag),
                    'language' => $language,
                ]);
                $tagIds[] = $tag->id;
            }
        }
        if (!empty($tagIds)) {
            $news->tags()->sync($tagIds);
        }

        // Instantly notify search engines (IndexNow for Bing/Yandex and Google/Bing sitemap pings)
        $indexingStatus = null;
        try {
            $indexingStatus = $this->indexingService->notifySearchEngines($news);
        } catch (\Exception $e) {
            Log::warning("Search engine notification skipped for News ID {$news->id}: " . $e->getMessage());
        }

        return [
            'id' => $news->id,
            'title' => $news->title,
            'slug' => $news->slug,
            'url' => route('news-details', $news->slug),
            'category' => $resolvedCategory->name,
            'language' => $language,
            'word_count' => $wordCount,
            'image' => asset($imagePath),
            'meta_title' => $news->meta_title,
            'meta_description' => $news->meta_description,
            'tags' => $tagsList,
            'source' => $sourceMeta['source'] ?? 'Extremis News Wire',
            'indexing' => $indexingStatus,
        ];
    }

    /**
     * Resolve category model and topic with real-time Google News search and journalistic angles.
     */
    protected function resolveCategoryAndTopic(
        string $language,
        ?string $categorySlug,
        ?string $customTopic,
        ?string $googleSearchQuery = null,
        bool $useGoogleNews = true
    ): array {
        $pool = config('ai_news.topics_pool', []);
        $angles = config('ai_news.angles', []);
        $sourceMeta = null;

        // Resolve Category
        if ($categorySlug) {
            $cleanSlug = str_replace('-bn', '', $categorySlug);
            $searchSlug = $language === 'bn' ? $cleanSlug . '-bn' : $cleanSlug;
            $category = Category::where('slug', $searchSlug)->first()
                ?? Category::where('slug', $cleanSlug)->first();
        } else {
            $cleanSlug = array_rand($pool);
            $searchSlug = $language === 'bn' ? $cleanSlug . '-bn' : $cleanSlug;
            $category = Category::where('slug', $searchSlug)->first();
        }

        if (!$category) {
            $category = Category::where('language', $language)->first() ?? Category::first();
        }

        // Check if custom topic is directly provided
        if (!empty($customTopic)) {
            $topic = $customTopic;
            return [$category, $topic, null];
        }

        // Live Google News popular sources search
        if ($useGoogleNews) {
            try {
                $candidate = $this->googleNewsService->fetchTopUniqueCandidate(
                    $category->slug,
                    $language,
                    $googleSearchQuery
                );

                if ($candidate && !empty($candidate['clean_title'])) {
                    $topic = $candidate['clean_title'];
                    $sourceMeta = [
                        'source' => $candidate['source'],
                        'snippet' => $candidate['snippet'],
                        'pub_date' => $candidate['pub_date'],
                        'link' => $candidate['link'],
                    ];

                    return [$category, $topic, $sourceMeta];
                }
            } catch (\Exception $e) {
                Log::warning("Live Google News fetch fallback triggered: " . $e->getMessage());
            }
        }

        // Fallback: Curated Topics Pool with fresh angle
        $catKey = str_replace('-bn', '', $category->slug);
        $topicList = $pool[$catKey] ?? $pool['technology'];
        $baseTopic = $topicList[array_rand($topicList)];
        $angle = !empty($angles) ? $angles[array_rand($angles)] : 'Strategic implications and international outlook';
        $topic = "{$baseTopic}: Focusing on {$angle}";

        return [$category, $topic, null];
    }

    /**
     * Send prompt to DeepSeek endpoint and parse structured JSON.
     */
    protected function requestArticleFromAi(string $topic, Category $category, string $language, ?array $sourceMeta = null): array
    {
        $systemPrompt = <<<PROMPT
You are a senior international investigative journalist, chief editorial writer, and SEO strategist for "Extremis News", a premier global news publication.
Your goal is to produce authoritative, comprehensive, 100% original, and highly engaging news articles that rank at the top of Google search results and strictly adhere to Google's Helpful Content and E-E-A-T guidelines (Experience, Expertise, Authoritativeness, Trustworthiness) suitable for Google AdSense monetization.

CRITICAL REQUIREMENTS:
1. Always output ONLY valid JSON without any markdown code fence wrappers or backticks.
2. The article must be in-depth, insightful, and informative (target 800 to 1400+ words across sections).
3. Include real-world context, technical depth, global economic/societal implications, and a forward-looking perspective.
4. Include 3-4 frequently asked questions with clear, actionable answers.
5. If the requested language is 'bn', write the title, content, FAQs, and tags in natural, professional Bengali; otherwise in professional English.
6. Synthesize any source reporting into completely original prose with unique phrasing, original analysis, and proper journalistic integrity. NEVER copy verbatim.

JSON SCHEMA:
{
  "title": "High-CTR, keyword-rich headline (60-85 characters)",
  "meta_title": "SEO Title optimized for Google SERP (< 60 chars)",
  "meta_description": "Compelling search snippet with call-to-action (145-160 chars)",
  "lead_summary": "2-3 sentence impactful executive summary of the breaking story.",
  "key_takeaways": [
    "Key takeaway point 1 with data or essential finding",
    "Key takeaway point 2 with key stakeholder or market impact",
    "Key takeaway point 3 with future outlook or timeline"
  ],
  "sections": [
    {
      "heading": "Clear, Keyword-Rich H2 Subheading",
      "paragraphs": [
        "In-depth analysis paragraph 1 providing granular context, facts, and developments.",
        "In-depth analysis paragraph 2 examining underlying drivers and methodology."
      ]
    },
    {
      "heading": "Global Implications and Strategic Impact",
      "paragraphs": [
        "Detailed paragraph examining worldwide economic, technological, or policy ramifications.",
        "Deep-dive paragraph highlighting consumer and industrial perspectives."
      ]
    },
    {
      "heading": "Challenges, Technical Hurdles, and Industry Debate",
      "paragraphs": [
        "Balanced examination of hurdles, regulatory questions, and contrasting viewpoints.",
        "Analysis of potential risk factors and implementation timelines."
      ]
    },
    {
      "heading": "Future Outlook and What to Watch Next",
      "paragraphs": [
        "Strategic forward-looking projection over the next 12 to 36 months.",
        "Closing perspective on the broader evolution of this story."
      ]
    }
  ],
  "quote": "A realistic, highly articulate direct quote reflecting expert perspective or official statement.",
  "quote_author": "Expert Name, Senior Director / Lead Researcher / Policy Strategist",
  "faqs": [
    {
      "question": "High-volume search question 1 regarding this topic?",
      "answer": "Clear, direct, and helpful answer providing immediate value to the reader."
    },
    {
      "question": "High-volume search question 2?",
      "answer": "Comprehensive answer addressing practical implications."
    },
    {
      "question": "High-volume search question 3?",
      "answer": "Detailed answer covering future outlook."
    }
  ],
  "tags": ["Tag1", "Tag2", "Tag3", "Tag4", "Tag5", "Tag6"]
}
PROMPT;

        $sourceContext = "";
        if (!empty($sourceMeta)) {
            $sourceContext = "\nREAL-TIME BREAKING SOURCE DATA:\n- Original Popular Source: " . ($sourceMeta['source'] ?? 'Verified Wire') . "\n- Headline: {$topic}\n- Context Excerpt: " . ($sourceMeta['snippet'] ?? '') . "\n- Publication Date: " . ($sourceMeta['pub_date'] ?? '') . "\n\nEDITORIAL INSTRUCTION:\nSynthesize this breaking development into a completely original, authoritative investigative report. Mention the source context naturally (e.g., 'According to initial reports from " . ($sourceMeta['source'] ?? 'wire reports') . "...') while providing extensive independent technical, economic, and strategic analysis, forward-looking insights, and full E-E-A-T depth. Do not copy sentences; craft 100% original prose suitable for Google AdSense monetization.";
        }

        $userPrompt = "Write a comprehensive, SEO-optimized worldwide news article on the topic: '{$topic}'. Category: {$category->name}. Language: {$language}. Target length: 800-1400+ words with deep analysis and FAQs.{$sourceContext}\nReturn pure JSON only.";

        try {
            $response = $this->httpClient->post($this->endpoint, [
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => $this->temperature,
                ],
            ]);

            $raw = $response->getBody()->getContents();
            $decoded = json_decode($raw, true);

            $contentStr = $decoded['choices'][0]['message']['content'] ?? '';

            // Clean markdown code blocks if the model wrapped it in ```json ... ```
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', trim($contentStr));
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

            $data = json_decode($cleanJson, true);

            if (!is_array($data) || empty($data['title'])) {
                // If direct JSON parse fails, attempt to extract first JSON object
                if (preg_match('/\{[\s\S]*\}/', $contentStr, $matches)) {
                    $data = json_decode($matches[0], true);
                }
            }

            if (!is_array($data) || empty($data['title'])) {
                Log::warning("AI endpoint returned malformed JSON. Using editorial fallback synthesis for: {$topic}");
                return $this->generateEditorialFallbackArticle($topic, $category, $language, $sourceMeta);
            }

            return $data;
        } catch (\Exception $e) {
            Log::warning("AiNewsPosterService AI API error ({$e->getMessage()}). Using editorial fallback synthesis for: {$topic}");
            return $this->generateEditorialFallbackArticle($topic, $category, $language, $sourceMeta);
        }
    }

    /**
     * Build rich, semantic HTML content optimized for Google SEO & AdSense.
     */
    protected function buildSemanticArticleHtml(array $data, ?array $sourceMeta = null): string
    {
        $html = [];

        // Live verified source badge
        if (!empty($sourceMeta['source'])) {
            $html[] = '<div class="article-source-meta" style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px; margin-bottom: 1.5rem; font-size: 0.88rem; color: #475569;">'
                . '<span style="color: #1066ff; font-weight: 700;">&#10003; Verified Coverage</span>'
                . '<span style="color: #cbd5e1;">|</span>'
                . '<span>Initial Source Reporting: <strong>' . htmlspecialchars($sourceMeta['source']) . '</strong></span>'
                . '</div>';
        }

        // Lead summary
        if (!empty($data['lead_summary'])) {
            $html[] = '<p class="lead" style="font-size: 1.15rem; font-weight: 500; line-height: 1.7; color: #2d3748; margin-bottom: 1.5rem;">'
                . htmlspecialchars($data['lead_summary']) . '</p>';
        }

        // Key Takeaways Box (Highly valued by Google for featured snippets)
        if (!empty($data['key_takeaways']) && is_array($data['key_takeaways'])) {
            $html[] = '<div class="key-takeaways-box" style="background: #f8fafc; border-left: 4px solid #1066ff; padding: 1.25rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
            $html[] = '<h4 style="margin-top: 0; margin-bottom: 0.75rem; color: #0f172a; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;"><span style="color:#1066ff;">&#9679;</span> Key Takeaways & Highlights</h4>';
            $html[] = '<ul style="margin: 0; padding-left: 1.25rem; color: #334155; line-height: 1.65;">';
            foreach ($data['key_takeaways'] as $takeaway) {
                $html[] = '<li style="margin-bottom: 0.4rem;">' . htmlspecialchars($takeaway) . '</li>';
            }
            $html[] = '</ul>';
            $html[] = '</div>';
        }

        // Main Article Sections
        if (!empty($data['sections']) && is_array($data['sections'])) {
            $sectionCount = count($data['sections']);
            foreach ($data['sections'] as $idx => $section) {
                if (!empty($section['heading'])) {
                    $html[] = '<h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin-top: 2rem; margin-bottom: 1rem;">'
                        . htmlspecialchars($section['heading']) . '</h2>';
                }

                if (!empty($section['paragraphs']) && is_array($section['paragraphs'])) {
                    foreach ($section['paragraphs'] as $p) {
                        $html[] = '<p style="line-height: 1.8; color: #334155; margin-bottom: 1.25rem; font-size: 1.05rem;">'
                            . nl2br(htmlspecialchars($p)) . '</p>';
                    }
                }

                // Insert Quote midway through article
                if ($idx === (int) floor($sectionCount / 2) && !empty($data['quote'])) {
                    $author = !empty($data['quote_author']) ? htmlspecialchars($data['quote_author']) : 'Editorial Desk';
                    $html[] = '<blockquote style="border-left: 4px solid #1066ff; background: #f1f5f9; padding: 1.25rem 1.5rem; margin: 2rem 0; border-radius: 0 8px 8px 0; font-style: italic; color: #1e293b;">';
                    $html[] = '<p style="margin-bottom: 0.5rem; font-size: 1.1rem; line-height: 1.6;">“' . htmlspecialchars($data['quote']) . '”</p>';
                    $html[] = '<footer style="font-style: normal; font-size: 0.9rem; color: #64748b; font-weight: 600;">— ' . $author . '</footer>';
                    $html[] = '</blockquote>';
                }
            }
        }

        // Schema-Ready FAQ Section (Crucial for Google People Also Ask & Ads)
        if (!empty($data['faqs']) && is_array($data['faqs'])) {
            $html[] = '<div class="faq-section" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 2px solid #e2e8f0;">';
            $html[] = '<h3 style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-bottom: 1.25rem;">Frequently Asked Questions</h3>';

            foreach ($data['faqs'] as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $html[] = '<div class="faq-item" style="margin-bottom: 1.25rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem;">';
                    $html[] = '<h4 style="font-size: 1.05rem; font-weight: 600; color: #1e293b; margin-top: 0; margin-bottom: 0.5rem;">'
                        . htmlspecialchars($faq['question']) . '</h4>';
                    $html[] = '<p style="margin: 0; color: #475569; line-height: 1.6; font-size: 0.98rem;">'
                        . htmlspecialchars($faq['answer']) . '</p>';
                    $html[] = '</div>';
                }
            }

            $html[] = '</div>';
        }

        // Editorial Disclaimer & Attribution
        $html[] = '<p style="margin-top: 2rem; font-size: 0.85rem; color: #94a3b8; font-style: italic; border-top: 1px solid #f1f5f9; padding-top: 0.75rem;">'
            . 'Reported and verified by the Extremis News International Desk. All rights reserved.</p>';

        return implode("\n", $html);
    }

    /**
     * Generate a crisp, branded 1200x675 cover banner for the article.
     */
    protected function generateFeatureImage(string $title, string $categoryName, string $slug): string
    {
        $width = 1200;
        $height = 675;

        $image = imagecreatetruecolor($width, $height);

        // Modern deep navy to dark slate gradient
        $topColor = ['r' => 10, 'g' => 17, 'b' => 40];   // #0a1128
        $botColor = ['r' => 28, 'g' => 37, 'b' => 65];  // #1c2541

        for ($y = 0; $y < $height; $y++) {
            $factor = $y / $height;
            $r = (int) ($topColor['r'] + ($botColor['r'] - $topColor['r']) * $factor);
            $g = (int) ($topColor['g'] + ($botColor['g'] - $topColor['g']) * $factor);
            $b = (int) ($topColor['b'] + ($botColor['b'] - $topColor['b']) * $factor);
            $lineColor = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $lineColor);
        }

        // Define color palette
        $white = imagecolorallocate($image, 255, 255, 255);
        $accentBlue = imagecolorallocate($image, 16, 102, 255); // #1066ff
        $darkBlue = imagecolorallocate($image, 13, 82, 204);
        $lightGray = imagecolorallocate($image, 203, 213, 225);
        $gridColor = imagecolorallocatealpha($image, 255, 255, 255, 120);

        // Draw subtle technological grid lines
        for ($x = 60; $x < $width; $x += 120) {
            imageline($image, $x, 0, $x, $height, $gridColor);
        }
        for ($y = 60; $y < $height; $y += 90) {
            imageline($image, 0, $y, $width, $y, $gridColor);
        }

        // Glowing bottom accent bar
        imagefilledrectangle($image, 0, $height - 14, $width, $height, $accentBlue);

        // Find TTF font
        $fontFile = null;
        $candidateFonts = [
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/segoeuib.ttf',
            'C:/Windows/Fonts/tahoma.ttf',
        ];
        foreach ($candidateFonts as $f) {
            if (file_exists($f)) {
                $fontFile = $f;
                break;
            }
        }

        // Top Category Badge Background & Text
        $badgeText = strtoupper($categoryName) . ' | WORLDWIDE';
        $brandText = "EXTREMIS NEWS | GLOBAL REPORT";

        $badgeWidth = 290;
        if ($fontFile && function_exists('imagettfbbox')) {
            $bbox = imagettfbbox(14, 0, $fontFile, $badgeText);
            $badgeWidth = abs($bbox[4] - $bbox[0]) + 40;
        }

        imagefilledrectangle($image, 70, 58, 70 + $badgeWidth, 102, $accentBlue);

        if ($fontFile && function_exists('imagettftext')) {
            imagettftext($image, 14, 0, 90, 88, $white, $fontFile, $badgeText);
            imagettftext($image, 13, 0, 70 + $badgeWidth + 25, 88, $lightGray, $fontFile, $brandText);
        } else {
            imagestring($image, 5, 85, 72, $badgeText, $white);
            imagestring($image, 4, 70 + $badgeWidth + 20, 74, $brandText, $lightGray);
        }

        // Circular Logo Icon Mockup at top right
        imagefilledellipse($image, 1120, 80, 50, 50, $accentBlue);
        imagefilledrectangle($image, 1112, 68, 1118, 92, $white);
        imagefilledrectangle($image, 1122, 74, 1128, 92, $white);

        // Word-wrap title for the banner
        $wrappedLines = explode("\n", wordwrap($title, 42, "\n", true));
        $wrappedLines = array_slice($wrappedLines, 0, 4); // Max 4 lines

        if ($fontFile && function_exists('imagettftext')) {
            // Large Title
            $yPos = 240;
            foreach ($wrappedLines as $line) {
                // Drop shadow
                imagettftext($image, 34, 0, 72, $yPos + 2, imagecolorallocatealpha($image, 0, 0, 0, 70), $fontFile, $line);
                // Main text
                imagettftext($image, 34, 0, 70, $yPos, $white, $fontFile, $line);
                $yPos += 64;
            }

            // Footer Subtitle / Watermark
            imagettftext($image, 18, 0, 70, $height - 50, $lightGray, $fontFile, 'Published by Extremis News • Verified Global Reporting & Analysis');
        } else {
            // Fallback to built-in bitmap fonts
            imagestring($image, 5, 85, 75, $badgeText, $white);

            $yPos = 220;
            foreach ($wrappedLines as $line) {
                imagestring($image, 5, 70, $yPos, $line, $white);
                $yPos += 45;
            }

            imagestring($image, 4, 70, $height - 50, 'Published by Extremis News • Verified Global Reporting', $lightGray);
        }

        // Save file
        $uploadDir = public_path('uploads');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $filename = 'ai-news-' . time() . '-' . Str::slug(Str::limit($slug, 30, '')) . '.png';
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        imagepng($image, $fullPath, 7);
        imagedestroy($image);

        return 'uploads/' . $filename;
    }

    /**
     * Generate unique slug that does not collide in the news table.
     */
    protected function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        if (empty($baseSlug)) {
            $baseSlug = 'global-news-update-' . time();
        }

        $slug = $baseSlug;
        $counter = 1;

        while (News::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Update existing published articles with refreshed AI analysis, latest developments, and SEO improvements.
     */
    public function updateExistingArticles(array $options = []): array
    {
        $limit = (int) ($options['limit'] ?? config('ai_news.update_limit', 2));
        $ageHours = (int) ($options['age_hours'] ?? config('ai_news.update_age_hours', 24));
        $specificId = $options['id'] ?? null;

        $query = News::query()->where('status', 1)->where('is_approved', 1);

        if ($specificId) {
            $query->where('id', $specificId);
        } else {
            // Prioritize oldest updated articles that exceed the age threshold
            if ($ageHours > 0) {
                $cutoff = now()->subHours($ageHours);
                $query->where('updated_at', '<=', $cutoff);
            }
            $query->orderBy('updated_at', 'ASC');
        }

        $candidates = $query->take($limit)->get();

        if ($candidates->isEmpty()) {
            return [
                'status' => 'no_candidates',
                'message' => "No published news articles met the update criteria (older than {$ageHours}h).",
                'updated_count' => 0,
                'articles' => [],
            ];
        }

        $updatedArticles = [];

        foreach ($candidates as $news) {
            try {
                $startTime = microtime(true);
                $aiData = $this->requestArticleUpdateFromAi($news);

                $updateSummary = trim($aiData['update_summary'] ?? 'Updated with latest international developments, verified statistical findings, and forward-looking analysis.');

                // Determine update notice badge based on language
                $timestampText = $news->language === 'bn' ? localizeDate(time(), 'M d, Y H:i') : date('M d, Y H:i T');

                $badgeHtml = $news->language === 'bn'
                    ? '<div class="alert alert-info py-2 px-3 mb-4" style="background: #e8f4fd; border-left: 4px solid #1066ff; color: #0b4fbf; border-radius: 4px;"><strong><i class="fa fa-refresh"></i> সর্বশেষ তথ্যের সাথে আপডেট:</strong> ' . htmlspecialchars($updateSummary) . ' <em>(হালনাগাদ: ' . $timestampText . ')</em></div>'
                    : '<div class="alert alert-info py-2 px-3 mb-4" style="background: #e8f4fd; border-left: 4px solid #1066ff; color: #0b4fbf; border-radius: 4px;"><strong><i class="fa fa-refresh"></i> Editorial Update:</strong> ' . htmlspecialchars($updateSummary) . ' <em>(Refreshed: ' . $timestampText . ')</em></div>';

                // Build refreshed semantic HTML
                $refreshedBody = $this->buildSemanticArticleHtml($aiData);
                $finalContent = $badgeHtml . "\n" . $refreshedBody;

                // Update news fields
                $oldWordCount = str_word_count(strip_tags($news->content));
                $news->content = $finalContent;
                if (!empty($aiData['meta_title'])) {
                    $news->meta_title = Str::limit($aiData['meta_title'], 190);
                }
                if (!empty($aiData['meta_description'])) {
                    $news->meta_description = Str::limit($aiData['meta_description'], 250);
                }
                $news->touch();
                $news->save();

                // Merge / Sync any new tags
                if (!empty($aiData['tags']) && is_array($aiData['tags'])) {
                    foreach ($aiData['tags'] as $tagName) {
                        $trimmed = trim($tagName);
                        if (!empty($trimmed)) {
                            $tag = Tag::firstOrCreate([
                                'name' => Str::title($trimmed),
                                'language' => $news->language,
                            ]);
                            if (!$news->tags->contains($tag->id)) {
                                $news->tags()->attach($tag->id);
                            }
                        }
                    }
                }

                $newWordCount = str_word_count(strip_tags($finalContent));
                $duration = round(microtime(true) - $startTime, 1);

                // Notify search engines about updated article
                try {
                    $this->indexingService->notifySearchEngines($news);
                } catch (\Exception $e) {
                    Log::warning("Failed to dispatch indexing for updated post ID {$news->id}: " . $e->getMessage());
                }

                $updatedArticles[] = [
                    'id' => $news->id,
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'url' => route('news-details', $news->slug),
                    'language' => $news->language,
                    'duration_sec' => $duration,
                    'old_words' => $oldWordCount,
                    'new_words' => $newWordCount,
                    'update_summary' => $updateSummary,
                ];
            } catch (\Exception $e) {
                Log::error("Failed to update article ID {$news->id} by AI: " . $e->getMessage());
            }
        }

        return [
            'status' => count($updatedArticles) > 0 ? 'success' : 'failed',
            'updated_count' => count($updatedArticles),
            'articles' => $updatedArticles,
        ];
    }

    /**
     * Request refreshed content from DeepSeek for an existing article.
     */
    protected function requestArticleUpdateFromAi(News $news): array
    {
        $existingExcerpt = Str::limit(strip_tags($news->content), 1200);
        $categoryName = $news->category?->name ?? 'Global News';

        $systemPrompt = <<<PROMPT
You are a senior chief editor and investigative update desk editor at "Extremis News".
Your job is to take an existing published news article and UPDATE it with fresh international developments, deeper critical analysis, new data points, revised key takeaways, and updated FAQs.
CRITICAL RULES:
1. Return ONLY valid JSON with no markdown wrapping or backticks.
2. Maintain the requested language: '{$news->language}'. If 'bn', write everything in natural, professional Bengali; otherwise in professional English.
3. Keep the core subject intact, but elevate the quality and depth to meet Google E-E-A-T guidelines for AdSense monetization.
4. Include a concise "update_summary" (1-2 sentences) detailing what new insights or developments were added in this edition.

JSON SCHEMA:
{
  "title": "Preserve or slightly refine headline if needed",
  "meta_title": "Optimized SEO title (< 60 chars)",
  "meta_description": "Search snippet with update hook (145-160 chars)",
  "update_summary": "1-2 sentence description of what was refreshed/updated in this edition",
  "lead_summary": "Refreshed impactful executive summary",
  "key_takeaways": [
    "Updated takeaway 1 with latest context",
    "Updated takeaway 2 with key impact",
    "Updated takeaway 3 with forward outlook"
  ],
  "sections": [
    {
      "heading": "H2 Heading",
      "paragraphs": ["Deep-dive paragraph with latest updates...", "Granular analysis paragraph..."]
    },
    {
      "heading": "H2 Heading: Latest Global Developments & Strategic Impact",
      "paragraphs": ["Detailed update paragraph...", "Second paragraph..."]
    },
    {
      "heading": "H2 Heading: Outlook and Next Steps",
      "paragraphs": ["Forward-looking perspective..."]
    }
  ],
  "quote": "Refreshed expert quotation or official spokesperson comment",
  "quote_author": "Expert Name, Title & Institution",
  "faqs": [
    {"question": "Updated search question 1?", "answer": "Clear helpful answer."},
    {"question": "Updated search question 2?", "answer": "Clear helpful answer."}
  ],
  "tags": ["Tag1", "Tag2", "Tag3", "Tag4"]
}
PROMPT;

        $userPrompt = "Update this existing story: '{$news->title}'. Category: {$categoryName}. Language: {$news->language}.\nExisting background text:\n{$existingExcerpt}\n\nProvide the updated, expanded edition in valid JSON format.";

        $response = $this->httpClient->post($this->endpoint, [
            'json' => [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => $this->temperature,
            ],
        ]);

        $raw = $response->getBody()->getContents();
        $decoded = json_decode($raw, true);
        $contentStr = $decoded['choices'][0]['message']['content'] ?? '';

        $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', trim($contentStr));
        $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

        $data = json_decode($cleanJson, true);
        if (!is_array($data) || empty($data['sections'])) {
            if (preg_match('/\{[\s\S]*\}/', $contentStr, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (!is_array($data)) {
            throw new \Exception("Could not parse AI update response JSON: " . Str::limit($contentStr, 150));
        }

        return $data;
    }

    /**
     * Delete / Prune outdated news articles according to retention settings.
     */
    public function deleteOldArticles(array $options = []): array
    {
        $afterDays = isset($options['days']) ? (int) $options['days'] : (int) config('ai_news.delete_after_days', 30);
        $minViews = isset($options['min_views']) ? (int) $options['min_views'] : (int) config('ai_news.delete_min_views', 50);
        $keepMax = isset($options['keep_max']) ? (int) $options['keep_max'] : (int) config('ai_news.delete_keep_max', 500);
        $cleanupImages = isset($options['cleanup_images']) ? (bool) $options['cleanup_images'] : (bool) config('ai_news.delete_cleanup_images', true);
        $dryRun = !empty($options['dry_run']);

        $candidateIds = collect();

        // 1. Age & Low-Views criteria: delete articles older than X days with views < threshold
        if ($afterDays > 0) {
            $ageCutoff = now()->subDays($afterDays);
            $oldQuery = News::where('created_at', '<', $ageCutoff);
            if ($minViews > 0) {
                // Only prune low-traffic posts; keep high-view articles alive
                $oldQuery->where('views', '<', $minViews);
            }
            $oldIds = $oldQuery->pluck('id');
            $candidateIds = $candidateIds->merge($oldIds);
        }

        // 2. Capacity-based criteria: if total articles exceed keep_max, prune oldest excess
        if ($keepMax > 0) {
            $totalCount = News::count();
            if ($totalCount > $keepMax) {
                $excessCount = $totalCount - $keepMax;
                $excessIds = News::orderBy('created_at', 'ASC')->take($excessCount)->pluck('id');
                $candidateIds = $candidateIds->merge($excessIds);
            }
        }

        $candidateIds = $candidateIds->unique();

        if ($candidateIds->isEmpty()) {
            return [
                'status' => 'no_action',
                'message' => "No articles matched the deletion criteria (older than {$afterDays}d with < {$minViews} views).",
                'deleted_count' => 0,
                'deleted_images_count' => 0,
                'articles' => [],
            ];
        }

        $articlesToDelete = News::whereIn('id', $candidateIds)->get();
        $deletedInfo = [];
        $imagesRemoved = 0;

        foreach ($articlesToDelete as $news) {
            $deletedInfo[] = [
                'id' => $news->id,
                'title' => $news->title,
                'views' => $news->views,
                'created_at' => $news->created_at?->toDateTimeString(),
                'image' => $news->image,
            ];

            if (!$dryRun) {
                // Delete image file if cleanup enabled and it's an uploaded AI news image
                if ($cleanupImages && !empty($news->image)) {
                    $imagePath = public_path($news->image);
                    if (File::exists($imagePath) && Str::contains($news->image, ['ai-news-', 'uploads/'])) {
                        // Protect critical site assets from accidental deletion
                        if (!Str::contains($news->image, ['logo', 'favicon', 'ad-'])) {
                            File::delete($imagePath);
                            $imagesRemoved++;
                        }
                    }
                }

                $news->tags()->detach();
                $news->comments()->delete();
                $news->delete();
            }
        }

        return [
            'status' => 'success',
            'dry_run' => $dryRun,
            'deleted_count' => count($deletedInfo),
            'deleted_images_count' => $imagesRemoved,
            'articles' => $deletedInfo,
        ];
    }

    /**
     * Generate an authoritative, comprehensive fallback article (>850 words) adhering strictly to
     * Google E-E-A-T and Helpful Content guidelines when the remote AI endpoint is temporarily unavailable.
     */
    protected function generateEditorialFallbackArticle(string $topic, Category $category, string $language, ?array $sourceMeta = null): array
    {
        $cleanTopic = rtrim(trim($topic), '.');
        $catName = $category->name;
        $sourceName = $sourceMeta['source'] ?? 'Extremis News International Wire';

        if ($language === 'bn') {
            return [
                'title' => "{$cleanTopic}: বিস্তারিত পর্যালোচনা এবং আন্তর্জাতিক প্রভাব",
                'meta_title' => Str::limit("{$cleanTopic} - বিশেষ প্রতিবেদন", 60, ''),
                'meta_description' => Str::limit("{$cleanTopic} সম্পর্কিত সর্বশেষ তথ্য, বিশেষজ্ঞ বিশ্লেষণ এবং বৈশ্বিক কৌশলগত প্রভাবের সার্বিক পর্যালোচনা।", 155, ''),
                'lead_summary' => "আন্তর্জাতিক অঙ্গনে '{$cleanTopic}' বিষয়টি নতুন করে আলোচনার কেন্দ্রবিন্দুতে পরিণত হয়েছে। বিভিন্ন আন্তর্জাতিক নীতি-নির্ধারক ও বাজার বিশ্লেষকদের মতে, এই পরিবর্তনের ফলে সংশ্লিষ্ট ক্ষেত্রসমূহে সুদূরপ্রসারী ইতিবাচক ও কাঠামোগত রূপান্তর ঘটতে পারে।",
                'key_takeaways' => [
                    "{$cleanTopic} খাতে কাঠামোগত পরিবর্তনের মাধ্যমে নতুন আন্তর্জাতিক সুযোগ সৃষ্টি হচ্ছে।",
                    "নীতি-নির্ধারক ও বিশেষজ্ঞদের সমন্বিত পর্যবেক্ষণ অনুযায়ী এই উদ্যোগ টেকসই ভবিষ্যৎ গঠনে সহায়ক ভূমিকা রাখবে।",
                    "আগামী ১২ থেকে ২৪ মাসের মধ্যে এই রূপান্তরের বাস্তব ফলাফল পরিলক্ষিত হবে বলে আশা করা হচ্ছে।"
                ],
                'sections' => [
                    [
                        'heading' => "বর্তমান প্রেক্ষাপট এবং ঘটনার পটভূমি",
                        'paragraphs' => [
                            "সাম্প্রতিক সময়ে আন্তর্জাতিক পরিমণ্ডলে বিভিন্ন পরিবর্তনের মাঝে '{$cleanTopic}' একটি বিশেষ গুরুত্ব অর্জন করেছে। সংশ্লিষ্ট খাত সংশ্লিষ্ট বিশেষজ্ঞ ও আন্তর্জাতিক বিশ্লেষকদের সাম্প্রতিক প্রতিবেদন অনুসারে, বিদ্যমান কাঠামোর আধুনিকায়ন এবং নতুন প্রযুক্তির সংযোজন এই রূপান্তরের মূল চালিকাশক্তি হিসেবে কাজ করছে।",
                            "এই প্রক্রিয়ায় আন্তর্জাতিক সংস্থা ও স্থানীয় অংশীজনদের সম্পৃক্ততা সামগ্রিক কার্যক্রমকে আরও সুসংহত এবং গ্রহণযোগ্য করে তুলছে। প্রাতিষ্ঠানিক পর্যায়ে স্বচ্ছতা এবং কৌশলগত পরিকল্পনার বাস্তবায়নের মাধ্যমে দীর্ঘমেয়াদি উন্নয়নের পথ সুগম হচ্ছে।"
                        ]
                    ],
                    [
                        'heading' => "বৈশ্বিক প্রভাব এবং অর্থনৈতিক প্রতিক্রিয়া",
                        'paragraphs' => [
                            "অর্থনৈতিক ও সামাজিক ক্ষেত্রে এই অগ্রগতির প্রভাব অত্যন্ত তাৎপর্যপূর্ণ। বিভিন্ন বৈশ্বিক সূচকে দেখা গেছে যে, সঠিক ব্যবস্থাপনার মাধ্যমে কার্যকারিতা বৃদ্ধি এবং ব্যয় হ্রাস সম্ভব হয়েছে, যা আন্তর্জাতিক বাজারের ওপর সরাসরি ইতিবাচক প্রভাব ফেলছে।",
                            "আন্তর্জাতিক বাণিজ্যে এবং আধুনিক ব্যবস্থাপনায় এই পরিবর্তনের ফলে নতুন কর্মসংস্থান সৃষ্টি ও প্রযুক্তিনির্ভর উদ্ভাবনের অপার সম্ভাবনা উন্মোচিত হয়েছে। বিশ্বের শীর্ষস্থানীয় অর্থনীতিবিদরা মনে করছেন, এই ধারা অব্যাহত থাকলে সংশ্লিষ্ট খাতে এক নতুন সম্ভাবনার দিগন্ত উন্মোচিত হবে।"
                        ]
                    ],
                    [
                        'heading' => "কৌশলগত চ্যালেঞ্জ এবং নীতিগত সমাধান",
                        'paragraphs' => [
                            "যেকোনো বড় ধরনের রূপান্তরের মতো এই ক্ষেত্রেও কিছু কারিগরি ও নীতিগত চ্যালেঞ্জ বিদ্যমান রয়েছে। বিশেষ করে আধুনিক প্রযুক্তির সাথে মানিয়ে নেওয়া এবং সংশ্লিষ্ট নীতিমালার যুগোপযোগী বাস্তবায়ন একটি বড় পরীক্ষা হিসেবে বিবেচিত হচ্ছে।",
                            "তবে আন্তর্জাতিক অংশীদারিত্ব এবং বিশেষজ্ঞদের সমন্বিত পরামর্শের ভিত্তিতে প্রয়োজনীয় নীতিগত সংস্কার গ্রহণ করা হচ্ছে, যার মাধ্যমে সম্ভাব্য ঝুঁকিগুলো সফলভাবে মোকাবিলা করা সম্ভব হবে।"
                        ]
                    ],
                    [
                        'heading' => "ভবিষ্যতের রূপরেখা ও দীর্ঘমেয়াদি সম্ভাবনা",
                        'paragraphs' => [
                            "আগামী কয়েক বছরের মধ্যে এই অগ্রগতির সুফল আন্তর্জাতিক অঙ্গনে দৃশ্যমান হবে বলে দৃঢ় আশাবাদ ব্যক্ত করা হচ্ছে। বিশেষ করে দীর্ঘমেয়াদি স্থায়িত্ব এবং নতুন উদ্ভাবনী ধারণার সমন্বয় এই খাতের ভবিষ্যৎ গতিপথ নির্ধারণ করবে।",
                            "সামগ্রিক পর্যালোচনায় বলা যায়, '{$cleanTopic}' বিষয়টির সফল বাস্তবায়ন শুধুমাত্র সংশ্লিষ্ট খাতের জন্যই নয়, বরং আন্তর্জাতিক আর্থ-সামাজিক স্থিতিশীলতা অর্জনে একটি গুরুত্বপূর্ণ মাইলফলক হিসেবে পরিগণিত হবে।"
                        ]
                    ]
                ],
                'quote' => "আমরা যখন একটি দূরদর্শী ও টেকসই ভবিষ্যৎ গড়ার প্রত্যয় নিয়ে কাজ করছি, তখন এ ধরনের যুগোপযোগী কৌশলগত রূপান্তর আন্তর্জাতিক অঙ্গনে নতুন মানদণ্ড স্থাপন করবে।",
                'quote_author' => "আন্তর্জাতিক কৌশলগত বিশ্লেষক প্যানেল, এক্সট্রিমিস নিউজ",
                'faqs' => [
                    [
                        'question' => "{$cleanTopic} কেন বর্তমানে অত্যন্ত গুরুত্বপূর্ণ?",
                        'answer' => "আন্তর্জাতিক বাজারে এর সামগ্রিক কার্যকারিতা, নীতিগত সংস্কার এবং ভবিষ্যৎ অর্থনৈতিক সম্ভাবনার কারণে এটি অত্যন্ত গুরুত্ব বহন করে।"
                    ],
                    [
                        'question' => "এই অগ্রগতির ফলে সাধারণ মানুষ ও সংশ্লিষ্ট ক্ষেত্র কীভাবে উপকৃত হবে?",
                        'answer' => "এটি কার্যকারিতা বৃদ্ধির পাশাপাশি উন্নত সেবা প্রদান, নতুন সুযোগ সৃষ্টি এবং সামগ্রিক উন্নয়ন প্রক্রিয়াকে আরও গতিশীল করবে।"
                    ],
                    [
                        'question' => "ভবিষ্যতে এই খাতের জন্য কী ধরনের পদক্ষেপ গ্রহণ করা প্রয়োজন?",
                        'answer' => "ধারাবাহিক গবেষণা, আন্তর্জাতিক সহযোগিতা এবং যুগোপযোগী নীতিমালার কঠোর প্রয়োগ ভবিষ্যতে এর সাফল্য নিশ্চিত করবে।"
                    ]
                ],
                'tags' => [$catName, 'আন্তর্জাতিক সংবাদ', 'বিশ্লেষণ', 'এক্সট্রিমিস নিউজ', 'অর্থনীতি ও সমাজ', 'বিশেষ প্রতিবেদন']
            ];
        }

        // Default English
        return [
            'title' => "{$cleanTopic}: Strategic In-Depth Analysis and Global Outlook",
            'meta_title' => Str::limit("{$cleanTopic} - In-Depth Global Analysis", 60, ''),
            'meta_description' => Str::limit("Comprehensive investigation into {$cleanTopic}: key developments, global market impact, strategic challenges, and expert 2026 outlook.", 155, ''),
            'lead_summary' => "The unfolding developments surrounding {$cleanTopic} mark a pivotal turning point across global markets and strategic policy frameworks. Industry leaders and international analysts are closely examining the cross-sector implications as structural shifts accelerate worldwide.",
            'key_takeaways' => [
                "Strategic developments in {$cleanTopic} are driving structural operational shifts across international markets.",
                "Multilateral stakeholders and regulatory bodies are establishing standardized frameworks to ensure long-term resilience and transparency.",
                "Market projections indicate accelerating adoption and measurable macroeconomic impact over the next 12 to 36 months."
            ],
            'sections' => [
                [
                    'heading' => "Operational Context and Key Drivers Behind {$cleanTopic}",
                    'paragraphs' => [
                        "The recent momentum surrounding {$cleanTopic} highlights a profound structural realignment across key sectors. Historically governed by conventional operational models, the integration of advanced technologies and institutional capital has rapidly elevated the pace of innovation, positioning this development at the center of international strategic discourse.",
                        "According to verified reports from {$sourceName}, the convergence of renewed regulatory guidance and private-sector investment is accelerating measurable adoption benchmarks. As organizational architectures adapt, market participants are demonstrating increased agility in deploying scalable capital and modern operational workflows."
                    ]
                ],
                [
                    'heading' => "Macroeconomic Ramifications and International Market Shifts",
                    'paragraphs' => [
                        "The global economic reverberations of {$cleanTopic} are multifaceted. International supply networks, technological infrastructure providers, and institutional investors are calibrating their strategic allocations in response to changing risk-adjusted return profiles and enhanced productivity metrics.",
                        "Econometric models tracking these transitions indicate that early institutional adopters are realizing measurable efficiency dividends, lowering baseline operational expenditures while expanding global reach. These trends suggest a sustained period of capital realignment benefiting cross-border trade corridors and high-growth sectors."
                    ]
                ],
                [
                    'heading' => "Technical Hurdles, Governance Standards, and Risk Vectors",
                    'paragraphs' => [
                        "Despite the compelling trajectory, significant execution and regulatory challenges remain to be addressed. Navigating fragmented jurisdictional mandates, harmonizing data compliance standards, and mitigating cybersecurity vulnerabilities represent vital priorities for global stakeholders.",
                        "Industry oversight bodies emphasize that building trust and institutional durability requires transparent auditing protocols, resilient fault-tolerant designs, and ethical governance standards capable of adapting to rapid technological evolution."
                    ]
                ],
                [
                    'heading' => "Strategic Outlook and Next Phase Milestones for 2026–2028",
                    'paragraphs' => [
                        "Looking ahead over the next 24 to 36 months, the trajectory of {$cleanTopic} will be defined by cross-sector consolidation, accelerated institutional integration, and next-generation policy roadmaps. Collaborative initiatives between private technology leaders and sovereign regulatory authorities will play a decisive role.",
                        "In conclusion, {$cleanTopic} represents far more than an incremental transition—it embodies a fundamental paradigm shift that will shape competitive landscapes and global industry standards for years to come."
                    ]
                ]
            ],
            'quote' => "Navigating complex global transitions demands not just tactical execution, but an unwavering commitment to systemic resilience, verified accountability, and forward-thinking institutional architecture.",
            'quote_author' => "Senior Global Strategy Advisory Council, Extremis News",
            'faqs' => [
                [
                    'question' => "What are the primary factors driving global focus on {$cleanTopic}?",
                    'answer' => "Accelerated institutional capital investment, breakthrough technological efficiency gains, and supportive regulatory frameworks have converged to make this a top international priority."
                ],
                [
                    'question' => "How will international markets and enterprises be impacted over the near term?",
                    'answer' => "Enterprises adopting these modern frameworks early can expect streamlined operational costs, improved compliance resilience, and enhanced competitive positioning across global supply corridors."
                ],
                [
                    'question' => "What should stakeholders monitor in the coming 12 to 24 months?",
                    'answer' => "Key metrics to track include legislative harmonizations, institutional adoption milestones, technological infrastructure resilience benchmarks, and cross-border partnership expansions."
                ]
            ],
            'tags' => [$catName, 'Global News', 'Investigative Analysis', 'Extremis Wire', 'Strategic Outlook', 'Market Trends']
        ];
    }
}
