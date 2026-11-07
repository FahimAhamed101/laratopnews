<footer class="footer-custom bg__footer-dark text-white pt-5 pb-0">
    <div class="container">
        <div class="row pb-4">
            <!-- Col 1: Brand & Bio -->
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <div class="footer-brand mb-3">
                    @php
                        $footerLogo = (!empty(@$footerInfo->logo) && file_exists(public_path(@$footerInfo->logo)))
                            ? @$footerInfo->logo
                            : ($settings['site_logo'] ?? null);
                    @endphp
                    @if (!empty($footerLogo) && file_exists(public_path($footerLogo)))
                        <a href="{{ route('home') }}" class="d-inline-block">
                            <img src="{{ asset($footerLogo) }}?v={{ filemtime(public_path($footerLogo)) }}" alt="{{ $settings['site_name'] ?? 'Extremis News' }}" style="max-height: 48px; width: auto; object-fit: contain;">
                        </a>
                    @elseif (!empty($footerLogo))
                        <a href="{{ route('home') }}" class="d-inline-block">
                            <img src="{{ asset($footerLogo) }}" alt="{{ $settings['site_name'] ?? 'Extremis News' }}" style="max-height: 48px; width: auto; object-fit: contain;">
                        </a>
                    @else
                        <a href="{{ route('home') }}" class="text-white text-decoration-none">
                            <h3 class="font-weight-bold mb-0 text-white tracking-wide">{{ $settings['site_name'] ?? 'Extremis News' }}</h3>
                        </a>
                    @endif
                </div>

                <p class="footer-bio text-white-50 mb-3" style="font-size: 14px; line-height: 1.75; max-width: 360px;">
                    {{ @$footerInfo->description ?: 'Extremis News delivers timely, verified reporting and deep analysis on global and local affairs.' }}
                </p>

                <!-- Social Media Horizontal Row -->
                <div class="footer-social-wrap">
                    @if ($socialLinks && count($socialLinks) > 0)
                        @foreach ($socialLinks as $link)
                            <a href="{{ normalizeStoredUrl($link->url) }}" target="_blank" rel="noopener noreferrer" class="footer-social-btn" title="{{ $link->name ?? 'Social Link' }}">
                                <i class="{{ $link->icon }}"></i>
                            </a>
                        @endforeach
                    @else
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="footer-social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="footer-social-btn"><i class="fab fa-twitter"></i></a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="footer-social-btn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="footer-social-btn"><i class="fab fa-youtube"></i></a>
                    @endif
                </div>
            </div>

            <!-- Col 2: Grid One (Company) -->
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4 mb-lg-0">
                <div class="footer-nav-widget">
                    <h5 class="footer-widget-title text-white font-weight-bold position-relative pb-2 mb-3">
                        {{ @$footerGridOneTitle->value ?: 'Company' }}
                    </h5>
                    <ul class="list-unstyled footer-nav-links mb-0">
                        @if ($footerGridOne && count($footerGridOne) > 0)
                            @foreach ($footerGridOne as $gridOne)
                                <li>
                                    <a href="{{ normalizeStoredUrl($gridOne->url) }}">
                                        <i class="fa fa-angle-right mr-2 text-white-50"></i>{{ $gridOne->name }}
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li><a href="{{ route('home') }}"><i class="fa fa-angle-right mr-2 text-white-50"></i>{{ __('frontend.Home') }}</a></li>
                            <li><a href="{{ route('news') }}"><i class="fa fa-angle-right mr-2 text-white-50"></i>{{ __('frontend.Latest News') }}</a></li>
                            <li><a href="{{ route('about') }}"><i class="fa fa-angle-right mr-2 text-white-50"></i>{{ __('frontend.About Us') }}</a></li>
                            <li><a href="{{ route('contact') }}"><i class="fa fa-angle-right mr-2 text-white-50"></i>{{ __('frontend.Contact Us') }}</a></li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Col 3: Grid Two (Categories) -->
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4 mb-lg-0">
                <div class="footer-nav-widget">
                    <h5 class="footer-widget-title text-white font-weight-bold position-relative pb-2 mb-3">
                        {{ @$footerGridTwoTitle->value ?: 'Categories' }}
                    </h5>
                    <ul class="list-unstyled footer-nav-links mb-0">
                        @if ($footerGridTwo && count($footerGridTwo) > 0)
                            @foreach ($footerGridTwo as $gridTwo)
                                <li>
                                    <a href="{{ normalizeStoredUrl($gridTwo->url) }}">
                                        <i class="fa fa-angle-right mr-2 text-white-50"></i>{{ $gridTwo->name }}
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li><a href="{{ route('news') }}?category=national"><i class="fa fa-angle-right mr-2 text-white-50"></i>National</a></li>
                            <li><a href="{{ route('news') }}?category=business"><i class="fa fa-angle-right mr-2 text-white-50"></i>Business</a></li>
                            <li><a href="{{ route('news') }}?category=technology"><i class="fa fa-angle-right mr-2 text-white-50"></i>Technology</a></li>
                            <li><a href="{{ route('news') }}?category=sports"><i class="fa fa-angle-right mr-2 text-white-50"></i>Sports</a></li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Col 4: Grid Three (Quick Links) -->
            <div class="col-lg-2 col-md-6 col-sm-6 mb-4 mb-lg-0">
                <div class="footer-nav-widget">
                    <h5 class="footer-widget-title text-white font-weight-bold position-relative pb-2 mb-3">
                        {{ @$footerGridThreeTitle->value ?: 'Quick Links' }}
                    </h5>
                    <ul class="list-unstyled footer-nav-links mb-0">
                        @if ($footerGridThree && count($footerGridThree) > 0)
                            @foreach ($footerGridThree as $gridThree)
                                <li>
                                    <a href="{{ normalizeStoredUrl($gridThree->url) }}">
                                        <i class="fa fa-angle-right mr-2 text-white-50"></i>{{ $gridThree->name }}
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li><a href="{{ route('privacy-policy') }}"><i class="fa fa-angle-right mr-2 text-white-50"></i>Privacy Policy</a></li>
                            <li><a href="{{ route('terms-of-service') }}"><i class="fa fa-angle-right mr-2 text-white-50"></i>Terms of Service</a></li>
                            <li><a href="{{ route('sitemap') }}"><i class="fa fa-angle-right mr-2 text-white-50"></i>Sitemap</a></li>
                            <li><a href="{{ route('feed') }}" target="_blank"><i class="fa fa-rss mr-2 text-white-50"></i>RSS Feed</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom Strip -->
    <div class="footer-bottom-strip py-3" style="border-top: 1px solid rgba(255, 255, 255, 0.08); background: rgba(0, 0, 0, 0.25);">
        <div class="container">
            <div class="row align-items-center">
                <!-- Copyright Fahim -->
                <div class="col-lg-6 col-md-5 text-center text-md-left mb-2 mb-md-0">
                    <p class="mb-0 text-white-50 footer-copyright-text">
                        {{ localizeNumber(@$footerInfo->copyright ?: ('Copyright © ' . date('Y') . ' Fahim. All rights reserved.')) }}
                    </p>
                </div>

                <!-- Footer Bottom Legal Links (Clean Horizontal Row) -->
                <div class="col-lg-6 col-md-7 text-center text-md-right">
                    <div class="footer-legal-links">
                        <a href="{{ route('privacy-policy') }}" class="footer-sub-link">Privacy Policy</a>
                        <span class="footer-link-divider">&bull;</span>
                        <a href="{{ route('terms-of-service') }}" class="footer-sub-link">Terms of Service</a>
                        <span class="footer-link-divider">&bull;</span>
                        <a href="{{ route('sitemap') }}" class="footer-sub-link" target="_blank">Sitemap</a>
                        <span class="footer-link-divider">&bull;</span>
                        <a href="javascript:void(0)" id="open-cookie-settings" class="footer-sub-link">{{ __('frontend.cookie_preferences') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.footer-custom {
    background: #0b1120 !important;
    font-family: inherit;
    border-top: 2px solid var(--colorPrimary, #dc2626);
}
.footer-widget-title {
    font-size: 16px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.footer-widget-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 32px;
    height: 2px;
    background: var(--colorPrimary, #dc2626);
    border-radius: 2px;
}
.footer-nav-links {
    padding-left: 0 !important;
    list-style: none !important;
}
.footer-nav-links li {
    margin-bottom: 10px !important;
    width: 100% !important;
    display: block !important;
}
.footer-nav-links li a {
    color: rgba(255, 255, 255, 0.7) !important;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.25s ease-in-out;
    display: inline-block;
}
.footer-nav-links li a:hover {
    color: #ffffff !important;
    transform: translateX(4px);
    text-decoration: none;
}
.footer-nav-links li a i {
    font-size: 12px;
    transition: color 0.25s ease;
}
.footer-nav-links li a:hover i {
    color: var(--colorPrimary, #dc2626) !important;
}

/* Social Media Icons: Guaranteed Horizontal Row */
.footer-social-wrap {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 10px !important;
    margin-top: 15px !important;
    padding: 0 !important;
}
.footer-social-btn {
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    max-width: 38px !important;
    border-radius: 50% !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(255, 255, 255, 0.08) !important;
    color: #ffffff !important;
    font-size: 14px !important;
    text-decoration: none !important;
    transition: all 0.25s ease !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    margin: 0 !important;
    padding: 0 !important;
}
.footer-social-btn:hover {
    background: var(--colorPrimary, #dc2626) !important;
    border-color: var(--colorPrimary, #dc2626) !important;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    text-decoration: none !important;
}

/* Footer Bottom: Guaranteed Horizontal Legal Links */
.footer-copyright-text {
    font-size: 13.5px !important;
    line-height: 1.5 !important;
}
.footer-legal-links {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 14px !important;
    margin: 0 !important;
    padding: 0 !important;
}
.footer-link-divider {
    color: rgba(255, 255, 255, 0.35) !important;
    font-size: 12px !important;
    line-height: 1 !important;
    user-select: none !important;
    margin: 0 !important;
}
.footer-sub-link {
    color: rgba(255, 255, 255, 0.7) !important;
    font-size: 13px !important;
    line-height: 1.5 !important;
    text-decoration: none !important;
    transition: color 0.2s ease !important;
    white-space: nowrap !important;
    margin: 0 !important;
}
.footer-sub-link:hover {
    color: #ffffff !important;
    text-decoration: underline !important;
}

@media (max-width: 767.98px) {
    .footer-custom {
        text-align: left;
    }
    .footer-widget-title {
        margin-top: 15px;
    }
    .footer-legal-links {
        justify-content: center !important;
        gap: 10px !important;
        margin-top: 8px !important;
    }
}
</style>
