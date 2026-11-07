<?php

namespace Tests\Unit;

use App\Services\LocationLanguageDetector;
use Illuminate\Http\Request;
use Tests\TestCase;

class LocationLanguageDetectorTest extends TestCase
{
    protected LocationLanguageDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new LocationLanguageDetector();
    }

    public function test_cloudflare_header_detects_bengali_for_bangladesh(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_CF_IPCOUNTRY' => 'BD',
        ]);

        $this->assertSame('BD', $this->detector->detectCountryCodeFromHeaders($request));
        $this->assertSame('bn', $this->detector->detect($request));
    }

    public function test_cloudflare_header_detects_english_for_us(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_CF_IPCOUNTRY' => 'US',
        ]);

        $this->assertSame('US', $this->detector->detectCountryCodeFromHeaders($request));
        $this->assertSame('en', $this->detector->detect($request));
    }

    public function test_x_country_code_header_works(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_COUNTRY_CODE' => 'BD',
        ]);

        $this->assertSame('BD', $this->detector->detectCountryCodeFromHeaders($request));
        $this->assertSame('bn', $this->detector->detect($request));
    }

    public function test_cloudfront_viewer_country_header_works(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_CLOUDFRONT_VIEWER_COUNTRY' => 'BD',
        ]);

        $this->assertSame('BD', $this->detector->detectCountryCodeFromHeaders($request));
        $this->assertSame('bn', $this->detector->detect($request));
    }

    public function test_accept_language_detects_bengali(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'bn-BD,bn;q=0.9,en-US;q=0.8,en;q=0.7',
        ]);

        $this->assertSame('bn', $this->detector->detectFromAcceptLanguage($request));
        $this->assertSame('bn', $this->detector->detect($request));
    }

    public function test_accept_language_detects_english(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ]);

        $this->assertSame('en', $this->detector->detectFromAcceptLanguage($request));
        $this->assertSame('en', $this->detector->detect($request));
    }

    public function test_country_to_language_mapping(): void
    {
        $this->assertSame('bn', $this->detector->mapCountryToLanguage('BD'));
        $this->assertSame('en', $this->detector->mapCountryToLanguage('US'));
        $this->assertSame('en', $this->detector->mapCountryToLanguage('GB'));
        $this->assertSame('en', $this->detector->mapCountryToLanguage('AU'));
        $this->assertSame('en', $this->detector->mapCountryToLanguage('CA'));
    }

    public function test_fallback_to_default_when_no_signals(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        $this->assertSame('en', $this->detector->detect($request));
    }

    public function test_get_language_helper_respects_session_over_headers(): void
    {
        session(['language' => 'en']);

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_CF_IPCOUNTRY' => 'BD',
        ]);
        $this->app->instance('request', $request);

        $this->assertSame('en', getLanguage());
    }

    public function test_get_language_helper_respects_cookie(): void
    {
        $request = Request::create('/', 'GET', [], [
            'site_language' => 'en',
        ], [], [
            'HTTP_CF_IPCOUNTRY' => 'BD',
        ]);
        $this->app->instance('request', $request);

        $this->assertSame('en', getLanguage());
    }

    public function test_get_language_helper_auto_detects_bengali_when_no_cookie_or_session(): void
    {
        session()->forget('language');

        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_CF_IPCOUNTRY' => 'BD',
        ]);
        $this->app->instance('request', $request);

        $this->assertSame('bn', getLanguage());
        $this->assertSame('bn', session('language'));
    }

    public function test_unsupported_country_falls_back_to_english(): void
    {
        // When user visits from France, Germany, Japan, etc. and that language is not in active site languages
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_CF_IPCOUNTRY' => 'FR',
        ]);

        $this->assertSame('en', $this->detector->detect($request));
    }

    public function test_explicit_english_selection_prevents_reverting_to_bengali(): void
    {
        // Simulate user clicking 'English' in language switcher
        session(['language' => 'en', 'manual_language' => true]);

        // Even with Bangladesh IP/country headers, it MUST remain English
        $request = Request::create('/', 'GET', [], [
            'site_language' => 'en',
            'user_manual_lang' => '1',
        ], [], [
            'HTTP_CF_IPCOUNTRY' => 'BD',
            'HTTP_ACCEPT_LANGUAGE' => 'bn-BD,bn;q=0.9',
        ]);
        $this->app->instance('request', $request);

        $this->assertSame('en', getLanguage());
    }
}
