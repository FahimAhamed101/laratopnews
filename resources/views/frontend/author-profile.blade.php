@extends('frontend.layouts.master')

@php
    $siteName = $settings['site_name'] ?? 'Extremis News';
    $designation = $author->designation ?: 'Editorial Staff & Correspondent';
    $pageTitle = $author->name . ' - ' . $designation . ' | ' . $siteName;
    $pageDesc = $author->bio ?: "Read verified news reports, investigative features, and in-depth analysis by {$author->name} on {$siteName}.";
    $authorUrl = route('author.profile', ['id' => $author->id, 'slug' => Str::slug($author->name)]);
    $authorImage = !empty($author->image) ? asset($author->image) : asset($settings['site_logo'] ?? '');

    $socialLinksArray = array_values(array_filter([
        $author->website,
        $author->twitter,
        $author->linkedin,
        $author->facebook,
        $author->instagram,
    ]));
@endphp

@section('title', $pageTitle)
@section('meta_description', Str::limit($pageDesc, 160))
@section('canonical_url', $authorUrl)
@section('meta_og_title', $pageTitle)
@section('meta_og_description', Str::limit($pageDesc, 160))
@section('meta_og_image', $authorImage)
@section('meta_tw_title', $pageTitle)
@section('meta_tw_description', Str::limit($pageDesc, 160))
@section('meta_tw_image', $authorImage)

@push('content')
<!-- Schema.org Person Structured Data -->
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $author->name,
    'jobTitle' => $designation,
    'description' => $pageDesc,
    'image' => $authorImage,
    'url' => $authorUrl,
    'sameAs' => $socialLinksArray,
    'worksFor' => [
        '@type' => 'NewsMediaOrganization',
        'name' => $siteName,
        'url' => url('/'),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<!-- Schema.org BreadcrumbList -->
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => __('frontend.Home'),
            'item' => route('home'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => __('frontend.author'),
            'item' => route('news'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $author->name,
            'item' => $authorUrl,
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
    <section class="blog_pages pt-4 pb-80">
        <div class="container">
            <!-- Breadcrumbs -->
            <div class="row">
                <div class="col-md-12">
                    <ul class="breadcrumbs bg-light mb-4">
                        <li class="breadcrumbs__item">
                            <a href="{{ route('home') }}" class="breadcrumbs__url">
                                <i class="fa fa-home"></i> {{ __('frontend.Home') }}
                            </a>
                        </li>
                        <li class="breadcrumbs__item">
                            <span class="breadcrumbs__url">{{ __('frontend.author') }}</span>
                        </li>
                        <li class="breadcrumbs__item">
                            <span class="text-muted">{{ $author->name }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Author Hero Banner Card -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="author-hero-card rounded shadow-sm border overflow-hidden" style="background: #ffffff;">
                        <div class="author-hero-cover" style="height: 140px; background: linear-gradient(135deg, #0b1120 0%, #1e293b 50%, var(--colorPrimary, #dc2626) 100%);"></div>
                        <div class="author-hero-body px-4 pb-4 position-relative" style="margin-top: -65px;">
                            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end justify-content-between mb-4">
                                <div class="d-flex flex-column flex-md-row align-items-center text-center text-md-left">
                                    <div class="author-avatar-wrap position-relative mr-md-4 mb-3 mb-md-0">
                                        <img src="{{ asset($author->image) }}" alt="{{ $author->name }}" class="rounded-circle border border-white shadow" style="width: 130px; height: 130px; object-fit: cover; background: #fff; border-width: 4px !important;">
                                        <span class="author-verified-badge" title="Verified Journalist" style="position: absolute; bottom: 8px; right: 8px; background: #2563eb; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; border: 2px solid #fff;">
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center justify-content-center justify-content-md-start flex-wrap" style="gap: 10px;">
                                            <h1 class="font-weight-bold text-dark mb-0" style="font-size: 26px;">{{ $author->name }}</h1>
                                            <span class="badge badge-primary py-1 px-2 font-weight-bold" style="font-size: 12px;">
                                                {{ $designation }}
                                            </span>
                                        </div>
                                        <p class="text-muted small mt-1 mb-0">
                                            <i class="fa fa-building mr-1"></i> Extremis News Editorial Staff &bull; Member since {{ localizeDate($author->created_at, 'M Y') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Author Social Media Pills -->
                                <div class="author-social-links mt-3 mt-md-0 d-flex flex-wrap justify-content-center" style="gap: 8px;">
                                    @if ($author->website)
                                        <a href="{{ normalizeStoredUrl($author->website) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary rounded-circle" title="Website" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-globe"></i>
                                        </a>
                                    @endif
                                    @if ($author->twitter)
                                        <a href="{{ normalizeStoredUrl($author->twitter) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-circle" title="Twitter / X" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    @endif
                                    @if ($author->linkedin)
                                        <a href="{{ normalizeStoredUrl($author->linkedin) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info rounded-circle" title="LinkedIn" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fab fa-linkedin-in"></i>
                                        </a>
                                    @endif
                                    @if ($author->facebook)
                                        <a href="{{ normalizeStoredUrl($author->facebook) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-circle" title="Facebook" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                    @endif
                                    @if ($author->instagram)
                                        <a href="{{ normalizeStoredUrl($author->instagram) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger rounded-circle" title="Instagram" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Bio Description -->
                            @if (!empty($author->bio))
                                <div class="author-bio-content p-3 rounded" style="background: #f8fafc; border-left: 3px solid var(--colorPrimary, #dc2626);">
                                    <p class="mb-0 text-dark" style="font-size: 14.5px; line-height: 1.7;">
                                        {{ $author->bio }}
                                    </p>
                                </div>
                            @endif

                            <!-- Author Metrics Bar -->
                            <div class="row text-center mt-4 pt-3 border-top">
                                <div class="col-4 border-right">
                                    <h4 class="font-weight-bold text-dark mb-0">{{ localizeNumber($totalArticles) }}</h4>
                                    <small class="text-muted text-uppercase tracking-wide" style="font-size: 11px;">{{ __('frontend.Published Articles') }}</small>
                                </div>
                                <div class="col-4 border-right">
                                    <h4 class="font-weight-bold text-dark mb-0">{{ convertToKFormat($totalViews) }}</h4>
                                    <small class="text-muted text-uppercase tracking-wide" style="font-size: 11px;">{{ __('frontend.Total Article Reads') }}</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="font-weight-bold text-dark mb-0">100%</h4>
                                    <small class="text-muted text-uppercase tracking-wide" style="font-size: 11px;">{{ __('frontend.Verified Reporter') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Articles List & Sidebar -->
            <div class="row">
                <!-- Articles Column -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h3 class="font-weight-bold text-dark mb-0" style="font-size: 20px;">
                            <i class="fa fa-newspaper text-primary mr-2"></i> {{ __('frontend.Published Stories') }}
                        </h3>
                        <span class="badge badge-secondary py-1 px-2 font-weight-bold">
                            {{ localizeNumber($news->total()) }} {{ __('frontend.News') }}
                        </span>
                    </div>

                    @if ($news->count() > 0)
                        <div class="row">
                            @foreach ($news as $item)
                                @php
                                    $itemWords = str_word_count(strip_tags($item->content));
                                    $itemReadTime = max(1, ceil($itemWords / 220));
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm border rounded overflow-hidden" style="transition: transform 0.25s ease, box-shadow 0.25s ease;">
                                        <div class="position-relative overflow-hidden" style="height: 190px;">
                                            <a href="{{ route('news-details', $item->slug) }}">
                                                <img src="{{ asset($item->image) }}" alt="{{ $item->title }}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.3s ease;">
                                            </a>
                                            @if ($item->category)
                                                <span class="badge badge-primary position-absolute" style="top: 12px; left: 12px; font-size: 11px; padding: 4px 8px; z-index: 2;">
                                                    {{ $item->category->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="card-body d-flex flex-column p-3">
                                            <div class="text-muted small mb-2 d-flex align-items-center justify-content-between">
                                                <span><i class="fa fa-calendar-alt mr-1"></i> {{ localizeDate($item->created_at, 'M d, Y') }}</span>
                                                <span><i class="fa fa-clock mr-1"></i> {{ $itemReadTime }}m read</span>
                                            </div>
                                            <h5 class="card-title font-weight-bold mb-2" style="font-size: 16px; line-height: 1.4;">
                                                <a href="{{ route('news-details', $item->slug) }}" class="text-dark text-decoration-none">
                                                    {!! truncate($item->title, 65) !!}
                                                </a>
                                            </h5>
                                            <p class="card-text text-muted small mb-3 flex-grow-1" style="line-height: 1.6;">
                                                {!! truncate(strip_tags($item->content), 95) !!}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                <span class="text-muted small"><i class="fa fa-eye mr-1"></i> {{ number_format($item->views) }} views</span>
                                                <a href="{{ route('news-details', $item->slug) }}" class="text-primary font-weight-bold small text-decoration-none">
                                                    Read Story <i class="fa fa-arrow-right ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination Links -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $news->links() }}
                        </div>
                    @else
                        <div class="text-center py-5 bg-white rounded border">
                            <i class="fa fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5 class="font-weight-bold text-dark">No Articles Found</h5>
                            <p class="text-muted">This author has not published any stories yet. Please check back soon.</p>
                            <a href="{{ route('home') }}" class="btn btn-primary mt-2">
                                <i class="fa fa-home mr-1"></i> Return to Homepage
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Right Sidebar Column -->
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="sticky-top" style="top: 20px;">
                        <!-- Categories Widget -->
                        <aside class="wrapper__list__article mb-4">
                            <h4 class="border_section">{{ __('frontend.Categories') }}</h4>
                            <div class="widget__category">
                                <ul class="list-unstyled mb-0">
                                    @foreach ($categories as $cat)
                                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <a href="{{ route('news', ['category' => $cat->slug]) }}" class="text-dark text-decoration-none font-weight-bold" style="font-size: 14px;">
                                                <i class="fa fa-folder text-primary mr-2"></i> {{ $cat->name }}
                                            </a>
                                            <span class="badge badge-light badge-pill">
                                                {{ $cat->news()->count() }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </aside>

                        <!-- Newsletter Widget -->
                        <aside class="wrapper__list__article mb-4">
                            <h4 class="border_section">{{ __('frontend.newsletter') }}</h4>
                            <div class="widget__form-subscribe bg__card-shadow p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <h6 class="font-weight-bold text-dark mb-1">
                                    Subscribe to {{ $siteName }}
                                </h6>
                                <p class="small text-muted mb-3">Get the latest investigative reports and breaking stories delivered straight to your inbox.</p>
                                <form action="" class="newsletter-form">
                                    <div class="input-group">
                                        <input type="email" class="form-control" name="email" placeholder="{{ __('frontend.Your email address') }}" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-primary newsletter-button" type="submit">{{ __('frontend.sign up') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </aside>

                        <!-- Stay Connected Widget -->
                        @if ($socialCounts && count($socialCounts) > 0)
                            <aside class="wrapper__list__article">
                                <h4 class="border_section">{{ __('frontend.stay conected') }}</h4>
                                <div class="wrap__social__media">
                                    @foreach ($socialCounts as $sc)
                                        <a href="{{ normalizeStoredUrl($sc->url) }}" target="_blank" rel="noopener noreferrer" class="d-block text-decoration-none">
                                            <div class="social__media__widget mt-2 d-flex align-items-center justify-content-between p-2 rounded text-white" style="background-color: {{ $sc->color }};">
                                                <span class="social__media__widget-icon ml-2">
                                                    <i class="{{ $sc->icon }}"></i>
                                                </span>
                                                <span class="social__media__widget-counter font-weight-bold">
                                                    {{ $sc->fan_count }} {{ $sc->fan_type }}
                                                </span>
                                                <span class="social__media__widget-name mr-2 small text-uppercase">
                                                    {{ $sc->button_text }}
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </aside>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

