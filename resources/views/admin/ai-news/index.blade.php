@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-robot text-primary mr-2"></i> AI Worldwide News Auto-Poster</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.news.index') }}">News</a></div>
            <div class="breadcrumb-item">AI Auto-Poster</div>
        </div>
    </div>

    {{-- System Status Banner --}}
    <div class="row">
        <div class="col-12">
            <div class="card {{ $connectionStatus['success'] ? 'card-success' : 'card-danger' }} shadow-sm">
                <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="badge badge-{{ $connectionStatus['success'] ? 'success' : 'danger' }} p-2 mr-3 font-weight-bold">
                            <i class="fas {{ $connectionStatus['success'] ? 'fa-check-circle' : 'fa-exclamation-triangle' }} mr-1"></i>
                            {{ $connectionStatus['success'] ? 'ENDPOINT CONNECTED' : 'CONNECTION ERROR' }}
                        </div>
                        <div>
                            <strong>Model:</strong> <span class="badge badge-light border">{{ config('ai_news.model', 'deepseek-expert') }}</span>
                            <span class="mx-2 text-muted">|</span>
                            <strong>Endpoint:</strong> <code class="text-dark bg-light px-2 py-1 rounded">{{ Str::limit(config('ai_news.endpoint'), 60) }}</code>
                            @if ($connectionStatus['success'])
                                <span class="mx-2 text-muted">|</span>
                                <span class="text-success"><i class="fas fa-bolt"></i> Latency: {{ $connectionStatus['latency_ms'] }}ms</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <button id="btn-test-conn" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-sync-alt"></i> Re-test Health
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Generation Controls --}}
    <div class="row">
        {{-- Fast Auto Pilot Mode --}}
        <div class="col-lg-5">
            <div class="card card-primary h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="text-primary"><i class="fas fa-bolt mr-2"></i> 1-Click Worldwide Auto-Pilot</h4>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p class="text-muted">
                        Automatically selects a trending high-volume international topic across technology, business, science, or world affairs, applies a unique journalistic angle, creates full E-E-A-T analysis with FAQs, draws an exclusive 1200x675 banner, and publishes live immediately.
                    </p>
                    <div class="p-3 bg-light rounded border mb-4">
                        <ul class="list-unstyled mb-0 font-weight-600 text-dark small">
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> 800–1200+ Words Long-form Article</li>
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Keyword-Rich Semantic Headings (H2, H3)</li>
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Key Takeaways & Expert Quotations</li>
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Google Schema-Ready FAQ Section</li>
                            <li><i class="fas fa-check text-success mr-2"></i> Auto-Generated Branded Cover Banner</li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.ai-news.generate') }}" method="POST" class="ai-gen-form">
                        @csrf
                        <input type="hidden" name="count" value="1">
                        <input type="hidden" name="language" value="en">
                        <button type="submit" class="btn btn-primary btn-lg btn-block shadow-primary font-weight-bold py-3 submit-btn">
                            <i class="fas fa-magic mr-2"></i> ⚡ Publish 1 Trending Story Now
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Custom Parameters Generator --}}
        <div class="col-lg-7">
            <div class="card card-info h-100 shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="text-info"><i class="fas fa-sliders-h mr-2"></i> Customized Generation & Publishing</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ai-news.generate') }}" method="POST" class="ai-gen-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Target Category</label>
                                <select name="category_slug" class="form-control selectric">
                                    <option value="">-- Random Worldwide Category --</option>
                                    <option value="technology">Technology & AI</option>
                                    <option value="business">Business & Global Finance</option>
                                    <option value="health">Healthcare & Medicine</option>
                                    <option value="climate">Climate & Renewable Energy</option>
                                    <option value="sports">Sports & Athletics</option>
                                    <option value="national">National & World Affairs</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Language</label>
                                <select name="language" class="form-control selectric">
                                    <option value="en" selected>English (Global)</option>
                                    <option value="bn">Bengali (বাংলা)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Custom Topic / Headline Angle <span class="text-muted font-weight-normal">(Optional)</span></label>
                            <input type="text" name="topic" class="form-control" placeholder="e.g. Next-Gen Nuclear Fusion Reactor Startup Achieves Commercial Net Energy...">
                            <small class="form-text text-muted">Leave empty to allow DeepSeek AI to automatically select an original high-traffic global story.</small>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Number of Articles</label>
                                <select name="count" class="form-control selectric">
                                    <option value="1" selected>1 Article (Immediate)</option>
                                    <option value="2">2 Unique Articles</option>
                                    <option value="3">3 Unique Articles</option>
                                    <option value="5">5 Batch Articles</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group pt-md-3">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" name="is_breaking" value="1" class="custom-control-input" id="isBreaking">
                                    <label class="custom-control-label font-weight-bold text-danger" for="isBreaking">
                                        <i class="fas fa-fire mr-1"></i> Flag as Breaking News
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-info btn-lg px-4 font-weight-bold submit-btn">
                                <i class="fas fa-paper-plane mr-2"></i> Generate & Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    </div>

    {{-- Live Google News Search & 1-Click Publishing --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-warning shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h4 class="text-dark"><i class="fab fa-google text-primary mr-2"></i> Live Google News Search & 1-Click Publishing</h4>
                    <span class="badge badge-primary font-weight-bold"><i class="fas fa-rss mr-1"></i> Real-time Popular Sources (AP, Reuters, BBC, etc.)</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-5 form-group mb-2">
                            <label class="font-weight-bold">Search Keywords / Wire Topic</label>
                            <div class="input-group">
                                <input type="text" id="google-search-query" class="form-control" placeholder="e.g. OpenAI GPT-5, SpaceX Starship, Global Economy, Formula 1...">
                            </div>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label class="font-weight-bold">Category</label>
                            <select id="google-search-cat" class="form-control selectric">
                                <option value="technology">Technology & AI</option>
                                <option value="business">Business & Markets</option>
                                <option value="sports">Sports</option>
                                <option value="health">Health & Medicine</option>
                                <option value="climate">Science & Climate</option>
                                <option value="national">World & National</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <label class="font-weight-bold">Language</label>
                            <select id="google-search-lang" class="form-control selectric">
                                <option value="en">English (Global)</option>
                                <option value="bn">Bengali (বাংলা)</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-2 d-flex align-items-end">
                            <button id="btn-search-google" class="btn btn-primary btn-block font-weight-bold py-2">
                                <i class="fas fa-search mr-1"></i> Search Google
                            </button>
                        </div>
                    </div>

                    {{-- Search Results Container --}}
                    <div id="google-news-results" style="display: none;">
                        <hr>
                        <h6 class="font-weight-bold text-muted mb-3"><i class="fas fa-list-alt mr-1"></i> Trending Stories Found on Google News:</h6>
                        <div id="google-news-list" class="row"></div>
                    </div>
                </div>
            </div>
        </div>
    {{-- Article Pruning & Retention Management (Delete Low Views after 30d) --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-danger shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h4 class="text-danger"><i class="fas fa-trash-alt mr-2"></i> Low-Traffic Article Pruning & Retention Manager</h4>
                    <span class="badge badge-danger font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> High-Traffic Posts Are Safely Preserved</span>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Keep your site lightweight and SEO authority strong by automatically pruning dead or thin articles that did not gain audience traction. Posts with more views than your threshold will <strong>never</strong> be deleted.
                    </p>
                    <form action="{{ route('admin.ai-news.delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to prune articles older than the selected age with views below the threshold? High-traffic articles will be preserved.');">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-3 form-group mb-2">
                                <label class="font-weight-bold">Delete Older Than</label>
                                <div class="input-group">
                                    <input type="number" name="days" class="form-control" value="30" min="1" max="365">
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="font-weight-bold">Views Threshold (Low Views)</label>
                                <div class="input-group">
                                    <input type="number" name="min_views" class="form-control" value="50" min="1" max="10000">
                                    <div class="input-group-append">
                                        <span class="input-group-text">views</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Only delete if views &lt; threshold</small>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="font-weight-bold">Max Articles Retention Cap</label>
                                <input type="number" name="keep_max" class="form-control" value="500" min="10" max="10000">
                                <small class="form-text text-muted">Safe buffer for site capacity</small>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <button type="submit" class="btn btn-danger btn-block font-weight-bold py-2">
                                    <i class="fas fa-broom mr-1"></i> Prune Low Views Now
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-newspaper mr-2"></i> Recently Published News Articles</h4>
                    <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary btn-sm">View All in News Manager</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 70px;">Cover</th>
                                    <th>Headline & URL</th>
                                    <th>Category</th>
                                    <th>Lang</th>
                                    <th>Length</th>
                                    <th>Date</th>
                                    <th class="text-center" style="width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentNews as $item)
                                    @php
                                        $words = str_word_count(strip_tags($item->content));
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($item->image)
                                                <img src="{{ asset($item->image) }}" alt="cover" class="rounded" style="width: 60px; height: 35px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 35px;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('news-details', $item->slug) }}" target="_blank" class="font-weight-600 text-dark d-block">
                                                {{ Str::limit($item->title, 65) }}
                                            </a>
                                            <small class="text-muted">
                                                <code>/news-details/{{ Str::limit($item->slug, 35) }}</code>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border">{{ $item->category->name ?? 'General' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info text-uppercase">{{ $item->language }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $words >= 600 ? 'success' : 'warning' }}">
                                                {{ $words }} words
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('news-details', $item->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View on site">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                            <a href="{{ route('admin.news.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit in admin">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No news articles found. Generate your first one above!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Progress Modal --}}
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center p-4">
            <div class="spinner-border text-primary mx-auto mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <h5 class="font-weight-bold mb-1">DeepSeek AI Writing Article...</h5>
            <p class="text-muted small mb-0">Crafting in-depth analysis, optimizing SEO headings, formatting FAQs, and rendering branded feature image. Please wait...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.ai-gen-form').forEach(form => {
        form.addEventListener('submit', function() {
            $('#loadingModal').modal('show');
            this.querySelectorAll('.submit-btn').forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating Content...';
            });
        });
    });

    document.getElementById('btn-test-conn')?.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

        fetch("{{ route('admin.ai-news.test-connection') }}")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Success! Endpoint connected with ' + data.latency_ms + 'ms latency.');
                    location.reload();
                } else {
                    alert('Connection Failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                alert('Connection test failed: ' + err.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt"></i> Re-test Health';
            });
    // Live Google News Search Engine
    const searchBtn = document.getElementById('btn-search-google');
    const searchResults = document.getElementById('google-news-results');
    const newsList = document.getElementById('google-news-list');

    searchBtn?.addEventListener('click', function() {
        const query = document.getElementById('google-search-query').value;
        const category = document.getElementById('google-search-cat').value;
        const language = document.getElementById('google-search-lang').value;

        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Searching...';
        searchResults.style.display = 'block';
        newsList.innerHTML = '<div class="col-12 py-4 text-center text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i><br>Contacting Google News RSS and scanning popular sources...</div>';

        const url = new URL("{{ route('admin.ai-news.search-google') }}");
        if (query) url.searchParams.append('query', query);
        if (category) url.searchParams.append('category_slug', category);
        if (language) url.searchParams.append('language', language);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.stories && data.stories.length > 0) {
                    let html = '';
                    data.stories.forEach(story => {
                        const safeTitle = story.clean_title.replace(/"/g, '&quot;');
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card border h-100 shadow-none bg-light">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge badge-primary"><i class="fas fa-newspaper mr-1"></i> ${story.source}</span>
                                                <small class="text-muted"><i class="far fa-clock mr-1"></i> ${story.pub_date || ''}</small>
                                            </div>
                                            <h6 class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem; line-height: 1.4;">${story.clean_title}</h6>
                                            ${story.snippet ? `<p class="text-muted small mb-3">${story.snippet}</p>` : ''}
                                        </div>
                                        <form action="{{ route('admin.ai-news.generate') }}" method="POST" class="ai-gen-form mt-2">
                                            @csrf
                                            <input type="hidden" name="topic" value="${safeTitle}">
                                            <input type="hidden" name="category_slug" value="${category}">
                                            <input type="hidden" name="language" value="${language}">
                                            <input type="hidden" name="count" value="1">
                                            <button type="submit" class="btn btn-warning btn-sm btn-block font-weight-bold submit-btn">
                                                <i class="fas fa-bolt mr-1"></i> ⚡ Write & Publish Full Story with AI
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    newsList.innerHTML = html;

                    // Re-bind submit listener for new forms
                    newsList.querySelectorAll('.ai-gen-form').forEach(form => {
                        form.addEventListener('submit', function() {
                            $('#loadingModal').modal('show');
                            this.querySelectorAll('.submit-btn').forEach(btn => {
                                btn.disabled = true;
                                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating Content...';
                            });
                        });
                    });
                } else {
                    newsList.innerHTML = '<div class="col-12 py-3 text-center text-muted"><i class="fas fa-info-circle mr-1"></i> No matching stories found on Google News. Try different search terms or categories.</div>';
                }
            })
            .catch(err => {
                newsList.innerHTML = '<div class="col-12 py-3 text-center text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Failed to fetch stories from Google News: ' + err.message + '</div>';
            })
            .finally(() => {
                searchBtn.disabled = false;
                searchBtn.innerHTML = '<i class="fas fa-search mr-1"></i> Search Google';
            });
    });
</script>
@endpush
