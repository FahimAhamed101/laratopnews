@extends('frontend.layouts.master')
<!-- Setting metas -->
@section('title', ($news->meta_title ?: $news->title) . ' | ' . ($settings['site_name'] ?? 'Extremis News'))
@section('meta_description', $news->meta_description ?: Str::limit(strip_tags($news->content), 160))
@section('meta_og_title', $news->meta_title ?: $news->title)
@section('meta_og_description', $news->meta_description ?: Str::limit(strip_tags($news->content), 160))
@section('meta_og_image', asset($news->image))
@section('meta_tw_title', $news->meta_title ?: $news->title)
@section('meta_tw_description', $news->meta_description ?: Str::limit(strip_tags($news->content), 160))
@section('meta_tw_image', asset($news->image))
@section('canonical_url', route('news-details', $news->slug))
@section('og_type', 'article')
@section('article_published_time', ($news->created_at ?? now())->toIso8601String())
@section('article_modified_time', ($news->updated_at ?? $news->created_at ?? now())->toIso8601String())
@section('article_section', $news->category->name ?? 'News')
@section('article_author', $news->author?->name ?? 'Editorial Desk')
@section('meta_keywords', $news->tags->pluck('name')->implode(', ') ?: ($settings['site_seo_keywords'] ?? 'news, breaking news'))
<!-- End Setting metas -->

@php
    $words = str_word_count(strip_tags($news->content));
    $readTime = max(1, ceil($words / 220));
    $canonicalUrl = route('news-details', $news->slug);
    $pubDate = ($news->created_at ?? now())->toIso8601String();
    $modDate = ($news->updated_at ?? $news->created_at ?? now())->toIso8601String();

    // Extract FAQs from content for FAQPage Schema
    $faqItems = [];
    if (preg_match_all('/<div class="faq-item"[^>]*>[\s\S]*?<h4[^>]*>([\s\S]*?)<\/h4>[\s\S]*?<p[^>]*>([\s\S]*?)<\/p>[\s\S]*?<\/div>/i', $news->content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $q = trim(strip_tags($match[1]));
            $a = trim(strip_tags($match[2]));
            if (!empty($q) && !empty($a)) {
                $faqItems[] = [
                    '@type' => 'Question',
                    'name' => $q,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $a,
                    ],
                ];
            }
        }
    }
@endphp

@push('content')
<!-- JSON-LD NewsArticle Structured Data -->
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $canonicalUrl,
    ],
    'headline' => $news->title,
    'description' => $news->meta_description ?: Str::limit(strip_tags($news->content), 150),
    'image' => [ asset($news->image) ],
    'datePublished' => $pubDate,
    'dateModified' => $modDate,
    'author' => [
        '@type' => 'Person',
        'name' => $news->author?->name ?? 'Editorial Desk',
        'jobTitle' => $news->author?->designation ?? 'Staff Correspondent',
        'url' => $news->author ? route('author.profile', ['id' => $news->author->id, 'slug' => Str::slug($news->author->name)]) : url('/'),
        'image' => $news->author?->image ? asset($news->author->image) : asset($settings['site_logo'] ?? ''),
        'sameAs' => array_values(array_filter([
            $news->author?->website,
            $news->author?->twitter,
            $news->author?->linkedin,
            $news->author?->facebook,
            $news->author?->instagram,
        ])),
    ],
    'publisher' => [
        '@type' => 'NewsMediaOrganization',
        'name' => $settings['site_name'] ?? 'Extremis News',
        'url' => url('/'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset($settings['site_logo'] ?? ''),
        ],
        'publishingPrinciples' => route('about'),
    ],
    'articleSection' => $news->category->name ?? 'News',
    'inLanguage' => $news->language === 'bn' ? 'bn-BD' : 'en-US',
    'wordCount' => $words,
    'isAccessibleForFree' => true,
    'keywords' => $news->tags->pluck('name')->toArray(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<!-- JSON-LD BreadcrumbList Structured Data -->
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
            'name' => $news->category->name ?? 'News',
            'item' => route('news', ['category' => $news->category->slug ?? '']),
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => Str::limit($news->title, 40),
            'item' => $canonicalUrl,
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

@if (!empty($faqItems))
<!-- JSON-LD FAQPage Structured Data -->
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqItems,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
@endpush

@section('content')

    <!-- Sticky Reading Progress Bar -->
    <div id="readingProgressBar" style="position: fixed; top: 0; left: 0; height: 3px; background: linear-gradient(90deg, #2563eb, #3b82f6, #60a5fa); z-index: 999999; width: 0%; transition: width 0.1s ease;"></div>

    <section class="pb-80">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <!-- Breadcrumb -->
                    <ul class="breadcrumbs bg-light mb-4">
                        <li class="breadcrumbs__item">
                            <a href="{{ route('home') }}" class="breadcrumbs__url">
                                <i class="fa fa-home"></i> {{ __('frontend.Home') }}</a>
                        </li>
                        @if ($news->category)
                        <li class="breadcrumbs__item">
                            <a href="{{ route('news', ['category' => $news->category->slug]) }}" class="breadcrumbs__url">{{ $news->category->name }}</a>
                        </li>
                        @endif
                        <li class="breadcrumbs__item">
                            <span class="text-muted">{{ Str::limit($news->title, 45) }}</span>
                        </li>
                    </ul>
                    <!-- end breadcrumb -->
                </div>
                <div class="col-md-8">
                    <!-- content article detail -->
                    <!-- Article Detail -->
                    <div class="wrap__article-detail">
                        <div class="wrap__article-detail-title">
                            <h1 style="line-height: 1.35; font-weight: 700; color: #0f172a;">
                                {!! $news->title !!}
                            </h1>
                        </div>
                        <hr>
                        <div class="wrap__article-detail-info mb-3">
                            <ul class="list-inline d-flex flex-wrap justify-content-start align-items-center mb-0" style="gap: 15px;">
                                <li class="list-inline-item">
                                    <i class="fa fa-user text-muted mr-1"></i> {{ __('frontend.By') }}
                                    @if($news->author)
                                        <a href="{{ $news->author->profileUrl() }}" class="text-dark font-weight-bold" style="text-decoration: underline; text-decoration-color: #cbd5e1;">
                                            {{ $news->author->name }}
                                        </a>
                                        @if($news->author->designation)
                                            <span class="badge badge-light border text-muted ml-1" style="font-weight: 500; font-size: 11px;">{{ $news->author->designation }}</span>
                                        @endif
                                    @else
                                        <strong>Editorial Desk</strong>
                                    @endif
                                </li>
                                <li class="list-inline-item">
                                    <i class="fa fa-calendar-alt text-muted mr-1"></i>
                                    <span class="text-dark text-capitalize">
                                        {{ localizeDate($news->created_at, 'M d, Y') }}
                                    </span>
                                </li>
                                <li class="list-inline-item">
                                    <i class="fa fa-folder text-muted mr-1"></i>
                                    <a href="{{ route('news', ['category' => $news->category->slug]) }}" class="badge badge-primary text-white py-1 px-2 font-weight-bold">
                                        {{ $news->category->name }}
                                    </a>
                                </li>
                                <li class="list-inline-item text-muted">
                                    <i class="fa fa-clock mr-1"></i> {{ $readTime }} min read
                                </li>
                                <li class="list-inline-item text-muted">
                                    <i class="fa fa-eye mr-1"></i> {{ number_format($news->views) }} views
                                </li>
                            </ul>
                        </div>

                        <div class="wrap__article-detail-image mt-4">
                            <figure>
                                <img src="{{ asset($news->image) }}" alt="{{ $news->title }}" class="img-fluid rounded" style="width: 100%; object-fit: cover;">
                            </figure>
                        </div>
                        <div class="wrap__article-detail-content">
                            <div class="total-views">
                                <div class="total-views-read">
                                    {{ convertToKFormat($news->views) }}
                                    <span>
                                        {{ __('frontend.views') }}
                                    </span>
                                </div>

                                <ul class="list-inline">
                                    <span class="share">{{ __('frontend.share on:') }}</span>
                                    <li class="list-inline-item">
                                        <a class="btn btn-social-o facebook"
                                            href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}"
                                            target="_blank" rel="noopener">
                                            <i class="fa fa-facebook-f"></i>
                                            <span>facebook</span>
                                        </a>

                                    </li>
                                    <li class="list-inline-item">
                                        <a class="btn btn-social-o twitter"
                                            href="https://twitter.com/intent/tweet?text={{ urlencode($news->title) }}&url={{ url()->current() }}"
                                            target="_blank" rel="noopener">
                                            <i class="fa fa-twitter"></i>
                                            <span>twitter</span>
                                        </a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a class="btn btn-social-o whatsapp"
                                            href="https://wa.me/?text={{ urlencode($news->title) }}%20{{ url()->current() }}"
                                            target="_blank" rel="noopener">
                                            <i class="fa fa-whatsapp"></i>
                                            <span>whatsapp</span>
                                        </a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a class="btn btn-social-o telegram"
                                            href="https://t.me/share/url?url={{ url()->current() }}&text={{ urlencode($news->title) }}"
                                            target="_blank" rel="noopener">
                                            <i class="fa fa-telegram"></i>
                                            <span>telegram</span>
                                        </a>
                                    </li>

                                    <li class="list-inline-item">
                                        <a class="btn btn-linkedin-o linkedin"
                                            href="https://www.linkedin.com/shareArticle?mini=true&url={{ url()->current() }}&title={{ urlencode($news->title) }}"
                                            target="_blank" rel="noopener">
                                            <i class="fa fa-linkedin"></i>
                                            <span>linkedin</span>
                                        </a>
                                    </li>

                                    <li class="list-inline-item">
                                        <button type="button" class="btn btn-social-o" id="copyShareLink" title="Copy article link" style="border: 1px solid #cbd5e1; background: #f8fafc; color: #334155; cursor: pointer;">
                                            <i class="fa fa-link mr-1"></i>
                                            <span id="copyShareLinkText">Copy Link</span>
                                        </button>
                                    </li>

                                </ul>
                            </div>
                            <div class="article-body-content">
                                {!! $news->content !!}
                            </div>
                        </div>


                    </div>
                    <!-- end content article detail -->

                    <!-- tags -->
                    <!-- News Tags -->
                    <div class="blog-tags">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                <i class="fa fa-tags">
                                </i>
                            </li>
                            @foreach ($news->tags as $tag)
                                <li class="list-inline-item">
                                    <a href="{{ route('news', ['tag' => $tag->name]) }}">
                                        #{{ $tag->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <!-- end tags-->

                    <!-- authors-->
                    <!-- Profile author -->
                    <div class="wrap__profile p-4 mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 4px 12px rgba(15,23,42,0.04);">
                        <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start" style="gap: 20px;">
                            <a href="{{ $news->author ? $news->author->profileUrl() : '#' }}" class="position-relative flex-shrink-0" style="display: inline-block;">
                                <img style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #2563eb; box-shadow: 0 4px 12px rgba(37,99,235,0.2);"
                                    src="{{ asset($news->author?->image ?? 'frontend/asset/images/avatar.jpg') }}" alt="{{ $news->author?->name ?? 'Author' }}"
                                    class="rounded-circle">
                                <span class="position-absolute" style="bottom: 2px; right: 2px; background: #2563eb; color: #fff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; border: 2px solid #fff;" title="Verified Staff Writer">
                                    <i class="fa fa-check"></i>
                                </span>
                            </a>
                            <div class="flex-grow-1 text-center text-sm-left" style="width: 100%;">
                                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-between mb-2" style="gap: 8px;">
                                    <div>
                                        <span class="text-uppercase font-weight-bold" style="font-size: 11px; letter-spacing: 0.08em; color: #2563eb;">{{ __('frontend.Written By') }}</span>
                                        <h4 class="mb-0 font-weight-bold" style="color: #0f172a; font-size: 20px;">
                                            @if($news->author)
                                                <a href="{{ $news->author->profileUrl() }}" style="color: inherit; text-decoration: none;">
                                                    {{ $news->author->name }}
                                                </a>
                                            @else
                                                Editorial Desk
                                            @endif
                                        </h4>
                                    </div>
                                    @if($news->author?->designation)
                                        <span class="badge badge-light border px-2 py-1 text-dark" style="font-weight: 600; font-size: 12px;">
                                            <i class="fa fa-feather mr-1 text-primary"></i> {{ $news->author->designation }}
                                        </span>
                                    @endif
                                </div>

                                <p class="text-muted mt-2 mb-3" style="font-size: 14px; line-height: 1.6;">
                                    {{ $news->author?->bio ?: 'Staff writer and investigative reporter covering key stories, breaking updates, and specialized analysis for Extremis News.' }}
                                </p>

                                <div class="d-flex flex-wrap align-items-center justify-content-between pt-2" style="border-top: 1px solid #e2e8f0; gap: 12px;">
                                    <ul class="list-inline mb-0">
                                        @if($news->author?->website)
                                            <li class="list-inline-item mr-2">
                                                <a href="{{ $news->author->website }}" target="_blank" rel="noopener nofollow" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 30px; text-align: center;" title="Website">
                                                    <i class="fa fa-globe"></i>
                                                </a>
                                            </li>
                                        @endif
                                        @if($news->author?->twitter)
                                            <li class="list-inline-item mr-2">
                                                <a href="{{ $news->author->twitter }}" target="_blank" rel="noopener nofollow" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 30px; text-align: center;" title="Twitter / X">
                                                    <i class="fa fa-twitter"></i>
                                                </a>
                                            </li>
                                        @endif
                                        @if($news->author?->linkedin)
                                            <li class="list-inline-item mr-2">
                                                <a href="{{ $news->author->linkedin }}" target="_blank" rel="noopener nofollow" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 30px; text-align: center;" title="LinkedIn">
                                                    <i class="fa fa-linkedin"></i>
                                                </a>
                                            </li>
                                        @endif
                                        @if($news->author?->facebook)
                                            <li class="list-inline-item mr-2">
                                                <a href="{{ $news->author->facebook }}" target="_blank" rel="noopener nofollow" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 30px; text-align: center;" title="Facebook">
                                                    <i class="fa fa-facebook-f"></i>
                                                </a>
                                            </li>
                                        @endif
                                        @if($news->author?->instagram)
                                            <li class="list-inline-item mr-2">
                                                <a href="{{ $news->author->instagram }}" target="_blank" rel="noopener nofollow" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding: 0; line-height: 30px; text-align: center;" title="Instagram">
                                                    <i class="fa fa-instagram"></i>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>

                                    @if($news->author)
                                        <a href="{{ $news->author->profileUrl() }}" class="btn btn-sm btn-outline-primary font-weight-bold px-3 py-1" style="border-radius: 20px;">
                                            <span>{{ __('frontend.All Stories by') }} {{ Str::words($news->author->name, 1, '') }}</span>
                                            <i class="fa fa-arrow-right ml-1"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end author-->

                    <!-- Comment  -->
                    @auth
                        <div id="comments" class="comments-area">
                            <h3 class="comments-title">{{ localizeNumber($news->comments()->count()) }} {{ __('frontend.Comments') }}:</h3>

                            <ol class="comment-list">
                                @foreach ($news->comments()->whereNull('parent_id')->get() as $comment)
                                    <li class="comment">
                                        <aside class="comment-body">
                                            <div class="comment-meta">
                                                <div class="comment-author vcard">
                                                    <img src="{{ asset('frontend/asset/images/avatar.jpg') }}" class="avatar"
                                                        alt="image">
                                                    <b class="fn">{{ $comment->user->name }}</b>
                                                    <span class="says">{{ __('frontend.says') }}:</span>
                                                </div>

                                                <div class="comment-metadata">
                                                    <a href="#">
                                                        <span>{{ localizeDate($comment->created_at, 'M, d, Y H:i') }}</span>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="comment-content">
                                                <p>{{ $comment->comment }}</p>
                                            </div>

                                            <div class="reply">
                                                <a href="#" class="comment-reply-link" data-toggle="modal"
                                                    data-target="#exampleModal-{{ $comment->id }}">{{ __('frontend.Reply') }}</a>
                                                <span class="delete-msg" data-id="{{ $comment->id }}">
                                                    <i class="fa fa-trash"></i>
                                                </span>
                                            </div>
                                        </aside>

                                        @if ($comment->reply()->count() > 0)
                                            @foreach ($comment->reply as $reply)
                                                <ol class="children">
                                                    <li class="comment">
                                                        <aside class="comment-body">
                                                            <div class="comment-meta">
                                                                <div class="comment-author vcard">
                                                                    <img src="{{ asset('frontend/asset/images/avatar.jpg') }}"
                                                                        class="avatar" alt="image">
                                                                    <b class="fn">{{ $reply->user->name }}</b>
                                                                    <span class="says">{{ __('frontend.says') }}:</span>
                                                                </div>

                                                                <div class="comment-metadata">
                                                                    <a href="#">
                                                                        <span>{{ localizeDate($reply->created_at, 'M, d, Y H:i') }}</span>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div class="comment-content">
                                                                <p>{{ $reply->comment }}</p>
                                                            </div>

                                                            <div class="reply">
                                                                @if ($loop->last)
                                                                    <a href="#" class="comment-reply-link"
                                                                        data-toggle="modal"
                                                                        data-target="#exampleModal-{{ $comment->id }}">{{ __('frontend.Reply') }}</a>
                                                                @endif

                                                                <span class="delete-msg" style="margin-left: auto;"
                                                                    data-id="{{ $reply->id }}">
                                                                    <i class="fa fa-trash"></i>
                                                                </span>
                                                            </div>
                                                        </aside>
                                                    </li>
                                                </ol>
                                            @endforeach
                                        @endif
                                    </li>
                                    <!-- Modal -->
                                    <div class="comment_modal">
                                        <div class="modal fade" id="exampleModal-{{ $comment->id }}" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">
                                                            {{ __('frontend.Write Your Comment') }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('news-comment-reply') }}" method="POST">
                                                            @csrf
                                                            <textarea name="reply" cols="30" rows="7" placeholder="{{ __('frontend.Type here') }}..."></textarea>
                                                            <input type="hidden" name="news_id"
                                                                value="{{ $news->id }}">
                                                            <input type="hidden" name="parent_id"
                                                                value="{{ $comment->id }}">
                                                            <button type="submit">{{ __('frontend.submit') }}</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </ol>

                            <div class="comment-respond">
                                <h3 class="comment-reply-title">{{ __('frontend.Leave a Reply') }}</h3>

                                <form action={{ route('news-comment') }} method='POST' class="comment-form">
                                    @csrf
                                    <p class="comment-notes">
                                        <span
                                            id="email-notes">{{ __('frontend.Your email address will not be published') }}.</span>
                                        {{ __('frontend.Required fields are marked') }}
                                        <span class="required">*</span>
                                    </p>
                                    <p class="comment-form-comment">
                                        <label for="comment">{{ __('frontend.Comment') }}</label>
                                        <textarea name="comment" id="comment" cols="45" rows="5" maxlength="65525" required="required"></textarea>
                                        <input type="hidden" name="news_id" value="{{ $news->id }}">
                                        <input type="hidden" name="parent_id" value="">
                                        @error('comment')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                    </p>
                                    <p class="form-submit mb-0">
                                        <input type="submit" name="submit" id="submit" class="submit"
                                            value="{{ __('frontend.Post Comment') }}">
                                    </p>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="card my-5">
                            <div class="card-body">
                                <h5 class="p-0">{{ __('frontend.Please') }} <a
                                        href="{{ route('login') }}">{{ __('frontend.Login') }}</a>
                                    {{ __('frontend.to comment in the post') }}!</h5>
                            </div>
                        </div>
                    @endauth


                    <!-- end comment -->



                    <div class="row">
                        <div class="col-md-6">
                            <div class="single_navigation-prev">
                                @if ($previousPost)
                                    <a href="{{ route('news-details', $previousPost->slug) }}">
                                        <span>{{ __('frontend.previous post') }}</span>
                                        {!! truncate($previousPost->title) !!}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="single_navigation-next text-left text-md-right">
                                @if ($nextPost)
                                    <a href="{{ route('news-details', $nextPost->slug) }}">
                                        <span>{{ __('frontend.next post') }}</span>
                                        {!! truncate($nextPost->title) !!}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="small_add_banner mb-5 pb-4">
                        <div class="small_add_banner_img">
                            <img src="images/placeholder_large.jpg" alt="adds">
                        </div>
                    </div>


                    <div class="clearfix"></div>

                    <div class="related-article">

                        @if (count($relatedPosts) > 0)
                            <h4>
                                {{ __('frontend.you may also like') }}
                            </h4>

                            <div class="article__entry-carousel-three">
                                @foreach ($relatedPosts as $post)
                                    <div class="item">
                                        <!-- Post Article -->
                                        <div class="article__entry">
                                            <div class="article__image">
                                                <a href="{{ route('news-details', $post->slug) }}">
                                                    <img src="{{ asset($post->image) }}" alt=""
                                                        class="img-fluid">
                                                </a>
                                            </div>
                                            <div class="article__content">
                                                <ul class="list-inline">
                                                    <li class="list-inline-item">
                                                        <span class="text-primary">
                                                            {{ __('frontend.by') }} {{ $post->author?->name ?? 'Editorial Desk' }}
                                                        </span>
                                                    </li>
                                                    <li class="list-inline-item">
                                                        <span>
                                                            <span>{{ localizeDate($post->created_at, 'M, d, Y H:i') }}</span>
                                                        </span>
                                                    </li>

                                                </ul>
                                                <h5>
                                                    <a href="{{ route('news-details', $post->slug) }}">
                                                        {!! truncate($post->title) !!}
                                                    </a>
                                                </h5>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>

                </div>
                <div class="col-md-4">
                    <div class="sticky-top">
                        <aside class="wrapper__list__article ">
                            <!-- <h4 class="border_section">Sidebar</h4> -->
                            <div class="mb-4">
                                <div class="widget__form-search-bar  ">
                                    <div class="row no-gutters">
                                        <div class="col">
                                            <input class="form-control border-secondary border-right-0 rounded-0"
                                                value="" placeholder="{{ __('frontend.search') }}">
                                        </div>
                                        <div class="col-auto">
                                            <button
                                                class="btn btn-outline-secondary border-left-0 rounded-0 rounded-right">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                                                    {!! $news->title !!}
                                                                </a>
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($loop->index === 3)
                                        <!-- Post Article -->
                                        <div class="article__entry">
                                            <div class="article__image">
                                                <a href="#">
                                                    <img src="{{ asset($news->image) }}" alt=""
                                                        class="img-fluid">
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
                                                    <a href="#">
                                                        {!! truncate($news->title) !!}
                                                    </a>
                                                </h5>
                                                <p>
                                                    {!! truncate($news->content, 160) !!}
                                                </p>
                                                <a href="{{ route('news-details', $news->slug) }}"
                                                    class="btn btn-outline-primary mb-4 text-capitalize">{{ __('frontend.read more') }}</a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </aside>

                        <!-- social media -->
                        <aside class="wrapper__list__article">
                            <h4 class="border_section">{{ __('frontend.stay conected') }}</h4>
                            <!-- widget Social media -->
                            <div class="wrap__social__media">
                                @foreach ($socialCounts as $socialCount)
                                    <a href="{{ normalizeStoredUrl($socialCount->url) }}" target="_blank">
                                        <div class="social__media__widget mt-2"
                                            style="background-color:{{ $socialCount->color }}">
                                            <span class="social__media__widget-icon">
                                                <i class="{{ $socialCount->icon }}"></i>
                                            </span>
                                            <span class="social__media__widget-counter">
                                                {{ $socialCount->fan_count }} {{ $socialCount->fan_type }}
                                            </span>
                                            <span class="social__media__widget-name">
                                                {{ $socialCount->button_text }}
                                            </span>
                                        </div>
                                    </a>
                                @endforeach

                            </div>
                        </aside>
                        <!-- End social media -->

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
            </div>
        </div>
    </section>
@endsection

@push('content')
<script>
    $(document).ready(function(){
        // Reading Progress Bar
        $(window).on('scroll', function() {
            var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var scrolled = height > 0 ? (winScroll / height) * 100 : 0;
            $('#readingProgressBar').css('width', scrolled + '%');
        });

        // 1-Click Copy Share Link
        $('#copyShareLink').on('click', function(e) {
            e.preventDefault();
            var shareUrl = window.location.href;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(shareUrl).then(function() {
                    feedbackCopySuccess();
                }).catch(function() {
                    fallbackCopy(shareUrl);
                });
            } else {
                fallbackCopy(shareUrl);
            }
        });

        function feedbackCopySuccess() {
            var $btn = $('#copyShareLink');
            var $text = $('#copyShareLinkText');
            $text.text('Copied!');
            $btn.css({'background': '#10b981', 'color': '#ffffff', 'border-color': '#10b981'});
            setTimeout(function() {
                $text.text('Copy Link');
                $btn.css({'background': '#f8fafc', 'color': '#334155', 'border-color': '#cbd5e1'});
            }, 2500);
        }

        function fallbackCopy(text) {
            var dummy = document.createElement('input');
            document.body.appendChild(dummy);
            dummy.value = text;
            dummy.select();
            document.execCommand('copy');
            document.body.removeChild(dummy);
            feedbackCopySuccess();
        }

        $('.delete-msg').on('click', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                Swal.fire({
                    title: '{{ __("frontend.Are you sure?") }}',
                    text: "{{ __("frontend.You won'\t be able to revert this!") }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __("frontend.Yes, delete it!") }}'
                }).then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            method: 'DELETE',
                            url: "{{ route('news-comment-destroy') }}",
                            data:{id:id},
                            success: function(data) {
                                if (data.status === 'success') {
                                    Swal.fire(
                                        'Deleted!',
                                        data.message,
                                        'success'
                                    )
                                    window.location.reload();
                                } else if (data.status === 'error') {
                                    Swal.fire(
                                        'Error!',
                                        data.message,
                                        'error'
                                    )
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });


                    }
                })
            })

    })
</script>
@endpush
