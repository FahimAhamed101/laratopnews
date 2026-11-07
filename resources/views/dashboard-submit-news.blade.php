<x-app-layout>
    <x-slot name="header">
        <div class="reader-header">
            <div>
                <p class="reader-kicker">{{ __('Reader Submission') }}</p>
                <h2>{{ __('Submit News for Review') }}</h2>
            </div>
            <a href="{{ route('dashboard') }}" class="reader-button reader-button-dark">{{ __('Back to Dashboard') }}</a>
        </div>
    </x-slot>

    <style>
        .reader-wrap { max-width: 56rem; margin: 0 auto; padding: 2rem 1rem 3rem; }
        .reader-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .reader-header h2 { margin: .15rem 0 0; font-size: 1.35rem; line-height: 1.2; }
        .reader-kicker { margin: 0; color: #c8102e; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
        .reader-button { display: inline-flex; align-items: center; justify-content: center; min-height: 2.5rem; padding: 0 .95rem; border-radius: 8px; background: #c8102e; color: #fff; font-size: .9rem; font-weight: 700; text-decoration: none; border: 0; }
        .reader-button-dark { background: #111827; }
        .submit-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 2px rgba(15, 23, 42, .06); padding: 1.25rem; }
        .submit-grid { display: grid; gap: 1rem; }
        .submit-label { display: block; margin-bottom: .35rem; font-weight: 800; color: #111827; }
        .submit-help { margin: .35rem 0 0; color: #6b7280; font-size: .85rem; }
        .submit-input, .submit-select, .submit-textarea { width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 8px; padding: .7rem .8rem; font: inherit; }
        .submit-textarea { min-height: 220px; resize: vertical; line-height: 1.55; }
        .submit-error { margin: .35rem 0 0; color: #dc2626; font-size: .85rem; }
        .submit-actions { display: flex; justify-content: flex-end; gap: .75rem; padding-top: .5rem; }
        @media (max-width: 640px) { .reader-header { align-items: flex-start; flex-direction: column; } .submit-actions { justify-content: stretch; } .reader-button { width: 100%; } }
    </style>

    <div class="reader-wrap">
        <form method="POST" action="{{ route('dashboard.news.store') }}" enctype="multipart/form-data" class="submit-card submit-grid">
            @csrf

            <div>
                <label for="category_id" class="submit-label">{{ __('Category') }}</label>
                <select id="category_id" name="category_id" class="submit-select" required>
                    <option value="">{{ __('Choose a category') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="submit-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="title" class="submit-label">{{ __('Headline') }}</label>
                <input id="title" name="title" value="{{ old('title') }}" class="submit-input" maxlength="255" required>
                @error('title') <p class="submit-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="image" class="submit-label">{{ __('Main Image') }}</label>
                <input id="image" name="image" type="file" accept="image/*" class="submit-input" required>
                <p class="submit-help">{{ __('Upload a clear image. The editorial team can replace it before publishing if needed.') }}</p>
                @error('image') <p class="submit-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="content" class="submit-label">{{ __('Story Details') }}</label>
                <textarea id="content" name="content" class="submit-textarea" required>{{ old('content') }}</textarea>
                <p class="submit-help">{{ __('Include who, what, when, where, and why. Submitted news remains pending until an admin approves it.') }}</p>
                @error('content') <p class="submit-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="tags" class="submit-label">{{ __('Tags') }}</label>
                <input id="tags" name="tags" value="{{ old('tags') }}" class="submit-input" placeholder="local, community, transport">
                <p class="submit-help">{{ __('Separate tags with commas.') }}</p>
                @error('tags') <p class="submit-error">{{ $message }}</p> @enderror
            </div>

            <div class="submit-actions">
                <button type="submit" class="reader-button">{{ __('Submit for Review') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
