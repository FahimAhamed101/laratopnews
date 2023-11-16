@extends('frontend.layouts.master')

@section('content')
    <!-- Tranding news  carousel-->
    @include('frontend.home-components.breaking-news')
    <!-- End Tranding news carousel -->

    <!-- Hero news -->
    @include('frontend.home-components.hero-silder')
    <!-- End Hero news -->

    <!-- End Hero news Section-->
    @if ($ad->home_top_bar_ad_status == 1)
    <a href="{{ $ad->home_top_bar_ad_url }}">
        <div class="large_add_banner">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="large_add_banner_img">
                            <img src="{{ $ad->home_top_bar_ad }}" alt="adds">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
    @endif


    <!-- Popular news category -->
    @include('frontend.home-components.main-news')
    <!-- End Popular news category -->
@endsection