<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use App\Services\AiNewsPosterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiNewsGeneratorController extends Controller
{
    protected AiNewsPosterService $posterService;

    public function __construct(AiNewsPosterService $posterService)
    {
        $this->posterService = $posterService;
    }

    /**
     * Display the AI Auto-Poster Dashboard.
     */
    public function index(Request $request): View
    {
        $connectionStatus = $this->posterService->testConnection();

        $categories = Category::where('status', 1)
            ->orderBy('name')
            ->get();

        $recentNews = News::with(['category', 'author', 'tags'])
            ->latest()
            ->take(12)
            ->get();

        return view('admin.ai-news.index', compact('connectionStatus', 'categories', 'recentNews'));
    }

    /**
     * Generate news articles on-demand.
     */
    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'count' => 'nullable|integer|min:1|max:5',
            'language' => 'nullable|string|in:en,bn',
            'category_slug' => 'nullable|string',
            'topic' => 'nullable|string|max:300',
            'search' => 'nullable|string|max:300',
            'is_breaking' => 'nullable|boolean',
        ]);

        $count = (int) ($request->input('count', 1));
        $language = $request->input('language', 'en');
        $categorySlug = $request->input('category_slug');
        $topic = $request->input('topic');
        $search = $request->input('search');
        $isBreaking = $request->boolean('is_breaking');

        $generatedTitles = [];
        $failedCount = 0;

        for ($i = 0; $i < $count; $i++) {
            try {
                $result = $this->posterService->generateAndPost([
                    'language' => $language,
                    'category_slug' => $categorySlug,
                    'topic' => $topic,
                    'google_search_query' => $search,
                    'is_breaking' => $isBreaking,
                ]);

                $generatedTitles[] = $result['title'];
            } catch (\Exception $e) {
                $failedCount++;
                logger()->error("AI News Generator failed: " . $e->getMessage());
            }
        }

        if (count($generatedTitles) > 0) {
            $msg = count($generatedTitles) . " unique, SEO-optimized worldwide news article(s) published successfully!";
            if ($failedCount > 0) {
                $msg .= " ({$failedCount} failed due to timeout)";
            }
            toast($msg, 'success')->width('400');
        } else {
            toast('Failed to generate articles. Please check the AI endpoint connection.', 'error')->width('400');
        }

        return redirect()->route('admin.ai-news.index');
    }

    /**
     * Update existing articles with AI on-demand.
     */
    public function updateArticles(Request $request): RedirectResponse
    {
        $limit = (int) $request->input('limit', config('ai_news.update_limit', 2));
        $ageHours = (int) $request->input('age_hours', config('ai_news.update_age_hours', 24));
        $specificId = $request->input('id') ? (int) $request->input('id') : null;

        $result = $this->posterService->updateExistingArticles([
            'limit' => $limit,
            'age_hours' => $ageHours,
            'id' => $specificId,
        ]);

        if ($result['status'] === 'no_candidates') {
            toast($result['message'], 'info')->width('400');
        } elseif ($result['updated_count'] > 0) {
            toast("Successfully updated {$result['updated_count']} article(s) with refreshed AI analysis and SEO!", 'success')->width('400');
        } else {
            toast('Failed to update articles. Check error logs for details.', 'error')->width('400');
        }

        return redirect()->route('admin.ai-news.index');
    }

    /**
     * Prune old articles on-demand.
     */
    public function deleteArticles(Request $request): RedirectResponse
    {
        $days = (int) $request->input('days', config('ai_news.delete_after_days', 30));
        $minViews = (int) $request->input('min_views', config('ai_news.delete_min_views', 50));
        $keepMax = (int) $request->input('keep_max', config('ai_news.delete_keep_max', 500));

        $result = $this->posterService->deleteOldArticles([
            'days' => $days,
            'min_views' => $minViews,
            'keep_max' => $keepMax,
            'cleanup_images' => true,
        ]);

        if ($result['status'] === 'no_action') {
            toast($result['message'] ?? 'No low-view articles met the retention criteria for deletion.', 'info')->width('400');
        } else {
            toast("Cleaned up {$result['deleted_count']} low-view article(s) older than {$days}d and {$result['deleted_images_count']} image(s).", 'success')->width('400');
        }

        return redirect()->route('admin.ai-news.index');
    }

    /**
     * Ajax endpoint for live Google News search across popular sources.
     */
    public function searchGoogleNews(Request $request): JsonResponse
    {
        $categorySlug = $request->input('category_slug', 'technology');
        $language = $request->input('language', 'en');
        $query = $request->input('query');

        $stories = $this->posterService->getGoogleNewsService()->fetchNews(
            $categorySlug,
            $language,
            $query,
            12
        );

        return response()->json([
            'status' => 'success',
            'count' => $stories->count(),
            'stories' => $stories,
        ]);
    }

    /**
     * Ajax endpoint for testing API connection.
     */
    public function testConnection(): JsonResponse
    {
        $status = $this->posterService->testConnection();
        return response()->json($status);
    }
}
