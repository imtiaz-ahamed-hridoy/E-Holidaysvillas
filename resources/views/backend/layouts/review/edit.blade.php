@extends('backend.app')

@section('title', 'Edit Review')

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
            <h1 class="page-title">Edit Review</h1>
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
                    <h5 class="card-title mb-0"><i class="fe fe-edit me-1"></i> Review Details</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('review.update', ['id' => $data->review_id]) }}"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Property (read-only) --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Property</label>
                            <input type="text" class="form-control" value="{{ $data->property->title ?? 'N/A' }}" readonly>
                        </div>

                        {{-- Reviewer Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reviewer Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="reviewerName" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $data->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Avatar --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reviewer Avatar</label>
                            <div class="d-flex align-items-center gap-3">
                                <div id="avatarPreviewWrapper">
                                    @if($data->avatar)
                                        <img src="{{ \App\Helpers\Helper::imageUrl('uploads/reviews/' . $data->avatar) }}" class="avatar-preview" id="currentAvatar">
                                    @else
                                        @php
                                            $words = explode(' ', trim($data->name ?? 'G'));
                                            $initials = '';
                                            foreach (array_slice($words, 0, 2) as $w) { $initials .= strtoupper(mb_substr($w, 0, 1)); }
                                        @endphp
                                        <div class="avatar-initials" id="avatarInitials">{{ $initials ?: '?' }}</div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="avatar" id="avatarInput" class="form-control @error('avatar') is-invalid @enderror"
                                           accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">Upload new avatar to replace. Leave empty to keep current.</small>
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
                                    <span class="star {{ old('rating_stars', $data->rating_stars) >= $i ? 'active' : '' }}" data-value="{{ $i }}">
                                        <i class="fa fa-star"></i>
                                    </span>
                                @endfor
                            </div>
                            <input type="hidden" name="rating_stars" id="ratingInput" value="{{ old('rating_stars', $data->rating_stars) }}">
                            @error('rating_stars')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Review Details --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Review Details <span class="text-danger">*</span></label>
                            <textarea name="details" class="form-control @error('details') is-invalid @enderror"
                                      rows="5" required>{{ old('details', $data->details) }}</textarea>
                            @error('details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ old('status', $data->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $data->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fe fe-check me-1"></i> Update Review
                            </button>
                            <a href="{{ route('review.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Star rating
            $('#starRating .star').on('click', function () {
                const val = $(this).data('value');
                $('#ratingInput').val(val);
                $('#starRating .star').each(function () {
                    $(this).toggleClass('active', $(this).data('value') <= val);
                });
            });

            // Avatar preview
            $('#avatarInput').on('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (ev) {
                        $('#avatarPreviewWrapper').html('<img src="' + ev.target.result + '" class="avatar-preview">');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush