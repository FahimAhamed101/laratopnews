@extends('admin.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ __('admin.News') }}</h1>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h4>{{ __('admin.Create News') }}</h4>

            </div>
            <div class="card-body">
                <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="">{{ __('admin.Language') }}</label>
                        <select name="language" id="language-select" class="form-control select2">
                            <option value="">--{{ __('admin.Select') }}--</option>
                            @foreach ($languages as $lang)
                                <option value="{{ $lang->lang }}">{{ $lang->name }}</option>
                            @endforeach
                        </select>
                        @error('language')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('admin.Category') }}</label>
                        <select name="category" id="category" class="form-control select2">
                            <option value="">--{{ __('admin.Select') }}---</option>

                        </select>
                        @error('category')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="form-group">
                        <label for="">{{ __('admin.Image') }}</label>
                        <div id="image-preview" class="image-preview">
                            <label for="image-upload" id="image-label">{{ __('admin.Choose File') }}</label>
                            <input type="file" name="image" id="image-upload">
                        </div>
                        @error('image')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="news-title">{{ __('admin.Title') }}</label>
                        <input name="title" value="{{ old('title') }}" type="text" class="form-control" id="news-title" required>
                        @error('title')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('admin.Content') }}</label>
                        <textarea name="content" id="news-content" class="summernote-simple">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="">{{ __('admin.Tags') }}</label>
                        <input name="tags" value="{{ old('tags') }}" type="text" class="form-control inputtags">
                        @error('tags')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Enhanced SEO & Google Preview Section -->
                    <div class="card border border-primary mb-4" style="background: #f8fafc; border-radius: 8px;">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" style="border-bottom: 1px solid #e2e8f0;">
                            <div>
                                <h5 class="mb-0 text-primary font-weight-bold" style="font-size: 16px;">
                                    <i class="fas fa-search-plus mr-1"></i> Search Engine Optimization (SEO) & Google Preview
                                </h5>
                                <small class="text-muted">Preview how this article appears in Google search results and social media shares</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-auto-seo">
                                <i class="fas fa-magic mr-1"></i> Auto-Generate SEO from Content
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Live Google SERP Preview Box -->
                            <div class="google-serp-preview p-3 mb-4 rounded" style="background: #ffffff; border: 1px solid #dfe1e5; max-width: 650px;">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="rounded-circle mr-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background: #e8f0fe;">
                                        <i class="fas fa-globe text-primary" style="font-size: 12px;"></i>
                                    </div>
                                    <div style="font-size: 12px; color: #202124; line-height: 1.3;">
                                        <span class="font-weight-bold">{{ $settings['site_name'] ?? 'Extremis News' }}</span>
                                        <span class="text-muted d-block" style="font-size: 11px;">
                                            {{ config('app.url') }} &rsaquo; news &rsaquo; <span id="preview-slug">article-slug</span>
                                        </span>
                                    </div>
                                </div>
                                <h4 id="preview-google-title" style="color: #1a0dab; font-size: 18px; line-height: 1.3; font-weight: 500; margin: 4px 0; font-family: Roboto, Arial, sans-serif;">
                                    {{ old('meta_title', 'Your Article Title Will Appear Here') }} | {{ $settings['site_name'] ?? 'Extremis News' }}
                                </h4>
                                <p id="preview-google-desc" style="color: #4d5156; font-size: 13.5px; line-height: 1.45; margin: 0; font-family: Roboto, Arial, sans-serif;">
                                    {{ old('meta_description', 'Provide a concise, engaging meta description to improve click-through rates from search engine result pages.') }}
                                </p>
                            </div>

                            <div class="form-group mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="meta_title" class="font-weight-bold mb-0">{{ __('admin.Meta Title') }}</label>
                                    <small class="text-muted">
                                        <span id="title-counter" class="font-weight-bold">0</span> / 60 characters
                                        <span id="title-badge" class="badge badge-secondary ml-1">Normal</span>
                                    </small>
                                </div>
                                <input name="meta_title" value="{{ old('meta_title') }}" type="text" class="form-control" id="meta_title" placeholder="Custom SEO Title for Google (recommended 50-60 characters)">
                                <small class="form-text text-muted">Leave empty to automatically use article headline.</small>
                                @error('meta_title')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group mb-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="meta_description" class="font-weight-bold mb-0">{{ __('admin.Meta Description') }}</label>
                                    <small class="text-muted">
                                        <span id="desc-counter" class="font-weight-bold">0</span> / 160 characters
                                        <span id="desc-badge" class="badge badge-secondary ml-1">Normal</span>
                                    </small>
                                </div>
                                <textarea name="meta_description" id="meta_description" rows="3" class="form-control" placeholder="Compelling summary shown under Google search title (recommended 120-160 characters)">{{ old('meta_description') }}</textarea>
                                <small class="form-text text-muted">Leave empty to automatically generate from first paragraph of content.</small>
                                @error('meta_description')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="control-label">{{ __('admin.Status') }}</div>
                                <label class="custom-switch mt-2">
                                    <input value="1" type="checkbox" name="status" class="custom-switch-input">
                                    <span class="custom-switch-indicator"></span>
                                </label>
                            </div>
                        </div>

                        @if (canAccess(['news status', 'news all-access']))
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="control-label">{{ __('admin.Is Breaking News') }}</div>
                                    <label class="custom-switch mt-2">
                                        <input value="1" type="checkbox" name="is_breaking_news"
                                            class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="control-label">{{ __('admin.Show At Slider') }}</div>
                                    <label class="custom-switch mt-2">
                                        <input value="1" type="checkbox" name="show_at_slider"
                                            class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3">

                                <div class="form-group">
                                    <div class="control-label">{{ __('admin.Show At Popular') }}</div>
                                    <label class="custom-switch mt-2">
                                        <input value="1" type="checkbox" name="show_at_popular"
                                            class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                    </label>
                                </div>

                            </div>
                        @endif

                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('admin.Create') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#language-select').on('change', function() {
                let lang = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "{{ route('admin.fetch-news-category') }}",
                    data: {
                        lang: lang
                    },
                    success: function(data) {
                        $('#category').html("");
                        $('#category').html(
                            `<option value="">---{{ __('admin.Select') }}---</option>`);

                        $.each(data, function(index, data) {
                            $('#category').append(
                                `<option value="${data.id}">${data.name}</option>`)
                        })

                    },
                    error: function(error) {
                        console.log(error);
                    }
                })
            })

            // Live SEO Preview & Counters
            const siteName = "{{ $settings['site_name'] ?? 'Extremis News' }}";

            function slugify(text) {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
            }

            function updateTitleCounter() {
                let metaVal = $('#meta_title').val();
                let titleVal = $('#news-title').val();
                let displayTitle = metaVal.trim() ? metaVal.trim() : (titleVal.trim() ? titleVal.trim() : 'Your Article Title Will Appear Here');

                $('#preview-google-title').text(displayTitle + ' | ' + siteName);
                let count = metaVal.length;
                $('#title-counter').text(count);

                let badge = $('#title-badge');
                if (count === 0) {
                    badge.attr('class', 'badge badge-secondary ml-1').text('Auto');
                } else if (count >= 40 && count <= 60) {
                    badge.attr('class', 'badge badge-success ml-1').text('Optimal');
                } else if (count < 40) {
                    badge.attr('class', 'badge badge-info ml-1').text('Short');
                } else {
                    badge.attr('class', 'badge badge-warning ml-1').text('Too Long (' + count + '/60)');
                }

                if (titleVal.trim()) {
                    $('#preview-slug').text(slugify(titleVal));
                }
            }

            function updateDescCounter() {
                let descVal = $('#meta_description').val();
                let displayDesc = descVal.trim() ? descVal.trim() : 'Provide a concise, engaging meta description to improve click-through rates from search engine result pages.';
                $('#preview-google-desc').text(displayDesc);

                let count = descVal.length;
                $('#desc-counter').text(count);

                let badge = $('#desc-badge');
                if (count === 0) {
                    badge.attr('class', 'badge badge-secondary ml-1').text('Auto');
                } else if (count >= 120 && count <= 160) {
                    badge.attr('class', 'badge badge-success ml-1').text('Optimal');
                } else if (count < 120) {
                    badge.attr('class', 'badge badge-info ml-1').text('Short');
                } else {
                    badge.attr('class', 'badge badge-warning ml-1').text('Too Long (' + count + '/160)');
                }
            }

            $('#news-title').on('input', updateTitleCounter);
            $('#meta_title').on('input', updateTitleCounter);
            $('#meta_description').on('input', updateDescCounter);

            // Auto-Generate SEO from Content
            $('#btn-auto-seo').on('click', function() {
                let title = $('#news-title').val().trim();
                let contentHtml = $('#news-content').val();
                if (!contentHtml && typeof $('.summernote-simple').summernote === 'function') {
                    contentHtml = $('.summernote-simple').summernote('code');
                }

                // Strip HTML
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = contentHtml || '';
                let rawText = tempDiv.textContent || tempDiv.innerText || '';
                rawText = rawText.replace(/\s+/g, ' ').trim();

                if (title) {
                    $('#meta_title').val(title.substring(0, 60));
                }
                if (rawText) {
                    $('#meta_description').val(rawText.substring(0, 155));
                }

                updateTitleCounter();
                updateDescCounter();
            });

            // Initial counter sync
            updateTitleCounter();
            updateDescCounter();
        })
    </script>
@endpush
