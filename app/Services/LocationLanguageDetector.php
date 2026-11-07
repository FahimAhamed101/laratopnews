<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LocationLanguageDetector
{
    /**
     * Map of ISO 3166-1 alpha-2 country codes to primary language codes (ISO 639-1).
     *
     * @var array<string, string>
     */
    protected array $countryToLanguageMap = [
        // Bengali
        'BD' => 'bn', // Bangladesh

        // English-speaking / primary English
        'US' => 'en', // United States
        'GB' => 'en', // United Kingdom
        'CA' => 'en', // Canada
        'AU' => 'en', // Australia
        'NZ' => 'en', // New Zealand
        'IE' => 'en', // Ireland
        'ZA' => 'en', // South Africa
        'SG' => 'en', // Singapore
        'NG' => 'en', // Nigeria
        'KE' => 'en', // Kenya
        'GH' => 'en', // Ghana

        // Other common regions (matched if language is enabled on site)
        'IN' => 'en', // India (defaults to English, or Bengali if Accept-Language indicates bn)
        'SA' => 'ar', 'AE' => 'ar', 'EG' => 'ar', 'QA' => 'ar', 'KW' => 'ar', // Arabic
        'ES' => 'es', 'MX' => 'es', 'AR' => 'es', 'CO' => 'es', // Spanish
        'FR' => 'fr', 'BE' => 'fr', 'SN' => 'fr', // French
        'DE' => 'de', 'AT' => 'de', 'CH' => 'de', // German
        'PK' => 'ur', // Pakistan (Urdu)
        'BR' => 'pt', 'PT' => 'pt', // Portuguese
        'RU' => 'ru', 'BY' => 'ru', // Russian
        'TR' => 'tr', // Turkey
        'JP' => 'ja', // Japan
        'CN' => 'zh', 'TW' => 'zh', 'HK' => 'zh', // Chinese
    ];

    /**
     * Detect preferred language automatically based on location & client signals.
     */
    public function detect(?Request $request = null): string
    {
        $request = $request ?: request();

        if (! $request) {
            return $this->getDefaultLanguage();
        }

        // 1. Check CDN / Reverse-Proxy GeoIP Headers (Cloudflare, AWS CloudFront, etc.)
        $countryCode = $this->detectCountryCodeFromHeaders($request);

        // 2. If no CDN header and public IP exists, check IP Geolocation
        if (! $countryCode) {
            $countryCode = $this->detectCountryCodeFromIp($request);
        }

        // 3. If country detected, map to language
        if ($countryCode) {
            $mappedLang = $this->mapCountryToLanguage($countryCode, $request);
            if ($mappedLang && $this->isLanguageSupported($mappedLang)) {
                return $mappedLang;
            }
        }

        // 4. Check Browser Accept-Language header (e.g. bn-BD, en-US)
        $browserLang = $this->detectFromAcceptLanguage($request);
        if ($browserLang && $this->isLanguageSupported($browserLang)) {
            return $browserLang;
        }

        // 5. Fallback to site default language
        return $this->getDefaultLanguage();
    }

    /**
     * Detect 2-letter ISO country code from proxy/CDN headers.
     */
    public function detectCountryCodeFromHeaders(Request $request): ?string
    {
        $headers = [
            'CF-IPCountry',               // Cloudflare
            'X-Country-Code',              // Custom proxies / Nginx GeoIP
            'CloudFront-Viewer-Country',   // AWS CloudFront
            'X-Geo-Country',               // Various CDNs
            'GEOIP_COUNTRY_CODE',          // Apache / Nginx mod_geoip
        ];

        foreach ($headers as $header) {
            $country = $request->header($header) ?: $request->server($header);
            if (is_string($country)) {
                $country = strtoupper(trim($country));
                // Exclude unknown or special Cloudflare values: XX (unknown), T1 (Tor)
                if (preg_match('/^[A-Z]{2}$/', $country) && ! in_array($country, ['XX', 'T1'], true)) {
                    return $country;
                }
            }
        }

        return null;
    }

    /**
     * Detect country code by querying a fast GeoIP service for public IPs.
     */
    public function detectCountryCodeFromIp(Request $request): ?string
    {
        $ip = $request->ip();

        if (! $ip || ! $this->isPublicIp($ip)) {
            return null;
        }

        $cacheKey = 'geoip_country_' . md5($ip);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($ip) {
            try {
                // Free, fast public endpoint with 1.5 second timeout
                $response = Http::timeout(1.5)
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,countryCode',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? null) === 'success' && ! empty($data['countryCode'])) {
                        return strtoupper(trim((string) $data['countryCode']));
                    }
                }
            } catch (Throwable) {
                // Fall through gracefully on network failure
            }

            return null;
        });
    }

    /**
     * Map country code to preferred language code.
     */
    public function mapCountryToLanguage(string $countryCode, ?Request $request = null): ?string
    {
        $countryCode = strtoupper(trim($countryCode));

        // Special handling for India: if user preferred Bengali, respect it
        if ($countryCode === 'IN' && $request) {
            $acceptLang = strtolower((string) $request->header('Accept-Language', ''));
            if (str_contains($acceptLang, 'bn')) {
                return 'bn';
            }
        }

        return $this->countryToLanguageMap[$countryCode] ?? null;
    }

    /**
     * Detect language from the HTTP Accept-Language header.
     */
    public function detectFromAcceptLanguage(Request $request): ?string
    {
        $acceptLanguage = (string) $request->header('Accept-Language', '');
        if (empty($acceptLanguage)) {
            return null;
        }

        // Parse RFC 2616 Accept-Language (e.g., "bn-BD,bn;q=0.9,en-US;q=0.8,en;q=0.7")
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $part) {
            $subParts = explode(';', trim($part));
            $tag = trim($subParts[0]);
            $q = 1.0;

            if (isset($subParts[1])) {
                $qParts = explode('=', trim($subParts[1]));
                if (isset($qParts[1])) {
                    $q = (float) trim($qParts[1]);
                }
            }

            if (! empty($tag)) {
                $languages[$tag] = $q;
            }
        }

        arsort($languages);

        foreach (array_keys($languages) as $langTag) {
            // Extract primary language subtag (e.g. "bn-BD" -> "bn", "en_US" -> "en")
            $primary = strtolower(explode('-', str_replace('_', '-', $langTag))[0]);
            if ($this->isLanguageSupported($primary)) {
                return $primary;
            }
        }

        return null;
    }

    /**
     * Check if a given language code is supported and active in the system.
     */
    public function isLanguageSupported(string $lang): bool
    {
        return in_array(strtolower(trim($lang)), $this->getSupportedLanguages(), true);
    }

    /**
     * Get list of supported active language codes.
     *
     * @return array<string>
     */
    public function getSupportedLanguages(): array
    {
        try {
            return Cache::remember('site_active_languages', 300, function () {
                if (Schema::hasTable('languages')) {
                    $languages = Language::where('status', 1)->pluck('lang')->toArray();
                    if (! empty($languages)) {
                        return array_map('strtolower', $languages);
                    }
                }

                return ['en', 'bn'];
            });
        } catch (Throwable) {
            return ['en', 'bn'];
        }
    }

    /**
     * Get the default site language.
     */
    public function getDefaultLanguage(): string
    {
        try {
            if (Schema::hasTable('languages')) {
                $defaultLang = Language::where(['status' => 1, 'default' => 1])->value('lang');
                if ($defaultLang) {
                    return strtolower($defaultLang);
                }
            }
        } catch (Throwable) {
            // Safe fallback
        }

        return (string) config('app.locale', 'en');
    }

    /**
     * Determine if an IP address is a public, routable IP (not private, loopback, or reserved).
     */
    public function isPublicIp(string $ip): bool
    {
        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
