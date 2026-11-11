<!DOCTYPE html>
<html lang="{{ getLanguage() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="google-site-verification" content="7D5GsLCJIj5u-4aD5whqMuZuQK5y5czs2M-JKQ6Qybk" />

    <!-- Google Tag Manager -->
    <script async src="https://www.googletagmanager.com/gtm.js?id=GTM-MLNZDWRF"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    </script>
    <!-- End Google Tag Manager -->

    <!-- Google Consent Mode v2 (GDPR / EEA / UK / California AdSense Compliance) -->
    <script>
        (function() {
            var consent = null;
            try {
                consent = localStorage.getItem('cookie_consent') ||
                    (document.cookie.match(/(?:^|;\s*)cookie_consent=([^;]+)/) || [])[1];
            } catch(e) {}

            if (consent === 'accepted') {
                gtag('consent', 'default', {
                    'ad_storage': 'granted',
                    'ad_user_data': 'granted',
                    'ad_personalization': 'granted',
                    'analytics_storage': 'granted',
                    'functionality_storage': 'granted',
                    'personalization_storage': 'granted',
                    'security_storage': 'granted',
                    'wait_for_update': 500
                });
            } else {
                gtag('consent', 'default', {
                    'ad_storage': 'denied',
                    'ad_user_data': 'denied',
                    'ad_personalization': 'denied',
                    'analytics_storage': 'denied',
                    'functionality_storage': 'granted',
                    'personalization_storage': 'denied',
                    'security_storage': 'granted',
                    'wait_for_update': 500
                });
            }
        })();

        gtag('config', 'G-HM30KCBC8K');
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-HM30KCBC8K"></script>

    <title>@hasSection('title') @yield('title') @else {{ $settings['site_seo_title'] ?? 'Extremis News - Verified News & Analysis' }} @endif</title>
    <meta name="description" content="@hasSection('meta_description') @yield('meta_description') @else {{ $settings['site_seo_description'] ?? 'Extremis News delivers breaking global news, investigative reports, and in-depth analysis.' }} @endif" />
    <meta name="keywords" content="@yield('meta_keywords', $settings['site_seo_keywords'] ?? 'news, breaking news, technology, world affairs, economy')" />
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')" />
    <link rel="canonical" href="@yield('canonical_url', url()->current())" />

    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="{{ $settings['site_name'] ?? 'Extremis News' }}" />
    <meta property="og:type" content="@yield('og_type', 'website')" />
    <meta property="og:url" content="@yield('canonical_url', url()->current())" />
    <meta property="og:locale" content="{{ getLanguage() === 'bn' ? 'bn_BD' : 'en_US' }}" />
    <meta property="og:title" content="@yield('meta_og_title', View::yieldContent('title', $settings['site_seo_title'] ?? ($settings['site_name'] ?? 'Extremis News')))" />
    <meta property="og:description" content="@yield('meta_og_description', View::yieldContent('meta_description', $settings['site_seo_description'] ?? ''))" />
    <meta property="og:image" content="@yield('meta_og_image', asset($settings['site_logo']))" />
    @hasSection('article_published_time')
    <meta property="article:published_time" content="@yield('article_published_time')" />
    @endif
    @hasSection('article_modified_time')
    <meta property="article:modified_time" content="@yield('article_modified_time')" />
    @endif
    @hasSection('article_section')
    <meta property="article:section" content="@yield('article_section')" />
    @endif
    @hasSection('article_author')
    <meta property="article:author" content="@yield('article_author')" />
    @endif

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('meta_tw_title', View::yieldContent('title', $settings['site_seo_title'] ?? ($settings['site_name'] ?? 'Extremis News')))" />
    <meta name="twitter:description" content="@yield('meta_tw_description', View::yieldContent('meta_description', $settings['site_seo_description'] ?? ''))" />
    <meta name="twitter:image" content="@yield('meta_tw_image', View::yieldContent('meta_og_image', asset($settings['site_logo'])))" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Sitemaps & RSS Feeds -->
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}" />
    <link rel="alternate" type="application/rss+xml" title="{{ $settings['site_name'] ?? 'Extremis News' }} - RSS Feed" href="{{ route('feed') }}" />

    <!-- Google AdSense Verification & Auto-Ads -->
    <meta name="google-adsense-account" content="ca-pub-7830531573570855">
    @php
        $adSensePubId = config('services.adsense.publisher_id') ?: env('ADSENSE_PUBLISHER_ID', 'pub-7830531573570855');
    @endphp
    @if (!empty($adSensePubId))
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-{{ str_replace('ca-', '', trim($adSensePubId)) }}" crossorigin="anonymous"></script>
    @endif

    <!-- JSON-LD Global Site Schema -->
    <script type="application/ld+json">
    {!! json_encode([
        '@' . 'context' => 'https://schema.org',
        '@type' => 'NewsMediaOrganization',
        'name' => $settings['site_name'] ?? 'Extremis News',
        'url' => url('/'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset($settings['site_logo'] ?? ''),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode([
        '@' . 'context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $settings['site_name'] ?? 'Extremis News',
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => route('news') . '?search={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <link rel="icon" href="{{ asset($settings['site_favicon']) }}" type="image/png">
    <link href="{{ asset('frontend/asset/css/styles.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"/>

    <style>
        :root {
            --colorPrimary: {{ $settings['site_color'] }};
        }
    </style>
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MLNZDWRF"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- Global Variables -->
    @php
        $socialLinks = \App\Models\SocialLink::where('status', 1)->get();
        $footerInfo = \App\Models\FooterInfo::where('language', getLanguage())->first();
        $footerGridOne = \App\Models\FooterGridOne::where(['status' => 1, 'language' => getLanguage()])->get();
        $footerGridTwo = \App\Models\FooterGridTwo::where(['status' => 1, 'language' => getLanguage()])->get();
        $footerGridThree = \App\Models\FooterGridThree::where(['status' => 1, 'language' => getLanguage()])->get();
        $footerGridOneTitle = \App\Models\FooterTitle::where(['key' => 'grid_one_title', 'language' => getLanguage()])->first();
        $footerGridTwoTitle = \App\Models\FooterTitle::where(['key' => 'grid_two_title', 'language' => getLanguage()])->first();
        $footerGridThreeTitle = \App\Models\FooterTitle::where(['key' => 'grid_three_title', 'language' => getLanguage()])->first();
    @endphp

    <!-- Header news -->
    @include('frontend.layouts.header')
    <!-- End Header news -->


    @yield('content')

    {{-- Footer Section --}}
    @include('frontend.layouts.footer')

    {{-- End Footer Section --}}

    <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up"></i></a>

    <script type="text/javascript" src="{{ asset('frontend/asset/js/index.bundle.js') }}"></script>
    @include('sweetalert::alert')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })


        // Add csrf token in ajax request
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            /** Ensure user's manual language choice is strictly respected **/
            (function initializeLanguagePreference() {
                var manualLang = false;
                try {
                    manualLang = localStorage.getItem('manual_language') === 'true';
                } catch(e) {}

                var cookieVal = (document.cookie.match(/(?:^|;\s*)site_language=([^;]+)/) || [])[1];
                var manualCookie = (document.cookie.match(/(?:^|;\s*)user_manual_lang=([^;]+)/) || [])[1];
                var currentServerLang = "{{ getLanguage() }}";

                // If user previously made a manual choice, enforce and protect it
                if (manualLang || manualCookie === '1') {
                    try {
                        var savedLang = localStorage.getItem('site_language') || cookieVal;
                        if (savedLang && savedLang !== currentServerLang) {
                            // Synchronize server to the user's explicit saved choice
                            $.ajax({
                                method: 'GET',
                                url: "{{ route('language') }}",
                                data: { language_code: savedLang },
                                success: function(data) {
                                    if (data.status === 'success') {
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    } catch(e) {}
                    return;
                }

                // First-time visit: record the server-detected language into cookie & localStorage without reload
                try {
                    localStorage.setItem('site_language', currentServerLang);
                } catch(e) {}
                document.cookie = "site_language=" + encodeURIComponent(currentServerLang) + "; path=/; max-age=31536000; SameSite=Lax";
            })();

            /** Change language manually - permanently saves choice **/
            $('#site-language').on('change', function() {
                let languageCode = $(this).val();

                try {
                    localStorage.setItem('manual_language', 'true');
                    localStorage.setItem('site_language', languageCode);
                } catch(e) {}

                document.cookie = "site_language=" + encodeURIComponent(languageCode) + "; path=/; max-age=31536000; SameSite=Lax";
                document.cookie = "user_manual_lang=1; path=/; max-age=31536000; SameSite=Lax";

                $.ajax({
                    method: 'GET',
                    url: "{{ route('language') }}",
                    data: {
                        language_code: languageCode
                    },
                    success: function(data) {
                        if (data.status === 'success') {
                            window.location.reload();
                        }
                    },
                    error: function(data) {
                        console.error(data);
                        window.location.reload();
                    }
                });
            });

            /** Subscribe Newsletter**/
            $('.newsletter-form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    method: 'POST',
                    url: "{{ route('subscribe-newsletter') }}",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('.newsletter-button').text('loading...');
                        $('.newsletter-button').attr('disabled', true);
                    },
                    success: function(data) {
                        if (data.status === 'success') {
                            Toast.fire({
                                icon: 'success',
                                title: data.message
                            })
                            $('.newsletter-form')[0].reset();
                            $('.newsletter-button').text('sign up');

                            $('.newsletter-button').attr('disabled', false);
                        }
                    },
                    error: function(data) {
                        $('.newsletter-button').text('sign up');
                        $('.newsletter-button').attr('disabled', false);

                        if (data.status === 422) {
                            let errors = data.responseJSON.errors;
                            $.each(errors, function(index, value) {
                                Toast.fire({
                                    icon: 'error',
                                    title: value[0]
                                })
                            })
                        }
                    }
                })
            })
        })
    </script>



    {{-- GDPR / AdSense Cookie Consent Banner --}}
    @include('frontend.layouts.cookie-banner')

    @stack('content')
</body>

</html>
