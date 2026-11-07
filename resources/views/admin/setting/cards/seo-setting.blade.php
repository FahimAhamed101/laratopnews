<div class="card border border-primary" style="background: #ffffff; border-radius: 8px;">
    <div class="card-header bg-white py-3" style="border-bottom: 1px solid #e2e8f0;">
        <h5 class="mb-0 text-primary font-weight-bold" style="font-size: 16px;">
            <i class="fas fa-globe-americas mr-1"></i> Global Site Search Engine Optimization (SEO)
        </h5>
        <small class="text-muted">Configure default metadata, homepage Google SERP snippet, and search discovery tags</small>
    </div>

    <div class="card-body">
        <!-- Live Google Search Result Simulator for Homepage -->
        <div class="google-home-preview p-3 mb-4 rounded" style="background: #f8fafc; border: 1px solid #dfe1e5; max-width: 650px;">
            <div class="d-flex align-items-center mb-1">
                <div class="rounded-circle mr-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background: #e8f0fe;">
                    <i class="fas fa-globe text-primary" style="font-size: 12px;"></i>
                </div>
                <div style="font-size: 12px; color: #202124; line-height: 1.3;">
                    <span class="font-weight-bold">{{ $settings['site_name'] ?? 'Extremis News' }}</span>
                    <span class="text-muted d-block" style="font-size: 11px;">
                        {{ config('app.url') }}
                    </span>
                </div>
            </div>
            <h4 id="home-google-title" style="color: #1a0dab; font-size: 18px; line-height: 1.3; font-weight: 500; margin: 4px 0; font-family: Roboto, Arial, sans-serif;">
                {{ $settings['site_seo_title'] ?? 'Extremis News - Verified News & Analysis' }}
            </h4>
            <p id="home-google-desc" style="color: #4d5156; font-size: 13.5px; line-height: 1.45; margin: 0; font-family: Roboto, Arial, sans-serif;">
                {{ $settings['site_seo_description'] ?? 'Extremis News delivers breaking global news, investigative reports, and in-depth analysis.' }}
            </p>
        </div>

        <form action="{{ route('admin.seo-setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="site_seo_title" class="font-weight-bold mb-0">{{ __('admin.Site Seo Title') }}</label>
                    <small class="text-muted">
                        <span id="site-title-count" class="font-weight-bold">0</span> / 60 characters
                        <span id="site-title-badge" class="badge badge-secondary ml-1">Normal</span>
                    </small>
                </div>
                <input type="text" id="site_seo_title" name="site_seo_title" value="{{ old('site_seo_title', $settings['site_seo_title']) }}" class="form-control" maxlength="60" placeholder="e.g. Extremis News - Verified Global News & Analysis" required>
                <small class="form-text text-muted">Primary title indexed by Google for the homepage. Recommended length: 50 - 60 characters.</small>
                @error('site_seo_title')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="site_seo_description" class="font-weight-bold mb-0">{{ __('admin.Site Seo Description') }}</label>
                    <small class="text-muted">
                        <span id="site-desc-count" class="font-weight-bold">0</span> / 160 characters
                        <span id="site-desc-badge" class="badge badge-secondary ml-1">Normal</span>
                    </small>
                </div>
                <textarea name="site_seo_description" id="site_seo_description" class="form-control" style="height: 110px;" rows="4" maxlength="160" placeholder="e.g. Extremis News delivers breaking global news, verified investigative reports, and in-depth political and economic analysis." required>{{ old('site_seo_description', $settings['site_seo_description']) }}</textarea>
                <small class="form-text text-muted">Primary meta description displayed in Google search results. Recommended length: 120 - 160 characters.</small>
                @error('site_seo_description')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="site_seo_keywords" class="font-weight-bold">{{ __('admin.Site Seo Keywords') }}</label>
                <input name="site_seo_keywords" id="site_seo_keywords" type="text" class="form-control inputtags"
                    value="{{ old('site_seo_keywords', $settings['site_seo_keywords']) }}" placeholder="news, breaking news, politics, world affairs, economy, technology" required>
                <small class="form-text text-muted">Comma-separated keywords representing topics covered by your news portal.</small>
                @error('site_seo_keywords')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> {{ __('admin.Save') }}</button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const titleInput = document.getElementById("site_seo_title");
    const descInput = document.getElementById("site_seo_description");
    const titlePreview = document.getElementById("home-google-title");
    const descPreview = document.getElementById("home-google-desc");
    const titleCount = document.getElementById("site-title-count");
    const descCount = document.getElementById("site-desc-count");
    const titleBadge = document.getElementById("site-title-badge");
    const descBadge = document.getElementById("site-desc-badge");

    function syncTitle() {
        if (!titleInput) return;
        const val = titleInput.value.trim();
        titlePreview.textContent = val || "Extremis News - Verified News & Analysis";
        const len = titleInput.value.length;
        titleCount.textContent = len;
        if (len >= 40 && len <= 60) {
            titleBadge.className = "badge badge-success ml-1";
            titleBadge.textContent = "Optimal";
        } else if (len < 40) {
            titleBadge.className = "badge badge-info ml-1";
            titleBadge.textContent = "Short";
        } else {
            titleBadge.className = "badge badge-warning ml-1";
            titleBadge.textContent = "Too Long";
        }
    }

    function syncDesc() {
        if (!descInput) return;
        const val = descInput.value.trim();
        descPreview.textContent = val || "Extremis News delivers breaking global news, investigative reports, and in-depth analysis.";
        const len = descInput.value.length;
        descCount.textContent = len;
        if (len >= 120 && len <= 160) {
            descBadge.className = "badge badge-success ml-1";
            descBadge.textContent = "Optimal";
        } else if (len < 120) {
            descBadge.className = "badge badge-info ml-1";
            descBadge.textContent = "Short";
        } else {
            descBadge.className = "badge badge-warning ml-1";
            descBadge.textContent = "Too Long";
        }
    }

    if (titleInput) {
        titleInput.addEventListener("input", syncTitle);
        syncTitle();
    }
    if (descInput) {
        descInput.addEventListener("input", syncDesc);
        syncDesc();
    }
});
</script>
