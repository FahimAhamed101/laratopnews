<?php

use App\Http\Controllers\Frontend\FeedController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LanguageController;
use App\Http\Controllers\Frontend\LegalPagesController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('language', LanguageController::class)->name('language');

// SEO XML Sitemaps & Feeds
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('sitemap', [SitemapController::class, 'index']);
Route::get('sitemap-news.xml', [SitemapController::class, 'newsSitemap'])->name('sitemap.news');
Route::get('feed', [FeedController::class, 'index'])->name('feed');
Route::get('rss.xml', [FeedController::class, 'index'])->name('feed.rss');

// Google AdSense Compliance & Legal Pages
Route::get('ads.txt', [LegalPagesController::class, 'adsTxt'])->name('ads.txt');
Route::get('privacy-policy', [LegalPagesController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('terms-of-service', [LegalPagesController::class, 'termsOfService'])->name('terms-of-service');

// IndexNow Key Verification Route
Route::get('{key}.txt', function (string $key) {
    $validKey = config('services.indexnow.key', 'extremis2026indexnow');
    if ($key === $validKey) {
        return response($validKey, 200)->header('Content-Type', 'text/plain');
    }
    abort(404);
})->where('key', '^[a-zA-Z0-9_-]+$');

Route::controller(HomeController::class)->group(function () {
    Route::get('news', 'news')->name('news');
    Route::get('news-details/{slug}', 'showNews')->name('news-details');
    Route::post('subscribe-newsletter', 'subscribeNewsLetter')->name('subscribe-newsletter');
    Route::get('about', 'about')->name('about');
    Route::get('contact', 'contact')->name('contact');
    Route::post('contact', 'handleContactFrom')->name('contact.submit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', UserDashboardController::class)->name('dashboard');
    Route::get('/dashboard/news/create', [UserDashboardController::class, 'create'])->name('dashboard.news.create');
    Route::post('/dashboard/news', [UserDashboardController::class, 'store'])->name('dashboard.news.store');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('news-comment', [HomeController::class, 'handleComment'])->name('news-comment');
    Route::post('news-comment-reply', [HomeController::class, 'handleReply'])->name('news-comment-reply');
    Route::delete('news-comment-destroy', [HomeController::class, 'commentDestroy'])->name('news-comment-destroy');
});

require __DIR__.'/auth.php';
