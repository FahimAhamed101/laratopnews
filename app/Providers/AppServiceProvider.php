<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        Paginator::useBootstrap();
        
        // Run safe database setup
        $this->safeDatabaseSetup();
        
        // Load settings
        $this->initializeSettings();
    }
    
    protected function safeDatabaseSetup(): void
    {
        // NEVER use migrate:fresh in production automatically!
        if ($this->app->environment('production')) {
            $this->productionDatabaseSetup();
        } else {
            $this->developmentDatabaseSetup();
        }
    }
    
    protected function productionDatabaseSetup(): void
    {
        try {
            Log::info('Starting production database setup');
            
            // Test database connection
            DB::connection()->getPdo();
            
            // Check if we need to migrate
            if (!Schema::hasTable('migrations')) {
                Log::info('First-time setup detected, running migrations...');
                \Artisan::call('migrate --force --no-interaction');
                
                Log::info('Seeding initial data...');
                \Artisan::call('db:seed --class=AdminSeeder --force --no-interaction');
                
                Log::info('Production database setup completed');
            } else {
                // Run only pending migrations
                \Artisan::call('migrate --force --no-interaction');
                Log::info('Production migrations completed');
            }
            
        } catch (\Exception $e) {
            Log::error('Production database setup failed: ' . $e->getMessage());
        }
    }
    
    protected function developmentDatabaseSetup(): void
    {
        try {
            // For development, you can use fresh if needed
            $shouldFresh = env('DB_FRESH', false);
            
            if ($shouldFresh) {
                Log::warning('Development fresh install starting...');
                \Artisan::call('migrate:fresh --force --no-interaction');
                \Artisan::call('db:seed --class=AdminSeeder --force --no-interaction');
                Log::info('Development fresh install completed');
            } else {
                \Artisan::call('migrate --force --no-interaction');
                Log::info('Development migrations completed');
            }
            
        } catch (\Exception $e) {
            Log::error('Development database setup failed: ' . $e->getMessage());
        }
    }
    
    protected function initializeSettings(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::pluck('value', 'key')->toArray();
            } else {
                $settings = [];
                Log::warning('Settings table not found');
            }
        } catch (\Exception $e) {
            $settings = [];
            Log::error('Failed to load settings: ' . $e->getMessage());
        }
        
        View::composer('*', function($view) use ($settings) {
            $view->with('settings', $settings);
        });
    }
}