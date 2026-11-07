<div class="card border border-primary">
    <div class="card-body">
        <form action="{{ route('admin.general-setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="font-weight-bold">{{ __('admin.Site Name') }}</label>
                <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? '' }}" required>
                @error('site_name')
                    <p class="text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="font-weight-bold d-block">{{ __('admin.Site Logo') }}</label>
                <div class="mb-2 p-2 d-inline-block rounded border" style="background: #f4f6f9; min-width: 160px; min-height: 60px; text-align: center;">
                    @php
                        $siteLogoPath = !empty($settings['site_logo']) && file_exists(public_path($settings['site_logo']))
                            ? $settings['site_logo']
                            : (file_exists(public_path('uploads/extremis-news-header-logo.png')) ? 'uploads/extremis-news-header-logo.png' : null);
                    @endphp
                    @if (!empty($siteLogoPath))
                        <img id="site_logo_preview" src="{{ asset($siteLogoPath) }}?v={{ file_exists(public_path($siteLogoPath)) ? filemtime(public_path($siteLogoPath)) : time() }}" alt="Site Logo Preview" style="max-height: 70px; max-width: 260px; object-fit: contain;">
                    @else
                        <img id="site_logo_preview" src="" alt="Site Logo Preview" style="max-height: 70px; max-width: 260px; object-fit: contain; display: none;">
                        <span id="site_logo_placeholder" class="text-muted small d-block py-3">No logo uploaded yet</span>
                    @endif
                </div>
                <div class="custom-file mt-1">
                    <input type="file" name="site_logo" id="site_logo_input" class="form-control" accept="image/*">
                </div>
                <small class="text-muted d-block mt-1">Accepted formats: PNG, SVG, JPG, WebP. Recommended height: 40-70px.</small>
                @error('site_logo')
                    <p class="text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="font-weight-bold d-block">{{ __('admin.Site Favicon') }}</label>
                <div class="mb-2 p-2 d-inline-block rounded border" style="background: #f4f6f9; min-width: 50px; min-height: 50px; text-align: center;">
                    @php
                        $siteFaviconPath = !empty($settings['site_favicon']) && file_exists(public_path($settings['site_favicon']))
                            ? $settings['site_favicon']
                            : (file_exists(public_path('uploads/bgSXgTV5CNlB1pZwLyH0eyNp5O4cCZ.png')) ? 'uploads/bgSXgTV5CNlB1pZwLyH0eyNp5O4cCZ.png' : null);
                    @endphp
                    @if (!empty($siteFaviconPath))
                        <img id="site_favicon_preview" src="{{ asset($siteFaviconPath) }}?v={{ file_exists(public_path($siteFaviconPath)) ? filemtime(public_path($siteFaviconPath)) : time() }}" alt="Favicon Preview" style="max-height: 40px; max-width: 40px; object-fit: contain;">
                    @else
                        <img id="site_favicon_preview" src="" alt="Favicon Preview" style="max-height: 40px; max-width: 40px; object-fit: contain; display: none;">
                        <span id="site_favicon_placeholder" class="text-muted small d-block py-2">No favicon</span>
                    @endif
                </div>
                <div class="custom-file mt-1">
                    <input type="file" name="site_favicon" id="site_favicon_input" class="form-control" accept="image/*,.ico">
                </div>
                <small class="text-muted d-block mt-1">Accepted formats: ICO, PNG, SVG. Recommended: 32x32px or 64x64px.</small>
                @error('site_favicon')
                    <p class="text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> {{ __('admin.Save') }}</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var logoInput = document.getElementById('site_logo_input');
        if (logoInput) {
            logoInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(evt) {
                        var preview = document.getElementById('site_logo_preview');
                        var placeholder = document.getElementById('site_logo_placeholder');
                        if (preview) {
                            preview.src = evt.target.result;
                            preview.style.display = 'inline-block';
                        }
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }

        var faviconInput = document.getElementById('site_favicon_input');
        if (faviconInput) {
            faviconInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(evt) {
                        var preview = document.getElementById('site_favicon_preview');
                        var placeholder = document.getElementById('site_favicon_placeholder');
                        if (preview) {
                            preview.src = evt.target.result;
                            preview.style.display = 'inline-block';
                        }
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
    });
</script>
