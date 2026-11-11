@extends('admin.layouts.master')
@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h1>{{ __('admin.Profile') }}</h1>
            <div class="section-header-breadcrumb">
                <a href="{{ route('author.profile', $user->id) }}" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fas fa-external-link-alt mr-1"></i> View Public Writer Profile
                </a>
            </div>
        </div>
        <div class="section-body">
            <h2 class="section-title">{{ __('admin.Hi') }}, {{ $user->name }}!</h2>
            <p class="section-lead">
                Manage your public journalist identity, editorial bio, social presence, and account security.
            </p>

            <div class="row mt-sm-4">
                <!-- Left Column: Writer / Editorial Profile Details -->
                <div class="col-12 col-lg-7">
                    <div class="card border border-primary">
                        <form method="post" action="{{ route('admin.profile.update', auth()->guard('admin')->user()->id) }}"
                            class="needs-validation" novalidate="" enctype="multipart/form-data">
                            @csrf
                            @method('put')
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h4 class="text-primary font-weight-bold mb-0">
                                    <i class="fas fa-feather-alt mr-1"></i> Writer & Journalist Profile
                                </h4>
                                <span class="badge badge-success">Active Author</span>
                            </div>
                            <div class="card-body">
                                <!-- Profile Avatar -->
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold">Author Avatar / Photo</label>
                                    <div id="image-preview" class="image-preview mb-2">
                                        <label for="image-upload" id="image-label">{{ __('admin.Choose File') }}</label>
                                        <input type="file" name="image" id="image-upload">
                                        <input type="hidden" name="old_image" value="{{ $user->image }}">
                                    </div>
                                    <small class="text-muted d-block">Recommended: square image (300x300px), max 3MB.</small>
                                    @error('image')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold">{{ __('admin.Full Name') }}</label>
                                        <input type="text" class="form-control" value="{{ old('name', $user->name) }}" required="" name='name'>
                                        @error('name')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold">{{ __('admin.Email') }}</label>
                                        <input type="email" class="form-control" value="{{ old('email', $user->email) }}" required="" name='email'>
                                        @error('email')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Designation / Editorial Role -->
                                <div class="form-group">
                                    <label class="font-weight-bold">Designation / Editorial Title</label>
                                    <input type="text" class="form-control" name="designation"
                                        value="{{ old('designation', $user->designation) }}"
                                        placeholder="e.g. Senior Investigative Journalist, Tech Reporter, Editor-in-Chief">
                                    <small class="text-muted">Displays on your article byline and public profile.</small>
                                    @error('designation')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Author Bio -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="font-weight-bold mb-0">Author Biography</label>
                                        <small class="text-muted"><span id="bio-counter">0</span> / 1000 chars</small>
                                    </div>
                                    <textarea name="bio" id="author-bio" class="form-control" rows="4" style="height: 120px;"
                                        placeholder="Share your journalistic experience, beat focus, and background. This displays at the bottom of all your articles for Google E-E-A-T and author credibility.">{{ old('bio', $user->bio) }}</textarea>
                                    @error('bio')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <hr class="my-4">
                                <h5 class="text-dark font-weight-bold mb-3" style="font-size: 15px;">
                                    <i class="fas fa-share-alt mr-1 text-primary"></i> Social Profiles & Web Presence
                                </h5>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label><i class="fas fa-globe text-muted mr-1"></i> Personal Website</label>
                                        <input type="text" class="form-control" name="website"
                                            value="{{ old('website', $user->website) }}" placeholder="https://yourwebsite.com">
                                        @error('website')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label><i class="fab fa-twitter text-info mr-1"></i> X (Twitter) Profile</label>
                                        <input type="text" class="form-control" name="twitter"
                                            value="{{ old('twitter', $user->twitter) }}" placeholder="https://twitter.com/username">
                                        @error('twitter')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label><i class="fab fa-linkedin text-primary mr-1"></i> LinkedIn Profile</label>
                                        <input type="text" class="form-control" name="linkedin"
                                            value="{{ old('linkedin', $user->linkedin) }}" placeholder="https://linkedin.com/in/username">
                                        @error('linkedin')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label><i class="fab fa-facebook text-primary mr-1"></i> Facebook Profile</label>
                                        <input type="text" class="form-control" name="facebook"
                                            value="{{ old('facebook', $user->facebook) }}" placeholder="https://facebook.com/username">
                                        @error('facebook')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label><i class="fab fa-instagram text-danger mr-1"></i> Instagram Profile</label>
                                        <input type="text" class="form-control" name="instagram"
                                            value="{{ old('instagram', $user->instagram) }}" placeholder="https://instagram.com/username">
                                        @error('instagram')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right bg-light">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save mr-1"></i> {{ __('admin.Save Changes') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Writer Card Preview & Security -->
                <div class="col-12 col-lg-5">
                    <!-- Live Author Badge Card Preview -->
                    <div class="card border border-info mb-4" style="background: #f8fafc;">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 text-info font-weight-bold" style="font-size: 15px;">
                                <i class="fas fa-id-badge mr-1"></i> Article Byline Preview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="p-3 bg-white rounded shadow-sm border">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset($user->image) }}" alt="{{ $user->name }}" class="rounded-circle mr-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div>
                                        <h5 class="mb-0 font-weight-bold text-dark">{{ $user->name }}</h5>
                                        <span class="badge badge-primary py-1 px-2 font-weight-bold" style="font-size: 11px;">
                                            {{ $user->designation ?: 'Editorial Staff' }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3" style="line-height: 1.5;">
                                    {{ Str::limit($user->bio ?: 'Experienced journalist reporting verified news and analysis.', 120) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="text-muted small"><i class="fas fa-newspaper mr-1"></i> {{ $user->publishedArticlesCount() }} Published Stories</span>
                                    <span class="text-muted small"><i class="fas fa-eye mr-1"></i> {{ number_format($user->totalViews()) }} Total Views</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security / Password Update -->
                    <div class="card">
                        <form method="post" action="{{ route('admin.profile-password.update', $user->id) }}"
                            class="needs-validation" novalidate="">
                            @csrf
                            @method('put')
                            <div class="card-header">
                                <h4 class="font-weight-bold"><i class="fas fa-key mr-1"></i> {{ __('admin.Update Password') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group col-12">
                                    <label>{{ __('admin.Old Password') }}</label>
                                    <input type="password" class="form-control" required="" name="current_password">
                                    @error('current_password')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group col-12">
                                    <label>{{ __('admin.New Password') }}</label>
                                    <input type="password" class="form-control" required="" name="password">
                                </div>
                                <div class="form-group col-12">
                                    <label>{{ __('admin.Confirm Password') }}</label>
                                    <input type="password" class="form-control" required="" name="password_confirmation">
                                </div>
                            </div>
                            <div class="card-footer text-right bg-light">
                                <button class="btn btn-primary">{{ __('admin.Save Changes') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.image-preview').css({
                "background-image": "url({{ asset($user->image) }})",
                "background-size": "cover",
                "background-position": "center center"
            });

            function updateBioCount() {
                var len = $('#author-bio').val().length;
                $('#bio-counter').text(len);
            }
            $('#author-bio').on('input', updateBioCount);
            updateBioCount();
        })
    </script>
@endpush
