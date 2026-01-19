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
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        // Use Bootstrap for pagination
        Paginator::useBootstrap();
        
        // STEP 1: Run database setup first
        $this->runDatabaseSetup();
        
        // STEP 2: Then load settings
        $this->initializeSettings();
    }
    
    /**
     * Run database migrations and seeders
     */
    protected function runDatabaseSetup(): void
    {
        // Only run in production
        if (!app()->environment('production')) {
            return;
        }
        
        try {
            Log::info('Checking database setup...');
            
            // Check if migrations table exists
            if (!Schema::hasTable('migrations')) {
                Log::info('Running migrations...');
                
                // Run migrations
                \Artisan::call('migrate --force --no-interaction');
                $migrationOutput = \Artisan::output();
                Log::info('Migration output: ' . $migrationOutput);
                
                // Wait a moment for migrations to complete
                sleep(2);
                
                Log::info('Running AdminSeeder...');
                
                // Run seeder
                \Artisan::call('db:seed --class=AdminSeeder --force --no-interaction');
                $seederOutput = \Artisan::output();
                Log::info('Seeder output: ' . $seederOutput);
                
                Log::info('Database setup completed successfully');
            } else {
                Log::info('Migrations table already exists, skipping setup');
            }
            
        } catch (\Exception $e) {
            Log::error('Database setup failed: ' . $e->getMessage());
            Log::error('Full error: ' . $e->getFile() . ':' . $e->getLine() . ' - ' . $e->getTraceAsString());
        }
    }
    
    /**
     * Load settings from database
     */
    protected function initializeSettings(): void
    {
        $settings = [];
        
        try {
            // Check if settings table exists
            if (Schema::hasTable('settings')) {
                Log::info('Loading settings from database...');
                $settings = Setting::pluck('value', 'key')->toArray();
                Log::info('Loaded ' . count($settings) . ' settings');
            } else {
                Log::warning('Settings table does not exist yet');
            }
        } catch (\Exception $e) {
            Log::error('Failed to load settings: ' . $e->getMessage());
        }
        
        // Share settings with all views
        View::composer('*', function($view) use ($settings) {
            $view->with('settings', $settings);
        });
    }
}