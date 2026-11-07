<?php

use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\AdminAuthenticationController;
use App\Http\Controllers\Admin\RoleUserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeSectionSettingController;
use App\Http\Controllers\Admin\FooterGridOneController;
use App\Http\Controllers\Admin\FooterGridThreeController;
use App\Http\Controllers\Admin\FooterGridTwoController;
use App\Http\Controllers\Admin\FooterInfoController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LocalizationController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SocialCountController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\AiNewsGeneratorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->prefix('admin')->as('admin.')->group(function () {
    Route::get('login', [AdminAuthenticationController::class, 'login'])->name('login');
    Route::post('login', [AdminAuthenticationController::class, 'handleLogin'])->name('handle-login');
    Route::get('forgot-password', [AdminAuthenticationController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('forgot-password', [AdminAuthenticationController::class, 'sendResetLink'])->name('forgot-password.send');
    Route::get('reset-password/{token}', [AdminAuthenticationController::class, 'resetPassword'])->name('reset-password');
    Route::post('reset-password', [AdminAuthenticationController::class, 'handleResetPassword'])->name('reset-password.send');
});

Route::prefix('admin')->as('admin.')->middleware(['admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [AdminAuthenticationController::class, 'logout'])->name('logout');
    Route::put('profile-password-update/{id}', [ProfileController::class, 'passwordUpdate'])->name('profile-password.update');

    // Category routes
    Route::resource('category', CategoryController::class)->except(['show']);

    //Home Section Setting Routes
    Route::get('home-section-setting', [HomeSectionSettingController::class, 'index'])->name('home-section-setting.index');
    Route::put('home-section-setting', [HomeSectionSettingController::class, 'update'])->name('home-section-setting.update');


    // News routes
    Route::get('fetch-news-category', [NewsController::class, 'fetchCategory'])->name('fetch-news-category');
    Route::post('news-copy/{id}', [NewsController::class, 'copyNews'])->name('news.copy');
    Route::get('pending-news', [NewsController::class, 'pendingNews'])->name('pending.news.index');
    Route::put('approve-news', [NewsController::class, 'approveNews'])->name('approve-news');
    Route::get('toggle-news-status', [NewsController::class, 'toggleNewsStatus'])->name('toggle-news-status');
    Route::resource('news', NewsController::class)->except(['show']);

    // AI News Auto-Poster Routes
    Route::get('ai-news-generator', [AiNewsGeneratorController::class, 'index'])->name('ai-news.index');
    Route::post('ai-news-generator/generate', [AiNewsGeneratorController::class, 'generate'])->name('ai-news.generate');
    Route::post('ai-news-generator/update', [AiNewsGeneratorController::class, 'updateArticles'])->name('ai-news.update');
    Route::post('ai-news-generator/delete', [AiNewsGeneratorController::class, 'deleteArticles'])->name('ai-news.delete');
    Route::get('ai-news-generator/test-connection', [AiNewsGeneratorController::class, 'testConnection'])->name('ai-news.test-connection');
    Route::get('ai-news-generator/search-google', [AiNewsGeneratorController::class, 'searchGoogleNews'])->name('ai-news.search-google');

   
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::match(['put', 'patch'], 'profile/{profile}', [ProfileController::class, 'update'])->name('profile.update');

    
    Route::resource('language', LanguageController::class)->except(['show']);

    
    Route::resource('social-count', SocialCountController::class)->except(['show']);

    
    Route::get('ad', [AdController::class, 'index'])->name('ad.index');
    Route::match(['put', 'patch'], 'ad/{ad}', [AdController::class, 'update'])->name('ad.update');

    
    Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
    Route::post('subscribers', [SubscriberController::class, 'store'])->name('subscribers.store');
    Route::delete('subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');

    
    Route::resource('social-link', SocialLinkController::class)->except(['show']);

   
    Route::get('footer-info', [FooterInfoController::class, 'index'])->name('footer-info.index');
    Route::post('footer-info', [FooterInfoController::class, 'store'])->name('footer-info.store');

    
    Route::post('footer-grid-one-title', [FooterGridOneController::class, 'handleTitle'])->name('footer-grid-one-title');
    Route::resource('footer-grid-one', FooterGridOneController::class)->except(['show']);

   
    Route::post('footer-grid-two-title', [FooterGridTwoController::class, 'handleTitle'])->name('footer-grid-two-title');
    Route::resource('footer-grid-two', FooterGridTwoController::class)->except(['show']);

    //Footer Grid Two Route
    Route::post('footer-grid-three-title', [FooterGridThreeController::class, 'handleTitle'])->name('footer-grid-three-title');
    Route::resource('footer-grid-three', FooterGridThreeController::class)->except(['show']);

    // About page Route
    Route::get('about', [AboutController::class, 'index'])->name('about.index');
    Route::put('about', [AboutController::class, 'update'])->name('about.update');

    // Contact page Route
    Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
    Route::put('contact', [ContactController::class, 'update'])->name('contact.update');

    // Contact Message Route
    Route::get('contact-message', [ContactMessageController::class, 'index'])->name('contact-message.index');
    Route::post('contact-send-reply', [ContactMessageController::class, 'sendReplay'])->name('contact.send-reply');
    Route::delete('contact-message/{message}', [ContactMessageController::class, 'destroy'])->name('contact-message.destroy');

    // Settings Routes
    Route::get('setting', [SettingController::class, 'index'])->name('setting.index');

    // Settings Routes
    Route::put('general-setting', [SettingController::class, 'updateGeneralSetting'])->name('general-setting.update');
    Route::put('seo-setting', [SettingController::class, 'updateSeoSetting'])->name('seo-setting.update');
    Route::put('appearance-setting', [SettingController::class, 'updateAppearanceSetting'])->name('appearance-setting.update');
    
    Route::put('microsoft-api-setting', [SettingController::class, 'updateMicrosoftApiSetting'])->name('microsoft-api-setting.update');

    //Role and Permission Routes
    Route::get('role', [RolePermissionController::class, 'index'])->name('role.index');
    Route::get('role/create', [RolePermissionController::class, 'create'])->name('role.create');
    Route::post('role/create', [RolePermissionController::class, 'store'])->name('role.store');
    Route::get('role/{id}/edit', [RolePermissionController::class, 'edit'])->name('role.edit');
    Route::put('role/{id}/edit', [RolePermissionController::class, 'update'])->name('role.update');
    Route::delete('role/{id}/destroy', [RolePermissionController::class, 'destroy'])->name('role.destroy');


    //Admin User Routes
    Route::resource('role-users', RoleUserController::class)->except(['show']);

    //Localization Routes
    Route::get('admin-localization', [LocalizationController::class, 'adminIndex'])->name('admin-localization.index');
    Route::get('frontend-localization', [LocalizationController::class, 'frontendIndex'])->name('frontend-localization.index');
    Route::post('extract-localize-string', [LocalizationController::class, 'extractLocalizationStrings'])->name('extract-localize-string');
    Route::post('update-lang-string', [LocalizationController::class, 'updateLangString'])->name('update-lang-string');
    Route::post('translate-string', [LocalizationController::class, 'translateString'])->name('translate-string');
});
