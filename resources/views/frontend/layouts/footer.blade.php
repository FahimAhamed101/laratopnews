<section class="wrapper__section p-0">
    <div class="wrapper__section__components">
        <!-- Footer -->
        <footer>
            <div class="wrapper__footer bg__footer-dark pb-0">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="widget__footer">
                                <figure class="image-logo">
                                    @php
                                        $footerLogo = (!empty(@$footerInfo->logo) && file_exists(public_path(@$footerInfo->logo)))
                                            ? @$footerInfo->logo
                                            : ($settings['site_logo'] ?? null);
                                    @endphp
                                    @if (!empty($footerLogo) && file_exists(public_path($footerLogo)))
                                        <img src="{{ asset($footerLogo) }}?v={{ filemtime(public_path($footerLogo)) }}" alt="{{ $settings['site_name'] ?? 'Extremis News' }}" class="logo-footer">
                                    @elseif (!empty($footerLogo))
                                        <img src="{{ asset($footerLogo) }}" alt="{{ $settings['site_name'] ?? 'Extremis News' }}" class="logo-footer">
                                    @else
                                        <h2 class="text-white font-weight-bold">{{ $settings['site_name'] ?? 'Extremis News' }}</h2>
                                    @endif
                                </figure>

                                <p>{{ @$footerInfo->description }}</p>


                                <div class="social__media mt-4">
                                    <ul class="list-inline">
                                        @foreach ($socialLinks as $link)
                                        <li class="list-inline-item">
                                            <a href="{{ normalizeStoredUrl($link->url) }}" class="btn btn-social rounded text-white facebook">
                                                <i class="{{ $link->icon }}"></i>
                                            </a>
                                        </li>
                                        @endforeach

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="widget__footer">
                                <div class="dropdown-footer">
                                    <h4 class="footer-title">
                                        {{ @$footerGridOneTitle->value }}
                                        <span class="fa fa-angle-down"></span>
                                    </h4>

                                </div>

                                <ul class="list-unstyled option-content is-hidden">

                                    @foreach ($footerGridOne as $gridOne)
                                    <li>
                                        <a href="{{ normalizeStoredUrl($gridOne->url) }}">{{ $gridOne->name }}</a>
                                    </li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="widget__footer">
                                <div class="dropdown-footer">
                                    <h4 class="footer-title">
                                        {{ @$footerGridTwoTitle->value }}
                                        <span class="fa fa-angle-down"></span>
                                    </h4>

                                </div>
                                <ul class="list-unstyled option-content is-hidden">

                                    @foreach ($footerGridTwo as $gridTwo)
                                    <li>
                                        <a href="{{ normalizeStoredUrl($gridTwo->url) }}">{{ $gridTwo->name }}</a>
                                    </li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="widget__footer">
                                <div class="dropdown-footer">
                                    <h4 class="footer-title">
                                        {{ @$footerGridThreeTitle->value }}
                                        <span class="fa fa-angle-down"></span>
                                    </h4>

                                </div>

                                <ul class="list-unstyled option-content is-hidden">
                                    @foreach ($footerGridThree as $gridThree)
                                    <li>
                                        <a href="{{ normalizeStoredUrl($gridThree->url) }}">{{ $gridThree->name }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer bottom -->
            <div class="wrapper__footer-bottom bg__footer-dark">
                <div class="container ">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="border-top-1 bg__footer-bottom-section d-flex flex-wrap justify-content-between align-items-center py-3">
                                <p class="text-white mb-0">
                                    {{ localizeNumber(@$footerInfo->copyright) }}</p>
                                <ul class="list-inline mb-0 small">
                                    <li class="list-inline-item"><a href="{{ route('privacy-policy') }}" class="text-white-50">Privacy Policy</a></li>
                                    <li class="list-inline-item text-white-50">|</li>
                                    <li class="list-inline-item"><a href="{{ route('terms-of-service') }}" class="text-white-50">Terms of Service</a></li>
                                    <li class="list-inline-item text-white-50">|</li>
                                    <li class="list-inline-item"><a href="{{ route('sitemap') }}" class="text-white-50" target="_blank">Sitemap</a></li>
                                    <li class="list-inline-item text-white-50">|</li>
                                    <li class="list-inline-item"><a href="javascript:void(0)" id="open-cookie-settings" class="text-white-50">{{ __('frontend.cookie_preferences') }}</a></li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </footer>
    </div>
</section>
