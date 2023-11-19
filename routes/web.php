<?php
use App\Http\Controllers\Admin\LocalizationController;
use App\Http\Controllers\Frontend\LanguageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Admin\SettingController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class,'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::resource('language',LanguageController::class);
Route::get('language',LanguageController::class)->name('language');
Route::get('admin-localization', [LocalizationController::class, 'adminIndex'])->name('admin-localization.index');
Route::get('frontend-localization', [LocalizationController::class, 'frontendIndex'])->name('frontend-localization.index');
Route::post('extract-localize-string', [LocalizationController::class, 'extractLocalizationStrings'])->name('extract-localize-string');
Route::post('update-lang-string', [LocalizationController::class, 'updateLangString'])->name('update-lang-string');
Route::post('translate-string', [LocalizationController::class, 'translateString'])->name('translate-string');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('setting', [SettingController::class, 'index'])->name('setting.index');

    // Settings Routes
    Route::put('general-setting', [SettingController::class, 'updateGeneralSetting'])->name('general-setting.update');
    Route::put('seo-setting', [SettingController::class, 'updateSeoSetting'])->name('seo-setting.update');
    Route::put('appearance-setting', [SettingController::class, 'updateAppearanceSetting'])->name('appearance-setting.update');
    Route::get('about', [HomeController::class, 'about'])->name('about');

    // Contact Page Route
    Route::get('contact', [HomeController::class, 'contact'])->name('contact');
    
    // Contact Page Route
    Route::post('contact', [HomeController::class, 'handleContactFrom'])->name('contact.submit');
    Route::get('news',[HomeController::class,'news'])->name('news');

    Route::post('subscribe-newsletter', [HomeController::class, 'SubscribeNewsLetter'])->name('subscribe-newsletter');
require __DIR__.'/auth.php';
