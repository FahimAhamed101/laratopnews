<?php

namespace App\Console\Commands;

use App\Services\AiNewsPosterService;
use Illuminate\Console\Command;

class GenerateAiNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:auto-generate
                            {--topic= : Specific topic for the news article}
                            {--search= : Search query for Google News popular sources}
                            {--category= : Target category slug (e.g. technology, business, health, sports, climate, national)}
                            {--count= : Number of unique articles to generate}
                            {--lang= : Language of the article (en or bn)}
                            {--breaking : Flag the news as breaking news}
                            {--force : Run even if AI_NEWS_GENERATE_ENABLED=false in .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate and publish unique, SEO-optimized worldwide news articles using DeepSeek AI';

    /**
     * Execute the console command.
     */
    public function handle(AiNewsPosterService $service): int
    {
        $isEnabled = config('ai_news.generate_enabled', true);
        if (!$isEnabled && !$this->option('force')) {
            $this->warn("AI News Generation is disabled in .env (AI_NEWS_GENERATE_ENABLED=false).");
            $this->line("Use --force to override this setting.");
            return Command::SUCCESS;
        }

        $countOpt = $this->option('count');
        $count = $countOpt !== null ? (int) $countOpt : (int) config('ai_news.generate_count', 1);
        if ($count < 1) {
            $count = 1;
        }

        $topic = $this->option('topic');
        $search = $this->option('search');
        $category = $this->option('category');
        $langOption = $this->option('lang');
        $isBreaking = (bool) $this->option('breaking');

        // Determine language array (e.g. 'en,bn' from .env)
        $configuredLangs = array_filter(array_map('trim', explode(',', config('ai_news.generate_languages', 'en,bn'))));
        if (empty($configuredLangs)) {
            $configuredLangs = ['en'];
        }

        $this->info("==========================================================");
        $this->info("   EXTREMIS NEWS - AI WORLDWIDE AUTO-POSTER ENGINE       ");
        $this->info("==========================================================");
        $this->info("Endpoint: " . config('ai_news.endpoint'));
        $this->info("Model:    " . config('ai_news.model'));
        $this->info("Articles to generate: {$count}");
        $this->info("Configured Languages: " . implode(', ', $configuredLangs));
        if ($search) {
            $this->info("Google News Search: {$search}");
        }
        if ($topic) {
            $this->info("Topic:    {$topic}");
        }
        if ($category) {
            $this->info("Category: {$category}");
        }
        $this->newLine();
        $this->newLine();

        $successCount = 0;

        for ($i = 1; $i <= $count; $i++) {
            $currentLang = $langOption ?: $configuredLangs[($i - 1) % count($configuredLangs)];
            $this->comment("[{$i}/{$count}] Ingesting Google News popular sources & generating SEO article in [{$currentLang}]...");

            $startTime = microtime(true);

            try {
                $result = $service->generateAndPost([
                    'topic' => $topic,
                    'google_search_query' => $search,
                    'category_slug' => $category,
                    'language' => $currentLang,
                    'is_breaking' => $isBreaking,
                ]);

                $duration = round(microtime(true) - $startTime, 1);

                $this->info(" SUCCESS! Article published in {$duration}s");
                $this->line("   - Title:      " . $result['title']);
                $this->line("   - Category:   " . $result['category']);
                $this->line("   - Source:     " . ($result['source'] ?? 'Extremis News Wire'));
                $this->line("   - Word Count: " . $result['word_count'] . " words");
                $this->line("   - URL:        " . $result['url']);
                $this->line("   - Image:      " . $result['image']);
                $this->line("   - SEO Meta:   " . $result['meta_title']);
                $this->line("   - Tags:       " . implode(', ', $result['tags']));
                if (!empty($result['indexing'])) {
                    $idxOk = (!empty($result['indexing']['indexnow']['success'])) ? 'IndexNow (Bing/Yandex) ✅' : 'IndexNow ⚠️';
                    $gPing = (!empty($result['indexing']['google_ping']['success'])) ? 'Google Ping ✅' : 'Google Ping ⚠️';
                    $this->line("   - Indexing:   {$idxOk} | {$gPing}");
                }
                $this->newLine();

                $successCount++;
            } catch (\Exception $e) {
                $this->error(" Generation failed for article {$i}: " . $e->getMessage());
                $this->newLine();
            }
        }

        $this->info("Completed! Generated {$successCount}/{$count} articles successfully.");
        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
