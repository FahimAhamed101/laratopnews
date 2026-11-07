<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = getLanguage();
        App::setLocale($lang);

        $response = $next($request);

        if (! $request->hasCookie('site_language') && method_exists($response, 'withCookie')) {
            $response->withCookie(cookie('site_language', $lang, 525600, '/', null, null, false));
        }

        return $response;
    }
}