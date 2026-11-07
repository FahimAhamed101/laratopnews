<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $lang = $request->language_code ?? 'en';

        // Validate that language is supported, fallback to 'en'
        $detector = app(\App\Services\LocationLanguageDetector::class);
        if (! $detector->isLanguageSupported($lang)) {
            $lang = 'en';
        }

        session(['language' => $lang]);
        session(['manual_language' => true]);

        // Non-httpOnly 1-year cookie so both JavaScript and server can read and respect the choice
        $cookie = cookie('site_language', $lang, 525600, '/', null, null, false);
        $manualCookie = cookie('user_manual_lang', '1', 525600, '/', null, null, false);

        cookie()->queue($cookie);
        cookie()->queue($manualCookie);

        return response(['status' => 'success'])
            ->withCookie($cookie)
            ->withCookie($manualCookie);
    }
}
