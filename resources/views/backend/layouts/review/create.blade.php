@extends('backend.app')

@section('title', 'Create Review')

@push('styles')
    <style>
        .star-rating { display: flex; gap: 4px; cursor: pointer; }
        .star-rating .star { font-size: 1.6rem; color: #d1d5db; transition: color .15s; }
        .star-rating .star.active { color: #f59e0b; }
        .star-rating .star:hover,
        .star-rating .star:hover ~ .star { color: #f59e0b; }
        .avatar-preview {
            width: 80px; height: 80px; border-radius: 50%;
            object-fit: cover; border: 3px solid #e2e8f0;
        }
        .avatar-initials {
            width: 80px; height: 80px; border-radius: 50%;
            background: #6366f1; color: #fff; font-size: 1.5rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Create Review</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <a href="{{ route('review.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fe fe-arrow-left me-1"></i> Back to Reviews
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fe fe-star me-1"></i> Review Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('review.store') }}" enctype="multipart/form-data" id="reviewForm">
                        @csrf

                        {{-- Property Selection --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Property <span class="text-danger">*</span></label>
                            <select name="property_id" id="property_id" class="form-select @error('property_id') is-invalid @enderror" required>
                                <option value="">-- Select Property --</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->property_id }}" {{ old('property_id') == $property->property_id ? 'selected' : '' }}>
                                        {{ $property->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('property_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Reviewer Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reviewer Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="reviewerName" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Sarah Johnson" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Avatar Upload --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reviewer Avatar (Optional)</label>
                            <div class="d-flex align-items-center gap-3">
                                <div id="avatarPreviewWrapper">
                                    <div class="avatar-initials" id="avatarInitials">?</div>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="avatar" id="avatarInput" class="form-control @error('avatar') is-invalid @enderror"
                                           accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">Upload a profile photo (JPEG, PNG, WebP, max 2MB). If empty, initials will be shown.</small>
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Star Rating --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
                            <div class="star-rating" id="starRating">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="star {{ old('rating_stars', 5) >= $i ? 'active' : '' }}" data-value="{{ $i }}">
                                        <i class="fa fa-star"></i>
                                    </span>
                                @endfor
                            </div>
                            <input type="hidden" name="rating_stars" id="ratingInput" value="{{ old('rating_stars', 5) }}">
                            @error('rating_stars')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Review Details --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Review Details <span class="text-danger">*</span></label>
                            <textarea name="details" class="form-control @error('details') is-invalid @enderror"
                                      rows="5" placeholder="Write the review content..." required>{{ old('details') }}</textarea>
                            @error('details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fe fe-check me-1"></i> Submit Review
                            </button>
                            <a href="{{ route('review.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Preview Card --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0"><i class="fe fe-eye me-1"></i> Preview</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div id="previewAvatarWrapper">
                            <div class="avatar-initials" id="previewInitials" style="width:40px;height:40px;font-size:0.9rem;">?</div>
                        </div>
                        <div class="flex-grow-1">
                            <strong id="previewName">Reviewer Name</strong>
                            <div class="text-muted small">just now</div>
                        </div>
                        <div class="text-warning" id="previewStars">
                            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0" id="previewBody">Review content will appear here...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Name change → update initials
            $('#reviewerName').on('input', function () {
                const name = $(this).val() || '?';
                updateInitials(name);
                $('#previewName').text(name || 'Reviewer Name');
            });

            // Star rating
            $('#starRating .star').on('click', function () {
                const val = $(this).data('value');
                $('#ratingInput').val(val);
                $('#starRating .star').each(function () {
                    $(this).toggleClass('active', $(this).data('value') <= val);
                });
                updatePreviewStars(val);
            });

            // Avatar preview
            $('#avatarInput').on('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (ev) {
                        $('#avatarPreviewWrapper').html('<img src="' + ev.target.result + '" class="avatar-preview">');
                        $('#previewAvatarWrapper').html('<img src="' + ev.target.result + '" class="avatar-preview" style="width:40px;height:40px;">');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Details preview
            $('textarea[name="details"]').on('input', function () {
                $('#previewBody').text($(this).val() || 'Review content will appear here...');
            });

            function updateInitials(name) {
                const words = name.trim().split(/\s+/);
                let initials = '';
                words.slice(0, 2).forEach(w => { initials += w.charAt(0).toUpperCase(); });
                initials = initials || '?';
                $('#avatarInitials').text(initials);
                $('#previewInitials').text(initials);
            }

            function updatePreviewStars(count) {
                let html = '';
                for (let i = 1; i <= 5; i++) {
                    html += i <= count
                        ? '<i class="fa fa-star"></i>'
                        : '<i class="fa fa-star-o text-muted"></i>';
                }
                $('#previewStars').html(html);
            }

            // Init
            updateInitials($('#reviewerName').val() || '?');
            updatePreviewStars($('#ratingInput').val() || 5);
        });
    </script>
@endpush