@extends('frontend.layouts.master')

@php
    // Internal search results and tag-filtered archives are thin, largely
    // duplicate listings. Keep them out of the search index (this is Google's
    // own guidance for site search pages) while still letting crawlers follow
    // the article links out of them.
    $isFilteredListing = request()->filled('search') || request()->filled('tag');

    $siteName = $settings['site_name'] ?? 'Extremis News';
    $pageTitle = __('frontend.News') . ' | ' . $siteName;
    $pageDesc = $settings['site_seo_description'] ?? 'Stay up to date with the latest breaking headlines and verified news.';

    if (request()->filled('category') && isset($categories)) {
        $selectedCat = $categories->firstWhere('slug', request()->category);
        if ($selectedCat) {
            $pageTitle = $selectedCat->name . ' - ' . __('frontend.News') . ' | ' . $siteName;
            $pageDesc = 'Explore the latest ' . $selectedCat->name . ' news, headlines, and analysis on ' . $siteName . '.';
        }
    } elseif (request()->filled('tag')) {
        $pageTitle = '#' . request()->tag . ' ' . __('frontend.News') . ' | ' . $siteName;
        $pageDesc = 'Explore recent articles and stories tagged #' . request()->tag . ' on ' . $siteName . '.';
    } elseif (request()->filled('search')) {
        $pageTitle = 'Search: ' . request()->search . ' | ' . $siteName;
        $pageDesc = 'Search results for "' . request()->search . '" on ' . $siteName . '.';
    }
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDesc)
@section('canonical_url', url()->current())

@if ($isFilteredListing)
    @section('robots', 'noindex, follow')
@endif

@section('content')
    <section class="blog_pages">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <!-- Breadcrumb -->
                    <ul class="breadcrumbs bg-light mb-4">
                        <li class="breadcrumbs__item">
                            <a href="{{ url('/') }}" class="breadcrumbs__url">
                                <i class="fa fa-home"></i> {{ __('frontend.Home') }}</a>
                        </li>
                        <li class="breadcrumbs__item">
                            <span class="breadcrumbs__url">{{ __('frontend.News') }}</span>
                        </li>

                    </ul>
                </div>

            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-8">

                    <div class="blog_page_search">
                        <form action="{{ route('news') }}" method="GET">
                            <div class="row">
                                <div class="col-lg-5">
                                    <input type="text" placeholder="{{ __('frontend.Type here') }}" value="{{ request()->search }}"
                                        name="search">
                                </div>
                                <div class="col-lg-4">
                                    <select name="category">
                                        <option value="">{{ __('frontend.All') }}</option>
                                        @foreach ($categories as $category)
                                            <option {{ $category->slug === request()->category ? 'selected' : '' }}
                                                value="{{ $category->slug }}">{{ $category->name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <button type="submit">{{ __('frontend.search') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <aside class="wrapper__list__article ">
                        @if (request()->has('category'))
                            <h4 class="border_section">{{ __('frontend.Category') }}: {{ request()->category }}</h4>
                        @endif

                        <div class="row">
                            @foreach ($news as $post)
                                <div class="col-lg-6">
                                    <!-- Post Article -->
                                    <div class="article__entry">
                                        <div class="article__image">
                                            <a href="{{ route('news-details', $post->slug) }}">
                                                <img src="{{ asset($post->image) }}" alt="" class="img-fluid">
                                            </a>
                                        </div>
                                        <div class="article__content">
                                            <div class="article__category">
                                                {{ $post->category->name }}
                                            </div>
                                            <ul class="list-inline">
                                                <li class="list-inline-item">
                                                    <span class="text-primary">
                                                        {{ __('frontend.by') }} {{ $post->author?->name ?? 'Editorial Desk' }}
                                                    </span>
                                                </li>
                                                <li class="list-inline-item">
                                                    <span class="text-dark text-capitalize">
                                                        {{ localizeDate($post->created_at, 'M d, Y') }}
                                                    </span>
                                                </li>

                                            </ul>
                                            <h5>
                                                <a href="{{ route('news-details', $post->slug) }}">
                                                    {!! truncate($post->title) !!}
                                                </a>
                                            </h5>
                                            <p>
                                                {!! truncate($post->content, 100) !!}
                                            </p>
                                            <a href="{{ route('news-details', $post->slug) }}"
                                                class="btn btn-outline-primary mb-4 text-capitalize">
                                                {{ __('frontend.read more') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if (count($news) === 0)
                                <div class="text-center w-100">
                                    <h4>{{ __('frontend.No News Found') }} :(</h4>
                                </div>
                            @endif
                        </div>

                    </aside>

                    <div class="text-center" style="display: flex;
                justify-content: center;">
                        <!-- Pagination -->
                        {{ $news->appends(request()->query())->links() }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sidebar-sticky">
                        <aside class="wrapper__list__article ">
                            <h4 class="border_section">{{ __('frontend.Sidebar') }}</h4>
                            <div class="wrapper__list__article-small">
                                @foreach ($recentNews as $news)
                                    @if ($loop->index <= 2)
                                        <div class="mb-3">
                                            <!-- Post Article -->
                                            <div class="card__post card__post-list">
                                                <div class="image-sm">
                                                    <a href="{{ route('news-details', $news->slug) }}">
                                                        <img src="{{ asset($news->image) }}" class="img-fluid"
                                                            alt="">
                                                    </a>
                                                </div>


                                                <div class="card__post__body ">
                                                    <div class="card__post__content">

                                                        <div class="card__post__author-info mb-2">
                                                            <ul class="list-inline">
                                                                <li class="list-inline-item">
                                                                    <span class="text-primary">
                                                                        {{ __('frontend.by') }} {{ $news->author?->name ?? 'Editorial Desk' }}
                                                                    </span>
                                                                </li>
                                                                <li class="list-inline-item">
                                                                    <span class="text-dark text-capitalize">
                                                                        {{ localizeDate($news->created_at, 'M d, Y') }}
                                                                    </span>
                                                                </li>

                                                            </ul>
                                                        </div>
                                                        <div class="card__post__title">
                                                            <h6>
                                                                <a href="{{ route('news-details', $news->slug) }}">
                                                                    {!! truncate($news->title) !!}
                                                                </a>
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @foreach ($recentNews as $news)
                                    @if ($loop->index > 2)
                                        <!-- Post Article -->
                                        <div class="article__entry">
                                            <div class="article__image">
                                                <a href="{{ route('news-details', $news->slug) }}">
                                                    <img src="{{ asset($news->image) }}" alt="" class="img-fluid">
                                                </a>
                                            </div>
                                            <div class="article__content">
                                                <div class="article__category">
                                                    {{ $news->category->name }}
                                                </div>
                                                <ul class="list-inline">
                                                    <li class="list-inline-item">
                                                        <span class="text-primary">
                                                            {{ __('frontend.by') }} {{ $news->author?->name ?? 'Editorial Desk' }}
                                                        </span>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <span class="text-dark text-capitalize">
                                                            {{ localizeDate($news->created_at, 'M d, Y') }}
                                                        </span>
                                                    </li>

                                                </ul>
                                                <h5>
                                                    <a href="{{ route('news-details', $news->slug) }}">
                                                        {!! truncate($news->title) !!}
                                                    </a>
                                                </h5>
                                                <p>
                                                    {!! truncate($news->content, 100) !!}
                                                </p>
                                                <a href="{{ route('news-details', $news->slug) }}"
                                                    class="btn btn-outline-primary mb-4 text-capitalize">
                                                    {{ __('frontend.read more') }}</a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </aside>

                        <aside class="wrapper__list__article">
                            <h4 class="border_section">{{ __('frontend.tags') }}</h4>
                            <div class="blog-tags p-0">
                                <ul class="list-inline">
                                    @foreach ($mostCommonTags as $tag)
                                        <li class="list-inline-item">
                                            <a href="{{ route('news', ['tag' => $tag->name]) }}">
                                                #{{ $tag->name }} ({{ localizeNumber($tag->count) }})
                                            </a>
                                        </li>
                                    @endforeach


                                </ul>
                            </div>
                        </aside>

                        <aside class="wrapper__list__article">
                            <h4 class="border_section">{{ __('frontend.newsletter') }}</h4>
                            <!-- Form Subscribe -->
                            <div class="widget__form-subscribe bg__card-shadow">
                                <h6>
                                    {{ __('frontend.The most important world news and events of the day') }}.
                                </h6>
                                <p><small>{{ __('frontend.Get magzrenvi daily newsletter on your inbox') }}.</small></p>
                                <form action="" class="newsletter-form">
                                    <div class="input-group ">
                                        <input type="text" class="form-control" name="email"
                                            placeholder="{{ __('frontend.Your email address') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary newsletter-button"
                                                type="submit">{{ __('frontend.sign up') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </aside>

                        @if ($ad?->side_bar_ad_status == 1)
                            <aside class="wrapper__list__article">
                                <h4 class="border_section">{{ __('frontend.Advertise') }}</h4>
                                <a href="{{ normalizeStoredUrl($ad->side_bar_ad_url) }}">
                                    <figure>
                                        <img src="{{ asset($ad->side_bar_ad) }}" alt="" class="img-fluid">
                                    </figure>
                                </a>
                            </aside>
                        @endif
                    </div>
                </div>

                <div class="clearfix"></div>
            </div>
        </div>
        @if ($ad?->news_page_ad_status == 1)
            <div class="large_add_banner my-4">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="large_add_banner_img">
                                <a href="{{ normalizeStoredUrl($ad->news_page_ad_url) }}">
                                    <img src="{{ asset($ad->news_page_ad) }}" alt="adds">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection
