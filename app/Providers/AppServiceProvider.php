<?php

namespace App\Providers;
use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Schema; // Add this
use Illuminate\Support\Facades\Log; 
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.\URL::forceScheme('https');
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

// Auto-run migrations in production (use with caution)
    if (app()->environment('production')) {
        try {
            if (!Schema::hasTable('migrations')) {
                \Artisan::call('migrate --force --no-interaction');
                \Artisan::call('db:seed --class=AdminSeeder --force --no-interaction');
            }
        } catch (\Exception $e) {
            \Log::error('Auto-migration failed: ' . $e->getMessage());
        }
    }


        Paginator::useBootstrap();

        $setting = Setting::pluck('value', 'key')->toArray();

        View::composer('*', function($view) use ($setting){
            $view->with('settings', $setting);
        });
    
    }
}
