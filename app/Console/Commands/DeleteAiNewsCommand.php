<?php

namespace App\Console\Commands;

use App\Services\AiNewsPosterService;
use Illuminate\Console\Command;

class DeleteAiNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:auto-delete
                            {--days= : Delete articles older than X days (defaults to AI_NEWS_DELETE_AFTER_DAYS, 30)}
                            {--min-views= : Only delete articles if views are below this threshold (defaults to AI_NEWS_DELETE_MIN_VIEWS, 50)}
                            {--keep-max= : Retain maximum X articles, pruning oldest (defaults to AI_NEWS_DELETE_KEEP_MAX)}
                            {--cleanup-images : Delete feature image files from disk}
                            {--dry-run : Simulate deletion without removing database records}
                            {--force : Run even if AI_NEWS_DELETE_ENABLED=false in .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically prune and delete outdated low-view news articles and cover images based on retention settings';

    /**
     * Execute the console command.
     */
    public function handle(AiNewsPosterService $service): int
    {
        $isEnabled = config('ai_news.delete_enabled', true);
        if (!$isEnabled && !$this->option('force')) {
            $this->warn("AI News Deletion is disabled in .env (AI_NEWS_DELETE_ENABLED=false).");
            $this->line("Use --force to override this setting.");
            return Command::SUCCESS;
        }

        $daysOpt = $this->option('days');
        $days = $daysOpt !== null ? (int) $daysOpt : (int) config('ai_news.delete_after_days', 30);

        $minViewsOpt = $this->option('min-views');
        $minViews = $minViewsOpt !== null ? (int) $minViewsOpt : (int) config('ai_news.delete_min_views', 50);

        $keepMaxOpt = $this->option('keep-max');
        $keepMax = $keepMaxOpt !== null ? (int) $keepMaxOpt : (int) config('ai_news.delete_keep_max', 500);

        $cleanupImages = $this->option('cleanup-images') || config('ai_news.delete_cleanup_images', true);
        $dryRun = (bool) $this->option('dry-run');

        $this->info("==========================================================");
        $this->info("   EXTREMIS NEWS - AI ARTICLE PRUNING & RETENTION ENGINE  ");
        $this->info("==========================================================");
        $this->info("Delete older than: " . ($days > 0 ? "{$days} days" : "Disabled"));
        $this->info("Views threshold:   " . ($minViews > 0 ? "Fewer than {$minViews} views (low views)" : "All views"));
        $this->info("Max total articles to retain: " . ($keepMax > 0 ? "{$keepMax}" : "Unlimited"));
        $this->info("Image cleanup: " . ($cleanupImages ? "Enabled" : "Disabled"));
        if ($dryRun) {
            $this->warn("MODE: DRY RUN (No database records or files will be modified)");
        }
        $this->newLine();

        $result = $service->deleteOldArticles([
            'days' => $days,
            'min_views' => $minViews,
            'keep_max' => $keepMax,
            'cleanup_images' => $cleanupImages,
            'dry_run' => $dryRun,
        ]);

        if ($result['status'] === 'no_action') {
            $this->info($result['message']);
            return Command::SUCCESS;
        }

        $verb = $dryRun ? 'Identified for deletion' : 'Deleted';
        $this->info("{$verb}: {$result['deleted_count']} article(s) | {$result['deleted_images_count']} image file(s) removed.");

        foreach ($result['articles'] as $art) {
            $this->line(" - [ID #{$art['id']}] {$art['title']} (Created: {$art['created_at']})");
        }

        return Command::SUCCESS;
    }
}
