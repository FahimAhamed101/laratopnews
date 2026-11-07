<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Services\AiNewsPosterService;
use App\Services\GoogleNewsSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixThinPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fix-thin-posts
                            {--min-words=200 : Minimum acceptable word count}
                            {--limit=25 : Maximum number of thin posts to remediate per run}
                            {--dry-run : Only list thin posts without updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and remediate thin, low-value, or 1-word posts with comprehensive, SEO-optimized journalism using Google News and AI';

    /**
     * Execute the console command.
     */
    public function handle(AiNewsPosterService $posterService, GoogleNewsSearchService $googleNews): int
    {
        $minWords = (int) $this->option('min-words');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Scanning news table for thin or low-value posts (< {$minWords} words)...");

        $allNews = News::all();
        $thinPosts = collect();

        foreach ($allNews as $news) {
            $words = str_word_count(strip_tags($news->content));
            if ($words < $minWords) {
                $thinPosts->push([
                    'model' => $news,
                    'words' => $words,
                ]);
            }
        }

        if ($thinPosts->isEmpty()) {
            $this->info("✅ Great news! All published posts meet the E-E-A-T word count threshold (>= {$minWords} words).");
            return Command::SUCCESS;
        }

        $this->warn("Found {$thinPosts->count()} thin posts below the quality threshold.");

        if ($dryRun) {
            $this->table(
                ['ID', 'Lang', 'Title', 'Current Words'],
                $thinPosts->map(fn($item) => [
                    $item['model']->id,
                    $item['model']->language,
                    Str::limit($item['model']->title, 50),
                    $item['words'],
                ])
            );
            return Command::SUCCESS;
        }

        $candidatesToFix = $thinPosts->take($limit);
        $this->info("Starting remediation for " . $candidatesToFix->count() . " posts...");

        $fixedCount = 0;
        foreach ($candidatesToFix as $idx => $item) {
            /** @var News $news */
            $news = $item['model'];
            $currentWords = $item['words'];
            $category = $news->category;
            $catSlug = $category ? $category->slug : 'technology';

            $this->comment("[#" . ($idx + 1) . "/{$candidatesToFix->count()}] Upgrading Post ID: {$news->id} ('{$news->title}') [{$news->language}] - currently {$currentWords} words");

            try {
                // Fetch fresh trending story from Google News for this category and language
                $candidate = $googleNews->fetchTopUniqueCandidate($catSlug, $news->language);
                $topic = $candidate['clean_title'] ?? $news->title;
                $sourceMeta = $candidate ? [
                    'source' => $candidate['source'],
                    'snippet' => $candidate['snippet'],
                    'pub_date' => $candidate['pub_date'],
                    'link' => $candidate['link'],
                ] : null;

                // Generate rich long-form content
                $result = $posterService->generateAndPost([
                    'language' => $news->language,
                    'category_slug' => $catSlug,
                    'topic' => $topic,
                    'is_breaking' => (bool) $news->is_breaking_news,
                ]);

                // Update the thin post with the rich content
                $news->title = $result['title'];
                $news->slug = $result['slug'];
                $news->content = News::find($result['id'])->content;
                $news->meta_title = $result['meta_title'];
                $news->meta_description = $result['meta_description'];
                $news->image = str_replace(asset(''), '', $result['image']);
                $news->status = 1;
                $news->is_approved = 1;
                $news->save();

                // Delete temporary duplicate record created by generateAndPost
                if ($result['id'] !== $news->id) {
                    News::where('id', $result['id'])->delete();
                }

                $newWordCount = str_word_count(strip_tags($news->content));
                $this->info("   -> Fixed! New title: '{$news->title}' ({$newWordCount} words)");
                $fixedCount++;
            } catch (\Exception $e) {
                $this->error("   -> Failed to fix post ID {$news->id}: " . $e->getMessage());
            }
        }

        $this->info("Finished! Successfully remediated {$fixedCount} posts.");
        return Command::SUCCESS;
    }
}
