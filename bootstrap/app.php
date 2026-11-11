<?php

use App\Http\Middleware\Admin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: [
            'site_language',
            'user_manual_lang',
            'cookie_consent',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLanguage::class,
        ]);

        $middleware->alias([
            'admin' => Admin::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e) {
            error_log("[LARAVEL_FATAL] " . get_class($e) . " => " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        });

        // Gracefully handle CSRF 419 token expiration without ugly dead-end error pages
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch or session expired. Please refresh the page.',
                ], 419);
            }

            if ($request->is('admin/*') || $request->is('admin')) {
                return redirect()->route('admin.login')
                    ->withInput($request->except('password', 'password_confirmation', '_token'))
                    ->with('error', 'Your session expired. Please log in again.');
            }

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('error', 'Your session expired. Please try submitting again.');
        });
    })->create();
