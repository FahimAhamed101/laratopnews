<x-app-layout>
    <x-slot name="header">
        <div class="reader-hero">
            <div class="reader-hero-copy">
                <p class="reader-kicker">{{ __('Reader Desk') }}</p>
                <h1>{{ __('Good to see you') }}, {{ $user->name }}</h1>
                <p>{{ __('Track your submissions, follow the latest stories, and help the newsroom spot important local updates.') }}</p>
            </div>

            <div class="reader-actions">
                <a href="{{ route('dashboard.news.create') }}" class="reader-button reader-button-dark">{{ __('Submit News') }}</a>
                <a href="{{ route('news') }}" class="reader-button">{{ __('Browse News') }}</a>
            </div>
        </div>
    </x-slot>

    <style>
        .reader-page { max-width: 80rem; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
        .reader-hero { display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; }
        .reader-hero-copy { max-width: 46rem; }
        .reader-hero h1 { margin: .15rem 0 .4rem; font-family: Georgia, "Times New Roman", serif; font-size: clamp(1.75rem, 3vw, 2.65rem); line-height: 1.08; color: #0f172a; }
        .reader-hero p { margin: 0; color: #4b5563; font-size: .98rem; line-height: 1.55; }
        .reader-kicker { margin: 0; color: #c8102e; font-size: .76rem; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; }
        .reader-actions { display: flex; gap: .65rem; flex-wrap: wrap; }
        .reader-button { display: inline-flex; align-items: center; justify-content: center; min-height: 2.55rem; padding: 0 1rem; border-radius: 8px; background: #c8102e; color: #fff; font-size: .9rem; font-weight: 800; text-decoration: none; border: 0; box-shadow: 0 8px 18px rgba(200, 16, 46, .16); }
        .reader-button-dark { background: #111827; box-shadow: 0 8px 18px rgba(15, 23, 42, .14); }
        .reader-alert { margin-bottom: 1rem; padding: .9rem 1rem; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px; font-weight: 800; }
        .reader-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 1rem; }
        .reader-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 2px rgba(15, 23, 42, .06); }
        .reader-pad { padding: 1rem; }
        .reader-stat { grid-column: span 3; min-height: 96px; display: flex; flex-direction: column; justify-content: space-between; }
        .reader-stat-top { display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
        .reader-label { margin: 0; color: #64748b; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .reader-value { margin: .45rem 0 0; color: #0f172a; font-size: 1.85rem; line-height: 1; font-weight: 900; }
        .reader-stat-mark { width: 34px; height: 34px; border-radius: 8px; background: #f8fafc; border: 1px solid #e5e7eb; display: grid; place-items: center; color: #c8102e; font-weight: 900; }
        .reader-main { grid-column: span 8; }
        .reader-side { grid-column: span 4; }
        .reader-full { grid-column: 1 / -1; }
        .reader-section-head { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; border-bottom: 2px solid #111827; padding-bottom: .65rem; }
        .reader-section-title { margin: 0; font-family: Georgia, "Times New Roman", serif; font-size: 1.2rem; line-height: 1.1; color: #0f172a; }
        .reader-section-link { color: #c8102e; font-size: .82rem; font-weight: 800; text-decoration: none; }
        .reader-lead { display: grid; grid-template-columns: minmax(220px, .9fr) 1fr; gap: 1rem; padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #e5e7eb; }
        .reader-lead img { width: 100%; aspect-ratio: 16 / 10; object-fit: cover; border-radius: 8px; background: #e5e7eb; }
        .reader-lead h2 { margin: 0; font-family: Georgia, "Times New Roman", serif; font-size: clamp(1.25rem, 2vw, 1.65rem); line-height: 1.15; color: #0f172a; }
        .reader-meta { margin: .45rem 0 0; color: #64748b; font-size: .84rem; }
        .reader-summary { margin: .75rem 0 0; color: #475569; font-size: .94rem; line-height: 1.55; }
        .reader-list { display: grid; gap: .8rem; }
        .reader-story { display: grid; grid-template-columns: 104px 1fr; gap: .85rem; align-items: center; color: inherit; text-decoration: none; }
        .reader-story img { width: 104px; height: 72px; object-fit: cover; border-radius: 8px; background: #e5e7eb; }
        .reader-story h3 { margin: 0; color: #0f172a; font-size: .96rem; line-height: 1.32; }
        .reader-story:hover h3, .reader-lead:hover h2 { color: #c8102e; }
        .reader-categories { display: grid; gap: .55rem; }
        .reader-category { display: flex; justify-content: space-between; align-items: center; gap: .75rem; padding: .72rem .78rem; border: 1px solid #e5e7eb; border-radius: 8px; color: #0f172a; text-decoration: none; font-size: .9rem; font-weight: 750; }
        .reader-category span { min-width: 1.75rem; min-height: 1.75rem; display: grid; place-items: center; border-radius: 999px; background: #f8fafc; color: #64748b; font-size: .78rem; }
        .reader-submission, .reader-comment { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; border-top: 1px solid #e5e7eb; padding-top: .85rem; }
        .reader-submission:first-child, .reader-comment:first-child { border-top: 0; padding-top: 0; }
        .reader-submission strong, .reader-comment strong { color: #0f172a; }
        .reader-submission p, .reader-comment p { margin: .3rem 0 0; color: #64748b; font-size: .86rem; line-height: 1.45; }
        .reader-status { display: inline-flex; align-items: center; min-height: 1.55rem; padding: 0 .6rem; border-radius: 999px; font-size: .73rem; font-weight: 900; white-space: nowrap; }
        .reader-status-pending { background: #fef3c7; color: #92400e; }
        .reader-status-approved { background: #dcfce7; color: #166534; }
        .reader-empty { margin: 0; color: #64748b; font-size: .92rem; line-height: 1.5; }
        .reader-note { background: #0f172a; color: #fff; border-radius: 8px; padding: 1rem; }
        .reader-note h3 { margin: 0 0 .45rem; font-family: Georgia, "Times New Roman", serif; font-size: 1.15rem; }
        .reader-note p { margin: 0 0 .85rem; color: #d1d5db; font-size: .9rem; line-height: 1.5; }
        @media (max-width: 980px) { .reader-stat, .reader-main, .reader-side { grid-column: 1 / -1; } .reader-lead { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .reader-hero { align-items: flex-start; flex-direction: column; } .reader-actions, .reader-button { width: 100%; } .reader-story { grid-template-columns: 88px 1fr; } .reader-story img { width: 88px; height: 64px; } }
    </style>

    <div class="reader-page">
        @if (session('status') === 'news-submitted')
            <div class="reader-alert">{{ __('Your news was submitted and is waiting for admin approval.') }}</div>
        @endif

        <div class="reader-grid">
            <div class="reader-card reader-pad reader-stat">
                <div class="reader-stat-top">
                    <p class="reader-label">{{ __('Published') }}</p>
                    <span class="reader-stat-mark">N</span>
                </div>
                <p class="reader-value">{{ $stats['published_news'] }}</p>
            </div>
            <div class="reader-card reader-pad reader-stat">
                <div class="reader-stat-top">
                    <p class="reader-label">{{ __('Sections') }}</p>
                    <span class="reader-stat-mark">S</span>
                </div>
                <p class="reader-value">{{ $stats['categories'] }}</p>
            </div>
            <div class="reader-card reader-pad reader-stat">
                <div class="reader-stat-top">
                    <p class="reader-label">{{ __('Submissions') }}</p>
                    <span class="reader-stat-mark">T</span>
                </div>
                <p class="reader-value">{{ $stats['submissions'] }}</p>
            </div>
            <div class="reader-card reader-pad reader-stat">
                <div class="reader-stat-top">
                    <p class="reader-label">{{ __('Total Views') }}</p>
                    <span class="reader-stat-mark">V</span>
                </div>
                <p class="reader-value">{{ convertToKFormat($stats['views']) }}</p>
            </div>

            <section class="reader-card reader-pad reader-main">
                <div class="reader-section-head">
                    <h2 class="reader-section-title">{{ __('Today in the Newsroom') }}</h2>
                    <a href="{{ route('news') }}" class="reader-section-link">{{ __('All news') }}</a>
                </div>

                @if ($latestNews->isNotEmpty())
                    @php $lead = $latestNews->first(); @endphp
                    <a href="{{ route('news-details', $lead->slug) }}" class="reader-lead">
                        <img src="{{ asset($lead->image) }}" alt="{{ $lead->title }}">
                        <div>
                            <h2>{{ $lead->title }}</h2>
                            <p class="reader-meta">{{ $lead->category?->name }} &middot; {{ $lead->created_at->format('M d, Y') }}</p>
                            <p class="reader-summary">{{ truncate($lead->content, 155) }}</p>
                        </div>
                    </a>

                    <div class="reader-list">
                        @foreach ($latestNews->skip(1) as $item)
                            <a href="{{ route('news-details', $item->slug) }}" class="reader-story">
                                <img src="{{ asset($item->image) }}" alt="{{ $item->title }}">
                                <div>
                                    <h3>{{ $item->title }}</h3>
                                    <p class="reader-meta">{{ $item->category?->name }} &middot; {{ $item->created_at->format('M d, Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="reader-empty">{{ __('No news has been published for this language yet.') }}</p>
                @endif
            </section>

            <aside class="reader-side">
                <div class="reader-note">
                    <h3>{{ __('Have a local tip?') }}</h3>
                    <p>{{ __('Send a clear report with a photo. Editors review submissions before publishing.') }}</p>
                    <a href="{{ route('dashboard.news.create') }}" class="reader-button">{{ __('Submit News') }}</a>
                </div>
            </aside>

            <aside class="reader-card reader-pad reader-side">
                <div class="reader-section-head">
                    <h2 class="reader-section-title">{{ __('Most Viewed') }}</h2>
                </div>
                <div class="reader-list">
                    @forelse ($popularNews as $item)
                        <a href="{{ route('news-details', $item->slug) }}" class="reader-story">
                            <img src="{{ asset($item->image) }}" alt="{{ $item->title }}">
                            <div>
                                <h3>{{ $item->title }}</h3>
                                <p class="reader-meta">{{ convertToKFormat($item->views) }} {{ __('views') }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="reader-empty">{{ __('Popular news will appear here after readers visit articles.') }}</p>
                    @endforelse
                </div>
            </aside>

            <section class="reader-card reader-pad reader-side">
                <div class="reader-section-head">
                    <h2 class="reader-section-title">{{ __('Sections') }}</h2>
                </div>
                <div class="reader-categories">
                    @foreach ($categories as $category)
                        <a href="{{ route('news', ['category' => $category->slug]) }}" class="reader-category">
                            {{ $category->name }}
                            <span>{{ $category->news_count }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="reader-card reader-pad reader-main">
                <div class="reader-section-head">
                    <h2 class="reader-section-title">{{ __('My Submitted News') }}</h2>
                    <a href="{{ route('dashboard.news.create') }}" class="reader-section-link">{{ __('New submission') }}</a>
                </div>
                <div class="reader-list">
                    @forelse ($submissions as $submission)
                        <div class="reader-submission">
                            <div>
                                @if ($submission->is_approved)
                                    <a href="{{ route('news-details', $submission->slug) }}"><strong>{{ $submission->title }}</strong></a>
                                @else
                                    <strong>{{ $submission->title }}</strong>
                                @endif
                                <p>{{ $submission->category?->name }} &middot; {{ $submission->created_at->format('M d, Y') }}</p>
                            </div>
                            <span class="reader-status {{ $submission->is_approved ? 'reader-status-approved' : 'reader-status-pending' }}">
                                {{ $submission->is_approved ? __('Approved') : __('Pending Review') }}
                            </span>
                        </div>
                    @empty
                        <p class="reader-empty">{{ __('You have not submitted any news yet. Share a local update for the editorial team to review.') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="reader-card reader-pad reader-main">
                <div class="reader-section-head">
                    <h2 class="reader-section-title">{{ __('My Recent Comments') }}</h2>
                    <span class="reader-section-link">{{ $stats['comments'] }} {{ __('total') }}</span>
                </div>
                <div class="reader-list">
                    @forelse ($comments as $comment)
                        <div class="reader-comment">
                            <div>
                                <a href="{{ $comment->news ? route('news-details', $comment->news->slug) : route('news') }}">
                                    <strong>{{ $comment->news?->title ?? __('Deleted news') }}</strong>
                                </a>
                                <p>{{ $comment->comment }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="reader-empty">{{ __('You have not commented yet. Open a story and join the discussion.') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
