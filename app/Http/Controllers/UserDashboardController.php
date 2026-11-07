<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\News;
use App\Models\Admin;
use App\Models\Tag;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    use FileUploadTrait;

    public function __invoke(Request $request): View
    {
        $language = getLanguage();
        $user = $request->user() ?? Auth::user();

        $latestNews = News::with(['category', 'author'])
            ->activeEntries()
            ->where('language', $language)
            ->latest()
            ->take(6)
            ->get();

        $popularNews = News::with('category')
            ->activeEntries()
            ->where('language', $language)
            ->orderByDesc('views')
            ->take(5)
            ->get();

        $categories = Category::where([
            'language' => $language,
            'status' => 1,
        ])->withCount(['news' => function ($query) use ($language) {
            $query->activeEntries()->where('language', $language);
        }])->orderBy('name')->get();

        $comments = Comment::with('news')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $submissions = News::with('category')
            ->where('submitted_by_user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'published_news' => News::activeEntries()->where('language', $language)->count(),
            'categories' => $categories->count(),
            'comments' => Comment::where('user_id', $user->id)->count(),
            'submissions' => News::where('submitted_by_user_id', $user->id)->count(),
            'views' => News::activeEntries()->where('language', $language)->sum('views'),
        ];

        return view('dashboard', compact('user', 'latestNews', 'popularNews', 'categories', 'comments', 'submissions', 'stats'));
    }

    public function create(): View
    {
        $categories = Category::where([
            'language' => getLanguage(),
            'status' => 1,
        ])->orderBy('name')->get();

        return view('dashboard-submit-news', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('language', getLanguage()),
            ],
            'image' => ['required', 'image', 'max:3000'],
            'title' => ['required', 'string', 'max:255', 'unique:news,title'],
            'content' => ['required', 'string', 'min:80'],
            'tags' => ['nullable', 'string', 'max:255'],
        ]);

        $imagePath = $this->handleFileUpload($request, 'image');
        $communityAuthor = $this->communityAuthor();

        $news = new News();
        $news->language = getLanguage();
        $news->category_id = $validated['category_id'];
        $news->author_id = $communityAuthor->id;
        $news->submitted_by_user_id = $request->user()->id;
        $news->image = $imagePath;
        $news->title = $validated['title'];
        $news->slug = $this->uniqueSlug($validated['title']);
        $news->content = $validated['content'];
        $news->meta_title = $validated['title'];
        $news->meta_description = Str::limit(strip_tags($validated['content']), 155);
        $news->is_breaking_news = 0;
        $news->show_at_slider = 0;
        $news->show_at_popular = 0;
        $news->status = 1;
        $news->is_approved = 0;
        $news->views = 0;
        $news->save();

        $tags = collect(explode(',', $validated['tags'] ?? 'community,reader submitted'))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->take(8);

        $tagIds = $tags->map(function ($tag) use ($news) {
            return Tag::create([
                'name' => $tag,
                'language' => $news->language,
            ])->id;
        });

        $news->tags()->attach($tagIds);

        return redirect()
            ->route('dashboard')
            ->with('status', 'news-submitted');
    }

    private function communityAuthor(): Admin
    {
        $admin = Admin::where('email', 'community@dailymetro.test')->first();

        if ($admin) {
            return $admin;
        }

        $admin = new Admin();
        $admin->name = 'Community Desk';
        $admin->email = 'community@dailymetro.test';
        $admin->password = Hash::make(Str::random(32));
        $admin->status = 1;
        $admin->save();

        return $admin;
    }

    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 2;

        while (News::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$count;
            $count++;
        }

        return $slug;
    }
}
