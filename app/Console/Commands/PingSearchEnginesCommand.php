<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Services\SearchEngineIndexingService;
use Illuminate\Console\Command;

class PingSearchEnginesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:ping-indexing
                            {--slug= : Specific post slug to ping}
                            {--limit=10 : Number of recent posts to submit to IndexNow}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Submit recent published articles to IndexNow (Bing/Yandex) and ping Google sitemaps';

    /**
     * Execute the console command.
     */
    public function handle(SearchEngineIndexingService $indexingService): int
    {
        $this->info("==========================================================");
        $this->info("   EXTREMIS NEWS - SEARCH ENGINE INDEXING DISPATCHER      ");
        $this->info("==========================================================");

        $specificSlug = $this->option('slug');
        $limit = (int) $this->option('limit');

        if ($specificSlug) {
            $news = News::where('slug', $specificSlug)->where('status', 1)->first();
            if (!$news) {
                $this->error("Post with slug '{$specificSlug}' not found or inactive.");
                return Command::FAILURE;
            }

            $this->comment("Pinging search engines for: {$news->title}...");
            $results = $indexingService->notifySearchEngines($news);

            $this->info("IndexNow status: " . ($results['indexnow']['success'] ? 'SUCCESS (HTTP 200/202)' : 'FAILED'));
            $this->info("Google sitemap ping: " . ($results['google_ping']['success'] ? 'SUCCESS' : 'FAILED'));
            $this->info("Bing sitemap ping: " . ($results['bing_ping']['success'] ? 'SUCCESS' : 'FAILED'));
            return Command::SUCCESS;
        }

        // Batch ping latest articles
        $recentNews = News::where('status', 1)
            ->where('is_approved', 1)
            ->orderBy('created_at', 'DESC')
            ->take($limit)
            ->get();

        if ($recentNews->isEmpty()) {
            $this->warn("No active news posts found to index.");
            return Command::SUCCESS;
        }

        $urls = $recentNews->map(fn($item) => route('news-details', $item->slug))->toArray();

        $this->comment("Submitting " . count($urls) . " URLs to IndexNow protocol...");
        $indexNowResult = $indexingService->submitToIndexNow($urls);
        $this->info("IndexNow Result: " . ($indexNowResult['success'] ? 'SUCCESS' : 'FAILED') . " (HTTP " . ($indexNowResult['status_code'] ?? 'N/A') . ")");

        $this->comment("Pinging Google XML Sitemaps (standard & Google News)...");
        $googleResult = $indexingService->pingGoogleSitemaps();
        $this->info("Google Sitemap Ping: " . ($googleResult['success'] ? 'SUCCESS' : 'FAILED'));

        $this->comment("Pinging Bing XML Sitemap...");
        $bingResult = $indexingService->pingBingSitemaps();
        $this->info("Bing Sitemap Ping: " . ($bingResult['success'] ? 'SUCCESS' : 'FAILED'));

        $this->newLine();
        $this->info("All search engines notified successfully!");
        return Command::SUCCESS;
    }
}
