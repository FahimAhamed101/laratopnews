<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

if (! function_exists('getLanguage')) {
    function getLanguage(): string
    {
        if (app()->runningInConsole() && ! app()->has('request')) {
            return config('app.locale', 'en');
        }

        try {
            $detector = app(\App\Services\LocationLanguageDetector::class);

            // 1. Explicit language request (e.g. ?lang=bn or ?lang=en)
            if (request()->has('lang')) {
                $reqLang = strtolower((string) request('lang'));
                if ($detector->isLanguageSupported($reqLang)) {
                    session(['language' => $reqLang, 'manual_language' => true]);
                    if (! app()->runningInConsole()) {
                        cookie()->queue(cookie('site_language', $reqLang, 525600, '/', null, null, false));
                        cookie()->queue(cookie('user_manual_lang', '1', 525600, '/', null, null, false));
                    }
                    return $reqLang;
                }
            }

            // 2. Explicit or previously set session
            if (session()->has('language')) {
                $sessLang = (string) session('language');
                if ($detector->isLanguageSupported($sessLang)) {
                    return $sessLang;
                }
            }

            // 3. Persistent cookie from past visit or explicit choice
            if (request()->hasCookie('site_language')) {
                $cookieLang = (string) request()->cookie('site_language');
                if ($detector->isLanguageSupported($cookieLang)) {
                    session(['language' => $cookieLang]);
                    return $cookieLang;
                }
            }

            // 4. Automatic Location & Locale Detection (Google-style zero prompt)
            $detected = $detector->detect(request());
            if (! $detector->isLanguageSupported($detected)) {
                $detected = 'en';
            }

            session(['language' => $detected]);
            if (! app()->runningInConsole()) {
                cookie()->queue(cookie('site_language', $detected, 525600, '/', null, null, false));
            }

            return $detected;
        } catch (\Throwable) {
            return 'en';
        }
    }
}

if (! function_exists('localizeNumber')) {
    function localizeNumber(mixed $number): string
    {
        $string = (string) $number;
        if (getLanguage() !== 'bn') {
            return $string;
        }

        $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

        return str_replace($enDigits, $bnDigits, $string);
    }
}

if (! function_exists('getRole')) {
    function getRole(): ?string
    {
        $admin = Auth::guard('admin')->user();

        return $admin?->getRoleNames()->first();
    }
}

if (! function_exists('checkPermission')) {
    function checkPermission(string $permission): bool
    {
        $admin = Auth::guard('admin')->user();

        return (bool) $admin?->can($permission);
    }
}

if (! function_exists('canAccess')) {
    function canAccess(array $permissions): bool
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return false;
        }

        if ($admin->hasRole('Super Admin')) {
            return true;
        }

        return $admin->hasAnyPermission($permissions);
    }
}

if (! function_exists('truncate')) {
    function truncate(?string $text, int $limit = 60, string $end = '...'): string
    {
        return e(Str::limit(strip_tags((string) $text), $limit, $end));
    }
}

if (! function_exists('convertToKFormat')) {
    function convertToKFormat(null|int|float|string $number): string
    {
        $number = max(0, (float) $number);

        if ($number >= 1000000) {
            $formatted = rtrim(rtrim(number_format($number / 1000000, 1), '0'), '.').'M';
        } elseif ($number >= 1000) {
            $formatted = rtrim(rtrim(number_format($number / 1000, 1), '0'), '.').'K';
        } else {
            $formatted = (string) (int) $number;
        }

        return localizeNumber($formatted);
    }
}

if (! function_exists('setSidebarActive')) {
    function setSidebarActive(array|string $routes, string $activeClass = 'active'): string
    {
        foreach ((array) $routes as $route) {
            if (request()->routeIs($route)) {
                return $activeClass;
            }
        }

        return '';
    }
}

if (! function_exists('normalizeStoredUrl')) {
    /**
     * Normalise a URL that was stored in the database.
     *
     * Editors frequently paste development URLs (http://localhost:8000/... or
     * http://something.test/...) into footer menus and social links. Those leak
     * into production, break navigation and send crawlers to dead hosts. Any dev
     * host is rewritten to the live application URL while the path, query string
     * and fragment are preserved.
     */
    function normalizeStoredUrl(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '#';
        }

        // Relative paths, in-page anchors and non-http schemes are already valid.
        if (str_starts_with($url, '/') || str_starts_with($url, '#')
            || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            return $url;
        }

        $parts = parse_url($url);

        if ($parts === false || empty($parts['host'])) {
            return $url;
        }

        $host = strtolower($parts['host']);

        $devHosts = ['localhost', '127.0.0.1', '0.0.0.0', '::1', 'host.docker.internal'];

        $isDevHost = in_array($host, $devHosts, true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');

        if (! $isDevHost) {
            return $url;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return url($path).$query.$fragment;
    }
}

if (! function_exists('localizeDate')) {
    function localizeDate(mixed $date = null, string $format = 'M d, Y'): string
    {
        $timestamp = $date ? (is_numeric($date) ? (int)$date : strtotime((string)$date)) : time();
        if (! $timestamp) {
            $timestamp = time();
        }

        $formatted = date($format, $timestamp);

        if (getLanguage() !== 'bn') {
            return $formatted;
        }

        $enDays = ['Friday', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu'];
        $bnDays = ['শুক্রবার', 'শনিবার', 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্র', 'শনি', 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহঃ'];

        $enMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $bnMonths = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর', 'জানু', 'ফেব্রু', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টে', 'অক্টো', 'নভে', 'ডিসে'];

        $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

        $translated = str_replace($enDays, $bnDays, $formatted);
        $translated = str_replace($enMonths, $bnMonths, $translated);
        $translated = str_replace($enDigits, $bnDigits, $translated);

        return $translated;
    }
}

