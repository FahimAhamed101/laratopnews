<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\News;
use App\Models\SocialCount;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends Controller
{
    /**
     * Display the public journalist / author profile with published articles.
     */
    public function show(int $id, ?string $slug = null): View
    {
        $author = Admin::where('status', 1)->findOrFail($id);

        $news = News::where('author_id', $author->id)
            ->where('status', 1)
            ->where('is_approved', 1)
            ->with(['category'])
            ->orderBy('created_at', 'DESC')
            ->paginate(12);

        $totalViews = News::where('author_id', $author->id)
            ->where('status', 1)
            ->where('is_approved', 1)
            ->sum('views');

        $totalArticles = News::where('author_id', $author->id)
            ->where('status', 1)
            ->where('is_approved', 1)
            ->count();

        $categories = Category::where('status', 1)->get();
        $socialCounts = SocialCount::where('status', 1)->get();

        return view('frontend.author-profile', compact(
            'author',
            'news',
            'totalViews',
            'totalArticles',
            'categories',
            'socialCounts'
        ));
    }
}

