<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LegalPagesController extends Controller
{
    /**
     * Display Google AdSense compliant Privacy Policy page.
     */
    public function privacyPolicy(): View
    {
        $siteName = config('app.name', 'Extremis News');
        return view('frontend.privacy-policy', compact('siteName'));
    }

    /**
     * Display Terms of Service page.
     */
    public function termsOfService(): View
    {
        $siteName = config('app.name', 'Extremis News');
        return view('frontend.terms', compact('siteName'));
    }

    /**
     * Serve dynamic ads.txt for Google AdSense verification.
     */
    public function adsTxt(): Response
    {
        $filePath = public_path('ads.txt');
        if (file_exists($filePath)) {
            return response(file_get_contents($filePath), 200)
                ->header('Content-Type', 'text/plain; charset=utf-8');
        }

        $publisherId = config('services.adsense.publisher_id') ?: env('ADSENSE_PUBLISHER_ID', 'pub-7830531573570855');
        $cleanPubId = str_replace('ca-', '', trim($publisherId)) ?: 'pub-7830531573570855';
        $content = "google.com, {$cleanPubId}, DIRECT, f08c47fec0942fa0\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
