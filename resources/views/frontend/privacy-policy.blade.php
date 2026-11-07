@extends('frontend.layouts.master')

@section('title', 'Privacy Policy - ' . ($siteName ?? 'Extremis News'))
@section('meta_description', 'Privacy Policy for ' . ($siteName ?? 'Extremis News') . '. Learn how we collect, use, and protect your information, including cookies and Google AdSense policies.')

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
                        <span class="breadcrumbs__url">Privacy Policy</span>
                    </li>
                </ul>
            </div>

            <div class="col-lg-10 mx-auto">
                <div class="wrap__article-detail bg-white p-4 p-md-5 rounded shadow-sm border">
                    <h1 class="mb-4 font-weight-bold" style="color: #0f172a; font-size: 2.2rem;">Privacy Policy</h1>
                    <p class="text-muted mb-4"><em>Last updated: {{ date('F d, Y') }}</em></p>
                    <hr class="mb-4">

                    <div class="article__content" style="line-height: 1.8; color: #334155; font-size: 1.05rem;">
                        <p>At <strong>{{ $siteName }}</strong>, accessible from <a href="{{ url('/') }}">{{ url('/') }}</a>, one of our main priorities is the privacy of our visitors. This Privacy Policy document outlines the types of information that is collected and recorded by {{ $siteName }} and how we use it.</p>

                        <p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us through our <a href="{{ route('contact') }}">Contact Us page</a>.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">1. Log Files</h3>
                        <p>{{ $siteName }} follows a standard procedure of using log files. These files log visitors when they visit websites. The information collected by log files includes internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users' movement on the website, and gathering demographic information.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">2. Cookies and Web Beacons</h3>
                        <p>Like any other website, {{ $siteName }} uses 'cookies'. These cookies are used to store information including visitors' preferences, and the pages on the website that the visitor accessed or visited. The information is used to optimize the users' experience by customizing our web page content based on visitors' browser type and/or other information.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">3. Google DoubleClick DART Cookie & AdSense Policies</h3>
                        <p>Google is one of a third-party vendor on our site. It also uses cookies, known as DART cookies, to serve ads to our site visitors based upon their visit to our site and other sites on the internet. However, visitors may choose to decline the use of DART cookies by visiting the Google ad and content network Privacy Policy at the following URL: <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener noreferrer">https://policies.google.com/technologies/ads</a>.</p>

                        <div class="p-3 my-3 bg-light rounded border-left" style="border-left: 4px solid #1066ff;">
                            <p class="mb-0"><strong>Third-Party Ad Networks:</strong> Some of our advertising partners (including Google AdSense) may use cookies and web beacons on our site. Third-party ad servers or ad networks use technologies like cookies, JavaScript, or Web Beacons that are used in their respective advertisements and links that appear on {{ $siteName }}, which are sent directly to users' browser. They automatically receive your IP address when this occurs.</p>
                        </div>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">4. Third Party Privacy Policies</h3>
                        <p>{{ $siteName }}'s Privacy Policy does not apply to other advertisers or websites. Thus, we are advising you to consult the respective Privacy Policies of these third-party ad servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options.</p>
                        <p>You can choose to disable cookies through your individual browser options. To know more detailed information about cookie management with specific web browsers, it can be found at the browsers' respective websites.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">5. CCPA Privacy Rights (Do Not Sell My Personal Information)</h3>
                        <p>Under the CCPA, among other rights, California consumers have the right to:</p>
                        <ul>
                            <li>Request that a business that collects a consumer's personal data disclose the categories and specific pieces of personal data that a business has collected about consumers.</li>
                            <li>Request that a business delete any personal data about the consumer that a business has collected.</li>
                            <li>Request that a business that sells a consumer's personal data, not sell the consumer's personal data.</li>
                        </ul>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">6. GDPR Data Protection Rights</h3>
                        <p>We would like to make sure you are fully aware of all of your data protection rights. Every user is entitled to the following:</p>
                        <ul>
                            <li><strong>The right to access</strong> – You have the right to request copies of your personal data.</li>
                            <li><strong>The right to rectification</strong> – You have the right to request that we correct any information you believe is inaccurate.</li>
                            <li><strong>The right to erasure</strong> – You have the right to request that we erase your personal data, under certain conditions.</li>
                            <li><strong>The right to restrict processing</strong> – You have the right to request that we restrict the processing of your personal data, under certain conditions.</li>
                        </ul>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">7. Children's Information</h3>
                        <p>Another part of our priority is adding protection for children while using the internet. We encourage parents and guardians to observe, participate in, and/or monitor and guide their online activity. {{ $siteName }} does not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think that your child provided this kind of information on our website, we strongly encourage you to contact us immediately and we will do our best efforts to promptly remove such information from our records.</p>

                        <h3 class="mt-4 mb-3 font-weight-bold" style="color: #0f172a;">8. Consent</h3>
                        <p>By using our website, you hereby consent to our Privacy Policy and agree to its Terms and Conditions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
