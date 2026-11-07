<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce HTTPS on Vercel and in production to avoid mixed content issues with CSS/JS assets
        if ($this->app->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'vercel.app'))) {
            URL::forceScheme('https');
        }

        // Safety net: never expose stack traces or source code to the public.
        // If a deploy leaves APP_DEBUG=true, visitors, crawlers and AdSense
        // reviewers would all be able to read the application source. Force it
        // off whenever we are running in production.
        if ($this->app->environment('production') && config('app.debug')) {
            config(['app.debug' => false]);
            Log::critical('APP_DEBUG was enabled in production. Forced off to prevent source-code disclosure.');
        }

        View::composer('*', function ($view) {
            app()->setLocale(getLanguage());

            $defaults = [
                'site_name' => config('app.name', 'Extremis News'),
                'site_logo' => 'uploads/extremis-news-header-logo.png',
                'site_favicon' => 'uploads/bgSXgTV5CNlB1pZwLyH0eyNp5O4cCZ.png',
                'site_seo_title' => config('app.name', 'Extremis News - Verified Local and Global Updates'),
                'site_seo_description' => 'Extremis News publishes verified updates for readers who need clear, useful reporting without noise.',
                'site_seo_keywords' => 'news, Extremis News, business, technology, sports, health, climate, culture',
                'site_color' => '#1066ff',
            ];

            $settings = $defaults;

            if (Schema::hasTable('settings')) {
                $settings = array_merge(
                    $settings,
                    Setting::query()->pluck('value', 'key')->toArray()
                );
            }

            // Legacy brand cleanup.
            // Part of this site's content was published under the "Daily Metro
            // News" brand while the domain, logo, legal pages and AdSense account
            // all say "Extremis News". A publisher must present one consistent
            // identity to pass AdSense review, so any legacy brand string is
            // rewritten to the canonical app name.
            //
            // Safe to delete once the settings table has been corrected
            // (see deploy/fix-database.sql).
            $legacyBrands = ['Daily Metro News', 'Daily Metro'];
            $canonicalBrand = config('app.name', 'Extremis News');

            foreach (['site_name', 'site_seo_title', 'site_seo_description', 'site_seo_keywords'] as $brandKey) {
                if (! empty($settings[$brandKey])) {
                    $settings[$brandKey] = str_replace($legacyBrands, $canonicalBrand, $settings[$brandKey]);
                }
            }

            if (!empty($settings['site_logo']) && !file_exists(public_path($settings['site_logo']))) {
                $settings['site_logo'] = $defaults['site_logo'];
            }

            if (!empty($settings['site_favicon']) && !file_exists(public_path($settings['site_favicon']))) {
                $settings['site_favicon'] = $defaults['site_favicon'];
            }

            $view->with('settings', $settings);
        });
    }
}
