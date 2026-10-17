@extends('backend.app')

@section('title', 'Edit Property Type')

@push('styles')
    <style>
        .marker-icon-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            background: #fdfdfd;
            border-radius: 5px;
        }

        .marker-item {
            text-align: center;
            padding: 10px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            background: #f0f0f0;
            border-radius: 5px;
        }

        .marker-item:hover {
            background: #e0e0e0;
        }

        .marker-item.active {
            background: #e9f7fe;
            border-color: #05c3fb;
        }

        .marker-item img {
            width: 40px;
            height: 40px;
            display: block;
            margin: 0 auto 5px;
            object-fit: contain;
        }

        .marker-item input {
            position: absolute;
            opacity: 0;
        }

        .marker-item .custom-radio {
            width: 16px;
            height: 16px;
            border: 1px solid #ccc;
            border-radius: 50%;
            display: inline-block;
            background-color: #fff;
        }

        .marker-item.active .custom-radio {
            background: #fff;
            border: 5px solid #0056b3;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Property Type</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
                <li class="breadcrumb-item active" aria-current="page">Property Type</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Property Type Details</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('property-type.update', $data->property_type_id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="{{ old('title', is_array($data->title) ? (isset($data->title['en']) ? $data->title['en'] : json_encode($data->title)) : $data->title) }}"
                                required>
                            @error('title')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="slug">Slug <i class="fe fe-help-circle text-warning fs-14"
                                    title="Unique URL-friendly name"></i></label>
                            <input type="text" name="slug" id="slug" class="form-control"
                                value="{{ old('slug', $data->slug) }}">
                            @error('slug')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label fs-16">Status <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active_yes" value="active" {{ $data->status === 'active' ? 'checked' : '' }}>
                                <label class="form-check-label fs-15" for="active_yes">Active</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active_no" value="inactive"
                                    {{ $data->status === 'inactive' ? 'checked' : '' }}>
                                <label class="form-check-label fs-15" for="active_no">Inactive</label>
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="form-label">Marker File</label>
                            <input type="file" name="marker_file" class="form-control mb-2" style="max-width: 400px;">
                            <span class="ms-1 text-muted">
                                @if($data->marker_file && str_starts_with($data->marker_file, 'uploads'))
                                    Current custom uploaded file
                                @else
                                    No custom file chosen
                                @endif
                            </span>
                            <div class="text-muted fs-12 mb-3 mt-1" style="line-height: 1.2;">
                                Max photo size: ∞ KB, 80 × 80 px<br>
                                Allowed types: jpg, jpeg, png, gif
                            </div>

                            <p class="mb-2 mt-4 fw-bold text-dark">Or choose from the list:</p>
                            <div class="marker-icon-list">
                                @php
                                    $sampleIcons = [
                                        'office-building.png',
                                        'house.png',
                                        'townhouse.png',
                                        'apartment-3.png',
                                        'condominium.png',
                                        'reatorlogowhite-22x22.png',
                                    ];
                                @endphp
                                @foreach($sampleIcons as $icon)
                                    @php
                                        // Simple string comparison
                                        $isActiveMarker = ($data->marker_file === $icon);
                                    @endphp
                                    <div class="marker-item {{ $isActiveMarker ? 'active' : '' }}"
                                        onclick="selectMarker(this, '{{ $icon }}')">
                                        <div
                                            style="background-color: #4a7ce6; padding: 10px; border-radius: 5px; margin-bottom: 8px;">
                                            <img src="{{ asset('backend/images/marker/' . $icon) }}" alt="Icon">
                                        </div>
                                        <span class="custom-radio"></span>
                                        <input type="radio" name="existing_marker" value="{{ $icon }}" {{ $isActiveMarker ? 'checked' : '' }}>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">Update Details</button>
                            <a href="{{ route('property-type.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function selectMarker(el, url) {
            $('.marker-item').removeClass('active').find('input').prop('checked', false);
            $(el).addClass('active').find('input').prop('checked', true);
        }
    </script>
@endpush