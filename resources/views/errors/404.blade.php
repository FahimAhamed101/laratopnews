@extends('frontend.layouts.master')

@section('title', 'Page not found - ' . ($settings['site_name'] ?? 'Extremis News'))
@section('meta_description', 'The page you requested could not be found. Browse the latest news or return to the homepage.')
@section('robots', 'noindex, follow')

@section('content')
    <section class="wrapper__section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">

                    <p class="text-primary font-weight-bold mb-2" style="font-size: 64px; line-height: 1;">404</p>

                    <h1 class="h4 mb-3">We could not find that page</h1>

                    <p class="text-muted mb-4">
                        The article or page you were looking for may have been moved, renamed,
                        or is no longer available. Try the news index, or search for what you need.
                    </p>

                    <form action="{{ route('news') }}" method="GET" class="mb-4">
                        <div class="input-group mx-auto" style="max-width: 420px;">
                            <input class="form-control border-secondary border-right-0 rounded-0"
                                   type="search" name="search" placeholder="Search news"
                                   aria-label="Search news">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary border-left-0 rounded-0" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <p class="mb-0">
                        <a href="{{ url('/') }}" class="btn btn-primary mr-2 mb-2">{{ __('frontend.Home') }}</a>
                        <a href="{{ route('news') }}" class="btn btn-outline-secondary mr-2 mb-2">{{ __('frontend.News') }}</a>
                        <a href="{{ route('contact') }}" class="btn btn-outline-secondary mb-2">{{ __('frontend.contact') }}</a>
                    </p>

                </div>
            </div>
        </div>
    </section>
@endsection
