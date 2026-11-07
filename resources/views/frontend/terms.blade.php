@extends('frontend.layouts.master')

@section('title', 'Terms of Service - ' . ($siteName ?? 'Extremis News'))
@section('meta_description', 'Terms of Service and user agreement for ' . ($siteName ?? 'Extremis News') . '. Please review our website usage terms, copyright guidelines, and user standards.')

@section('content')
<section class="pb-80 pt-40">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="breadcrumbs bg-light mb-4">
                    <li class="breadcrumbs__item">
                        <a href="{{ route('home') }}" class="breadcrumbs__url"><i class="fa fa-home"></i> {{ __('frontend.Home') }}</a>
                    </li>
                    <li class="breadcrumbs__item">
                        <span class="breadcrumbs__url">Terms of Service</span>
                    </li>
                </ul>
            </div>

            <div class="col-lg-10 mx-auto">
                <div class="wrap__article-detail bg-white p-4 p-md-5 rounded shadow-sm border">
                    <h1 class="mb-4 font-weight-bold" style="color: #0f172a; font-size: 2.2rem;">Terms of Service</h1>
                    <p class="text-muted mb-4"><em>Last updated: {{ date('F d, Y') }}</em></p>
                    <hr class="mb-4">

                    <div class="article__content" style="line-height: 1.8; color: #334155; font-size: 1.05rem;">
                        <p>Welcome to <strong>{{ $siteName }}</strong>. These terms and conditions outline the rules and regulations for the use of {{ $siteName }}'s Website, located at <a href="{{ url('/') }}">{{ url('/') }}</a>.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">1. Acceptance of Terms</h3>
                        <p>By accessing this website we assume you accept these terms and conditions in full. Do not continue to use {{ $siteName }} if you do not accept all of the terms and conditions stated on this page.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">2. Intellectual Property Rights</h3>
                        <p>Unless otherwise stated, {{ $siteName }} and/or its licensors own the intellectual property rights for all material on {{ $siteName }}. All intellectual property rights are reserved. You may view and/or print pages from {{ url('/') }} for your own personal use subject to restrictions set in these terms and conditions.</p>
                        <p>You must not:</p>
                        <ul>
                            <li>Republish material from {{ $siteName }} without clear attribution and canonical backlink.</li>
                            <li>Sell, rent, or sub-license material from {{ $siteName }}.</li>
                            <li>Reproduce, duplicate or copy material from {{ $siteName }} for commercial purposes without prior written authorization.</li>
                        </ul>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">3. User Comments & Content</h3>
                        <p>Certain parts of this website offer the opportunity for users to post and exchange opinions and information in certain areas of the website. {{ $siteName }} does not screen, edit, publish or review comments prior to their presence on the website. Comments reflect the views and opinions of the person who posts their views and opinions.</p>
                        <p>{{ $siteName }} reserves the right to monitor all comments and to remove any comments which can be considered inappropriate, offensive or causes breach of these Terms and Conditions.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">4. Disclaimer of Warranties</h3>
                        <p>The information on this website is provided "as is", and to the maximum extent permitted by law, {{ $siteName }} makes no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, or suitability with respect to the website or the information, products, or services contained on the website.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">5. Contact Us</h3>
                        <p>If you have any questions about these Terms of Service, please visit our <a href="{{ route('contact') }}">Contact Us page</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
