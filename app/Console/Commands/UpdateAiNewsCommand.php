<?php

namespace App\Console\Commands;

use App\Services\AiNewsPosterService;
use Illuminate\Console\Command;

class UpdateAiNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:auto-update
                            {--limit= : Number of articles to update (defaults to AI_NEWS_UPDATE_LIMIT)}
                            {--age-hours= : Minimum hours since last update (defaults to AI_NEWS_UPDATE_AGE_HOURS)}
                            {--id= : Specific article ID to update}
                            {--force : Run even if AI_NEWS_UPDATE_ENABLED=false in .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically refresh and update existing news articles with latest AI developments and SEO optimization';

    /**
     * Execute the console command.
     */
    public function handle(AiNewsPosterService $service): int
    {
        $isEnabled = config('ai_news.update_enabled', true);
        if (!$isEnabled && !$this->option('force')) {
            $this->warn("AI News Update is disabled in .env (AI_NEWS_UPDATE_ENABLED=false).");
            $this->line("Use --force to override this setting.");
            return Command::SUCCESS;
        }

        $limitOpt = $this->option('limit');
        $limit = $limitOpt !== null ? (int) $limitOpt : (int) config('ai_news.update_limit', 2);

        $ageOpt = $this->option('age-hours');
        $ageHours = $ageOpt !== null ? (int) $ageOpt : (int) config('ai_news.update_age_hours', 24);

        $specificId = $this->option('id') ? (int) $this->option('id') : null;

        $this->info("==========================================================");
        $this->info("   EXTREMIS NEWS - AI ARTICLE REFRESH & UPDATE ENGINE     ");
        $this->info("==========================================================");
        $this->info("Endpoint:  " . config('ai_news.endpoint'));
        $this->info("Model:     " . config('ai_news.model'));
        $this->info("Max Limit: {$limit} articles | Min Age: {$ageHours} hours");
        if ($specificId) {
            $this->info("Target ID: {$specificId}");
        }
        $this->newLine();

        $this->comment("Searching candidate articles and generating refreshed analysis...");

        $result = $service->updateExistingArticles([
            'limit' => $limit,
            'age_hours' => $ageHours,
            'id' => $specificId,
        ]);

        if ($result['status'] === 'no_candidates') {
            $this->warn($result['message']);
            return Command::SUCCESS;
        }

        if ($result['updated_count'] > 0) {
            $this->info("Successfully updated {$result['updated_count']} article(s):");
            foreach ($result['articles'] as $art) {
                $this->line(" - [ID #{$art['id']}] {$art['title']}");
                $this->line("   Words: {$art['old_words']} -> {$art['new_words']} words ({$art['duration_sec']}s)");
                $this->line("   Summary: {$art['update_summary']}");
                $this->line("   URL: {$art['url']}");
                $this->newLine();
            }
            return Command::SUCCESS;
        }

        $this->error("Failed to update any candidate articles. Check logs for details.");
        return Command::FAILURE;
    }
}
