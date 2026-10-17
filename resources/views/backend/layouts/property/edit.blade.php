@extends('backend.app')

@section('title', 'Edit Property')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .section-title {
        font-weight: bold;
        margin-bottom: 20px;
    }

    .amenity-card {
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 15px;
        background: #fafafa;
        margin-bottom: 20px;
    }

    /* Professional Selection Group */
    .select2-container--default .select2-selection--single {
        border: 1px solid #e9edf4 !important;
        height: 42px !important;
        border-radius: 6px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }

    /* Compact FullCalendar Styling */
    .calendar-professional-wrapper {
        width: 100%;
    }

    .fc-compact .fc-scrollgrid {
        border-color: #f0f0f2 !important;
    }

    .fc-compact .fc-col-header-cell {
        background-color: #f8f9fa !important;
        font-size: 10px !important;
        text-transform: uppercase;
        padding: 4px 0 !important;
    }

    .fc-compact .fc-col-header-cell-cushion {
        text-decoration: none !important;
        font-size: 10px !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
    }

    .fc-compact .fc-daygrid-day-number {
        font-size: 11px !important;
        color: #7987a1 !important;
        padding: 2px 6px !important;
        text-decoration: none !important;
    }

    .fc-compact .fc-daygrid-day-frame {
        min-height: 45px !important;
    }

    .fc-compact .fc-event {
        font-size: 9px !important;
        padding: 1px 3px !important;
        border-radius: 3px !important;
        border: 0 !important;
    }

    .fc-compact .fc-toolbar-title {
        font-size: 0.95rem !important;
        font-weight: 700 !important;
    }

    .fc-compact .fc-button {
        padding: 0.15rem 0.4rem !important;
        font-size: 0.7rem !important;
    }

    .fc-compact .fc-header-toolbar {
        margin-bottom: 0.5rem !important;
    }

    .fc-compact .fc-day-today {
        background-color: rgba(14, 165, 233, 0.04) !important;
    }

    /* Calendar Responsiveness */
    @media (max-width: 767.98px) {
        .fc-compact .fc-toolbar {
            flex-direction: column;
            gap: 8px;
            padding: 6px 0;
        }

        .fc-compact .fc-toolbar-title {
            font-size: 0.85rem !important;
        }

        .fc-compact .fc-daygrid-day-frame {
            min-height: 35px !important;
        }

        .fc-compact .fc-col-header-cell {
            font-size: 9px !important;
        }


        /* Form responsiveness */
        .card-body .row .form-group {
            margin-bottom: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title fs-2">Edit Property: {{ optional($data)->title }}</h1>
    </div>
    <div class="ms-auto pageheader-btn">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('property.index') }}">Properties</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Property</li>
        </ol>
    </div>
</div>

<form action="{{ route('property.update', $data?->property_id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card box-shadow-0 mb-5">
        <div class="card-header border-bottom">
            <h3 class="card-title">Property Details</h3>
        </div>
        <div class="card-body">
            <div class="mb-5">
                <h4 class="mb-4 border-bottom pb-2">Basics</h4>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label">Attached to User <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror"
                            required>
                            <option value="">Select User</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (old('user_id') ?? $data?->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="form-label">Property Status</label>
                        <select name="property_status"
                            class="form-control @error('property_status') is-invalid @enderror">
                            @foreach(['inactive', 'active', 'pending', 'sold', 'withdrawn', 'others'] as $status)
                            <option value="{{ $status }}" {{ (old('property_status') ?? $data?->property_status) == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                            @endforeach
                        </select>
                        @error('property_status') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="form-label">Contract Type</label>
                        <select name="property_main_type"
                            class="form-control @error('property_main_type') is-invalid @enderror">
                            <option value="rent" {{ (old('property_main_type') ?? $data?->property_main_type) == 'rent' ? 'selected' : '' }}>
                                Rent</option>
                            <option value="sale" {{ (old('property_main_type') ?? $data?->property_main_type) == 'sale' ? 'selected' : '' }}>
                                Sale</option>
                        </select>
                        @error('property_main_type') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">Property Type</label>
                        <select name="property_type_id"
                            class="form-control select2 @error('property_type_id') is-invalid @enderror">
                            <option value="">Select Type</option>
                            @foreach($propertyTypes as $type)
                            <option value="{{ $type->property_type_id }}" {{ (old('property_type_id') ?? $data?->property_type_id) == $type->property_type_id ? 'selected' : '' }}>
                                {{ $type->title }}
                            </option>
                            @endforeach
                        </select>
                        @error('property_type_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">Unit Type</label>
                        <select name="unit_type_id"
                            class="form-control select2 @error('unit_type_id') is-invalid @enderror">
                            <option value="">Select Unit</option>
                            @foreach($unitTypes as $ut)
                            <option value="{{ $ut->unit_type_id }}" {{ (old('unit_type_id') ?? $data?->unit_type_id) == $ut->unit_type_id ? 'selected' : '' }}>
                                {{ $ut->title }}
                            </option>
                            @endforeach
                        </select>
                        @error('unit_type_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">Property Category</label>
                        <select name="property_category_id"
                            class="form-control select2 @error('property_category_id') is-invalid @enderror">
                            <option value="">Select Category</option>
                            @foreach($propertyCategories as $cat)
                            <option value="{{ $cat->property_category_id }}" {{ (old('property_category_id') ?? $data?->property_category_id) == $cat->property_category_id ? 'selected' : '' }}>
                                {{ $cat->title }}
                            </option>
                            @endforeach
                        </select>
                        @error('property_category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12 form-group">
                        <label class="form-label">Property Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') ?? $data?->title }}" required>
                        @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12 form-group">
                        <label class="form-label">Full Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                            rows="8" required>{{ old('description') ?? $data?->description }}</textarea>
                        @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- PROPERTY DETAILS -->
            <div class="mb-5 mt-5">
                <h4 class="mb-4 border-bottom pb-2">Property Details</h4>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="form-label">Currency</label>
                        <select name="currency_id" class="form-control @error('currency_id') is-invalid @enderror">
                            <option value="">Select Currency</option>
                            @foreach($currencies as $curr)
<<<<<<< HEAD
                                <option value="{{ $curr->currency_id }}" @selected($data->currency_id == $curr->currency_id)>
                                    {{ $curr->title }} ({{ $curr->currency_code }})
                                </option>
=======
                            <option value="{{ $curr->currency_id }}" {{ $data->currency_id == $curr->currency_id ? 'selected' : '' }}>{{ $curr->title }} ({{ $curr->currency_code }})</option>
>>>>>>> imtiaz
                            @endforeach
                        </select>
                        @error('currency_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">Stars <span class="text-danger">*</span></label>
                    <select name="stars" class="form-control @error('stars') is-invalid @enderror" required>
                        @foreach(['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'] as $star)
                        <option value="{{ $star }}" {{ (old('stars') ?? $data->stars) == $star ? 'selected' : '' }}>
                            {{ $star }}
                        </option>
                        @endforeach
                    </select>
                    @error('stars') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">Available From <span class="text-danger">*</span></label>
                    <input type="date" name="available_from"
                        class="form-control @error('available_from') is-invalid @enderror"
                        value="{{ old('available_from') ?? $data->available_from }}" required>
                    @error('available_from') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">Total Square Feet (Sq. Meters) <span
                            class="text-danger">*</span></label>
                    <input type="number" name="total_square_feet"
                        class="form-control @error('total_square_feet') is-invalid @enderror" step="0.01"
                        value="{{ old('total_square_feet') ?? $data->total_square_feet }}" required>
                    @error('total_square_feet') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">No. Bedrooms <span class="text-danger">*</span></label>
                    <input type="number" name="no_bedrooms"
                        class="form-control @error('no_bedrooms') is-invalid @enderror"
                        value="{{ old('no_bedrooms') ?? $data->no_bedrooms }}" required>
                    @error('no_bedrooms') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">No. Bathrooms <span class="text-danger">*</span></label>
                    <input type="number" name="no_bathrooms"
                        class="form-control @error('no_bathrooms') is-invalid @enderror"
                        value="{{ old('no_bathrooms') ?? $data->no_bathrooms }}" required>
                    @error('no_bathrooms') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">No. Floors <span class="text-danger">*</span></label>
                    <input type="number" name="no_floors"
                        class="form-control @error('no_floors') is-invalid @enderror"
                        value="{{ old('no_floors') ?? $data->no_floors }}" required>
                    @error('no_floors') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">No. Guests <span class="text-danger">*</span></label>
                    <input type="number" name="no_guests"
                        class="form-control @error('no_guests') is-invalid @enderror"
                        value="{{ old('no_guests') ?? $data->no_guests }}" required>
                    @error('no_guests') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">Min Stay (Nights) <span class="text-danger">*</span></label>
                    <input type="number" name="min_no_of_stay"
                        class="form-control @error('min_no_of_stay') is-invalid @enderror"
                        value="{{ old('min_no_of_stay') ?? $data->min_no_of_stay }}" required>
                    @error('min_no_of_stay') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4 form-group">
                    <label class="form-label">Visibility Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ (old('status') ?? $data->status) === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ (old('status') ?? $data->status) === 'inactive' ? 'selected' : '' }}>
                            Inactive</option>
                    </select>
                    @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3 mt-4">
                    <label class="form-label d-block">Parking Available</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="parking" id="parkingYes" value="1" {{ $data->parking ? 'checked' : '' }}>
                        <label class="form-check-label" for="parkingYes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="parking" id="parkingNo" value="0" {{ !$data->parking ? 'checked' : '' }}>
                        <label class="form-check-label" for="parkingNo">No</label>
                    </div>
                </div>
                <div class="col-md-3 mt-4">
                    <label class="form-label d-block">Car Essential</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="car_essential" id="carEssentialYes" value="1" {{ $data->car_essential ? 'checked' : '' }}>
                        <label class="form-check-label" for="carEssentialYes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="car_essential" id="carEssentialNo" value="0" {{ !$data->car_essential ? 'checked' : '' }}>
                        <label class="form-check-label" for="carEssentialNo">No</label>
                    </div>
                </div>
                <div class="col-md-4 mt-4">
                    <label class="form-label d-block">Allow Direct Bookings</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="allow_bookings" id="allowBookingsYes" value="1" {{ $data->allow_bookings ? 'checked' : '' }}>
                        <label class="form-check-label" for="allowBookingsYes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="allow_bookings" id="allowBookingsNo" value="0" {{ !$data->allow_bookings ? 'checked' : '' }}>
                        <label class="form-check-label" for="allowBookingsNo">No</label>
                    </div>
                </div>
            </div>

            <!-- LOCATION -->
            <div class="mb-5 mt-5">
                <h4 class="mb-4 border-bottom pb-2">Choose Property Location</h4>
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <label class="form-label">Search location :</label>
                        <div class="input-group mb-3">
                            <input type="text" id="map-search" class="form-control"
                                   placeholder="Search for a building, street, or city to pinpoint on map...">
                            <button class="btn btn-primary" type="button" id="search-btn">Search</button>
                        </div>
                        <div id="map" style="height: 800px; width: 100%; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                    </div>

<<<<<<< HEAD
                    <div class="col-md-4 form-group">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <input type="text" name="country" id="country"
                               class="form-control @error('country') is-invalid @enderror"
                               placeholder="Type to search country..."
                               value="{{ old('country') ?? ($data->location?->country ?? '') }}" autocomplete="off">
                        @error('country') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">State / Region</label>
                        <input type="text" name="region" id="region"
                               class="form-control @error('region') is-invalid @enderror"
                               placeholder="Type to search region..."
                               value="{{ old('region') ?? ($data->location?->region ?? '') }}" autocomplete="off">
                        @error('region') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" id="city"
                               class="form-control @error('city') is-invalid @enderror"
                               placeholder="Type to search city..."
                               value="{{ old('city') ?? ($data->location?->city ?? '') }}" autocomplete="off">
                        @error('city') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">Area / District</label>
                        <input type="text" name="area" id="area"
                               class="form-control @error('area') is-invalid @enderror"
                               value="{{ old('area') ?? ($data->location?->area ?? '') }}"
                               placeholder="Type to search area..." autocomplete="off">
                        @error('area') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">Landmark</label>
                        <input type="text" name="landmark" id="landmark"
                               class="form-control @error('landmark') is-invalid @enderror"
                               value="{{ old('landmark') ?? ($data->location?->landmark ?? '') }}"
                               placeholder="e.g. Near Central Park">
                        @error('landmark') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">Zip / Postal Code</label>
                        <input type="text" name="zip_code" id="zip_code"
                               class="form-control @error('zip_code') is-invalid @enderror"
                               value="{{ old('zip_code') ?? ($data->location?->zip_code ?? '') }}"
                               placeholder="e.g. 10001">
                        @error('zip_code') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-12 form-group">
                        <label class="form-label">Full Address</label>
                        <textarea name="address" id="address"
                                  class="form-control @error('address') is-invalid @enderror"
                                  rows="2" placeholder="Full street address (auto-filled from map)">{{ old('address') ?? ($data->location?->address ?? '') }}</textarea>
                        @error('address') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    {{-- Hidden country_code field (auto-filled by JS) --}}
                    <input type="hidden" name="country_code" id="country_code" value="{{ old('country_code') ?? ($data->location?->country_code ?? '') }}">

                    <div class="col-12 mb-2">
                        <a class="small text-muted" data-bs-toggle="collapse" href="#coordsCollapse" role="button" aria-expanded="false">
                            <i class="fe fe-chevron-down me-1"></i>Show Latitude / Longitude / Zoom
                        </a>
                    </div>
                    <div class="collapse" id="coordsCollapse">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="form-label text-muted small">Latitude</label>
                                <input type="text" name="lat" id="lat" class="form-control form-control-sm bg-light"
                                       value="{{ old('lat') ?? ($data->location?->lat ?? '') }}" readonly>
=======
        <!-- AMENITIES -->
        <div class="mb-5 mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="mb-0">Amenities</h4>
                <button type="button" class="btn btn-sm btn-success" id="add-amenity">
                    <i class="fe fe-plus"></i> Add Amenity
                </button>
            </div>
            <div id="amenities-container">
                @foreach($data->amenities as $index => $amenity)
                <div class="amenity-row card mb-3 border p-3">
                    <div class="row">
                        <div class="col-md-2 form-group">
                            <label class="form-label text-muted small">Icon Type</label>
                            <select name="amenities[{{ $index }}][icon_type]"
                                class="form-control form-control-sm icon-type-select-amenity">
                                <option value="image" {{ $amenity->icon_type === 'image' ? 'selected' : '' }}>
                                    Image File</option>
                                <option value="code" {{ $amenity->icon_type === 'code' ? 'selected' : '' }}>
                                    SVG/Code</option>
                            </select>
                        </div>
                        <div
                            class="col-md-4 form-group icon-file-group-amenity {{ $amenity->icon_type === 'code' ? 'd-none' : '' }}">
                            <label class="form-label text-muted small">Icon Image</label>
                            @if($amenity->icon_type === 'image' && $amenity->icon)
                            <div class="mb-1">
                                <img src="{{ \App\Helpers\Helper::imageUrl($amenity->icon) }}" style="height: 30px; width: auto;"
                                    class="img-thumbnail">
                                <input type="hidden" name="amenities[{{ $index }}][existing_icon]"
                                    value="{{ $amenity->icon }}">
>>>>>>> imtiaz
                            </div>
<<<<<<< HEAD
                            <div class="col-md-4 form-group">
                                <label class="form-label text-muted small">Longitude</label>
                                <input type="text" name="lng" id="lng" class="form-control form-control-sm bg-light"
                                       value="{{ old('lng') ?? ($data->location?->lng ?? '') }}" readonly>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label text-muted small">Zoom Level</label>
                                <input type="number" name="zoom" id="zoom" class="form-control form-control-sm bg-light"
                                       value="{{ old('zoom') ?? ($data->location?->zoom ?? 12) }}" readonly>
                            </div>
=======
                            @endif
                            <input type="file" name="amenities[{{ $index }}][icon_file]"
                                class="form-control form-control-sm">
                        </div>
                        <div
                            class="col-md-4 form-group icon-code-group-amenity {{ $amenity->icon_type === 'image' ? 'd-none' : '' }}">
                            <label class="form-label text-muted small">Icon Code (SVG/HTML)</label>
                            <textarea name="amenities[{{ $index }}][icon_code]" class="form-control form-control-sm"
                                rows="1" {{ $amenity->icon_type === 'code' ? 'required' : '' }}>{{ $amenity->icon_type === 'code' ? $amenity->icon : '' }}</textarea>
                        </div>
                        <div class="col-md-5 form-group">
                            <label class="form-label text-muted small">Title</label>
                            <input type="text" name="amenities[{{ $index }}][title]"
                                class="form-control form-control-sm" value="{{ $amenity->title }}">
                        </div>
                        <div class="col-md-1 d-flex align-items-end justify-content-center pb-3">
                            <button type="button" class="btn btn-sm btn-danger remove-amenity">
                                <i class="fe fe-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>


        <!-- FACILITIES -->
        <div class="mb-5 mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="mb-0">Property Facilities</h4>
                <button type="button" class="btn btn-sm btn-success" id="add-facility">
                    <i class="fe fe-plus"></i> Add Facility
                </button>
            </div>
            <div id="facilities-container">
                @foreach($data->facilities as $index => $facility)
                <div class="facility-row card mb-3 border p-3">
                    <div class="row">
                        <div class="col-md-2 form-group">
                            <label class="form-label text-muted small">Icon Type</label>
                            <select name="facilities[{{ $index }}][icon_type]"
                                class="form-control form-control-sm icon-type-select">
                                <option value="image" {{ $facility->icon_type === 'image' ? 'selected' : '' }}>Image
                                    File</option>
                                <option value="code" {{ $facility->icon_type === 'code' ? 'selected' : '' }}>
                                    SVG/Code</option>
                            </select>
                        </div>
                        <div
                            class="col-md-3 form-group icon-file-group {{ $facility->icon_type === 'code' ? 'd-none' : '' }}">
                            <label class="form-label text-muted small">Icon Image</label>
                            @if($facility->icon_type === 'image' && $facility->icon)
                            <div class="mb-1">
                                <img src="{{ \App\Helpers\Helper::imageUrl($facility->icon) }}" style="height: 30px; width: auto;"
                                    class="img-thumbnail">
                                <input type="hidden" name="facilities[{{ $index }}][existing_icon]"
                                    value="{{ $facility->icon }}">
                            </div>
                            @endif
                            <input type="file" name="facilities[{{ $index }}][icon_file]"
                                class="form-control form-control-sm">
                        </div>
                        <div
                            class="col-md-3 form-group icon-code-group {{ $facility->icon_type === 'image' ? 'd-none' : '' }}">
                            <label class="form-label text-muted small">Icon Code (SVG/HTML)</label>
                            <textarea name="facilities[{{ $index }}][icon_code]"
                                class="form-control form-control-sm" rows="1" {{ $facility->icon_type === 'code' ? 'required' : '' }}>{{ $facility->icon_type === 'code' ? $facility->icon : '' }}</textarea>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="form-label text-muted small">Title</label>
                            <input type="text" name="facilities[{{ $index }}][title]"
                                class="form-control form-control-sm" value="{{ $facility->title }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="form-label text-muted small">Short Desc</label>
                            <input type="text" name="facilities[{{ $index }}][description]"
                                class="form-control form-control-sm" value="{{ $facility->description }}">
                        </div>
                        <div class="col-md-1 d-flex align-items-end justify-content-center pb-3">
                            <button type="button" class="btn btn-sm btn-danger remove-facility">
                                <i class="fe fe-trash"></i>
                            </button>
>>>>>>> imtiaz
                        </div>
                    </div>
                </div>
            </div>

            <!-- AMENITIES -->
            <div class="mb-5 mt-5">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                    <h4 class="mb-0">Amenities</h4>
                    <button type="button" class="btn btn-sm btn-success" id="add-amenity">
                        <i class="fe fe-plus"></i> Add Amenity
                    </button>
                </div>
                <div id="amenities-container">
                    @foreach($data->amenities as $index => $amenity)
                        <div class="amenity-row card mb-3 border p-3">
                            <div class="row">
                                <div class="col-md-2 form-group">
                                    <label class="form-label text-muted small">Icon Type</label>
                                    <select name="amenities[{{ $index }}][icon_type]"
                                            class="form-control form-control-sm icon-type-select-amenity">
                                        <option value="image" {{ $amenity->icon_type === 'image' ? 'selected' : '' }}>
                                            Image File</option>
                                        <option value="code" {{ $amenity->icon_type === 'code' ? 'selected' : '' }}>
                                            SVG/Code</option>
                                    </select>
                                </div>
                                <div
                                    class="col-md-4 form-group icon-file-group-amenity {{ $amenity->icon_type === 'code' ? 'd-none' : '' }}">
                                    <label class="form-label text-muted small">Icon Image</label>
                                    @if($amenity->icon_type === 'image' && $amenity->icon)
                                        <div class="mb-1">
                                            <img src="{{ asset($amenity->icon) }}" style="height: 30px; width: auto;"
                                                 class="img-thumbnail">
                                            <input type="hidden" name="amenities[{{ $index }}][existing_icon]"
                                                   value="{{ $amenity->icon }}">
                                        </div>
                                    @endif
                                    <input type="file" name="amenities[{{ $index }}][icon_file]"
                                           class="form-control form-control-sm" {{ ($amenity->icon_type === 'image' && !$amenity->icon) ? 'required' : '' }}>
                                </div>
                                <div
                                    class="col-md-4 form-group icon-code-group-amenity {{ $amenity->icon_type === 'image' ? 'd-none' : '' }}">
                                    <label class="form-label text-muted small">Icon Code (SVG/HTML)</label>
                                    <textarea name="amenities[{{ $index }}][icon_code]" class="form-control form-control-sm"
                                              rows="1" {{ $amenity->icon_type === 'code' ? 'required' : '' }}>{{ $amenity->icon_type === 'code' ? $amenity->icon : '' }}</textarea>
                                </div>
                                <div class="col-md-5 form-group">
                                    <label class="form-label text-muted small">Title</label>
                                    <input type="text" name="amenities[{{ $index }}][title]"
                                           class="form-control form-control-sm" value="{{ $amenity->title }}">
                                </div>
                                <div class="col-md-1 d-flex align-items-end justify-content-center pb-3">
                                    <button type="button" class="btn btn-sm btn-danger remove-amenity">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>


            <!-- FACILITIES -->
            <div class="mb-5 mt-5">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                    <h4 class="mb-0">Property Facilities</h4>
                    <button type="button" class="btn btn-sm btn-success" id="add-facility">
                        <i class="fe fe-plus"></i> Add Facility
                    </button>
                </div>
                <div id="facilities-container">
                    @foreach($data->facilities as $index => $facility)
                        <div class="facility-row card mb-3 border p-3">
                            <div class="row">
                                <div class="col-md-2 form-group">
                                    <label class="form-label text-muted small">Icon Type</label>
                                    <select name="facilities[{{ $index }}][icon_type]"
                                            class="form-control form-control-sm icon-type-select">
                                        <option value="image" {{ $facility->icon_type === 'image' ? 'selected' : '' }}>Image
                                            File</option>
                                        <option value="code" {{ $facility->icon_type === 'code' ? 'selected' : '' }}>
                                            SVG/Code</option>
                                    </select>
                                </div>
                                <div
                                    class="col-md-3 form-group icon-file-group {{ $facility->icon_type === 'code' ? 'd-none' : '' }}">
                                    <label class="form-label text-muted small">Icon Image</label>
                                    @if($facility->icon_type === 'image' && $facility->icon)
                                        <div class="mb-1">
                                            <img src="{{ asset($facility->icon) }}" style="height: 30px; width: auto;"
                                                 class="img-thumbnail">
                                            <input type="hidden" name="facilities[{{ $index }}][existing_icon]"
                                                   value="{{ $facility->icon }}">
                                        </div>
                                    @endif
                                    <input type="file" name="facilities[{{ $index }}][icon_file]"
                                           class="form-control form-control-sm" {{ ($facility->icon_type === 'image' && !$facility->icon) ? 'required' : '' }}>
                                </div>
                                <div
                                    class="col-md-3 form-group icon-code-group {{ $facility->icon_type === 'image' ? 'd-none' : '' }}">
                                    <label class="form-label text-muted small">Icon Code (SVG/HTML)</label>
                                    <textarea name="facilities[{{ $index }}][icon_code]"
                                              class="form-control form-control-sm" rows="1" {{ $facility->icon_type === 'code' ? 'required' : '' }}>{{ $facility->icon_type === 'code' ? $facility->icon : '' }}</textarea>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label class="form-label text-muted small">Title</label>
                                    <input type="text" name="facilities[{{ $index }}][title]"
                                           class="form-control form-control-sm" value="{{ $facility->title }}">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label class="form-label text-muted small">Short Desc</label>
                                    <input type="text" name="facilities[{{ $index }}][description]"
                                           class="form-control form-control-sm" value="{{ $facility->description }}">
                                </div>
                                <div class="col-md-1 d-flex align-items-end justify-content-center pb-3">
                                    <button type="button" class="btn btn-sm btn-danger remove-facility">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- SEO / META -->
            <div class="mb-5 mt-5">
                <h4 class="mb-4 border-bottom pb-2">SEO / Meta Details</h4>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="property_meta_title" class="form-control"
                               value="{{ $data?->property_meta_title }}">
                    </div>
                    <div class="col-md-12 form-group">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="property_meta_keywords" class="form-control"
                               value="{{ $data?->property_meta_keywords }}">
                    </div>
                    <div class="col-md-12 form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="property_meta_description" class="form-control"
                                  rows="3">{{ $data?->property_meta_description }}</textarea>
                    </div>
                </div>
            </div>

        </div>

    </div> <!-- End of Property Details Card -->

    <!-- PAYMENT MANAGEMENT CARD -->
    <div class="card box-shadow-0 mb-5">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h3 class="card-title">Payment Management</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info border-0 py-2 mb-4">
                <i class="fe fe-info me-2"></i> Manage Rates, Discounts, and Addons directly connected to this property.
            </div>

            <div class="row">
                <div class="col-md-12">
                    <!-- Rates Section -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fs-16 fw-semibold">Property Rates</h5>
                        <a href="{{ route('rate.create') }}" class="btn btn-sm btn-primary-light">
                            <i class="fe fe-plus me-1"></i>Add Rate
                        </a>
                    </div>

                    <div class="table-responsive border  mb-5">
                        <table class="table table-vcenter text-nowrap table-bordered border-bottom mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-bottom-0">Dates</th>
                                    <th class="border-bottom-0">Nightly Rate</th>
                                    <th class="border-bottom-0">Min Stay</th>
                                    <th class="border-bottom-0">Status</th>
                                    <th class="border-bottom-0 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($data?->rates ?? []) as $rate)
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $rate->start_date ? $rate->start_date->format('M d, Y') : 'N/A' }} -
                                        {{ $rate->end_date ? $rate->end_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td>€{{ number_format($rate->nightly_rate, 2) }}
                                    </td>
                                    <td>{{ $rate->min_stay }} nts</td>
                                    <td>
                                        @if($rate->status)
                                        <span
                                            class="badge bg-success-transparent text-success rounded-pill">Active</span>
                                        @else
                                        <span
                                            class="badge bg-danger-transparent text-danger rounded-pill">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('rate.edit', $rate->rate_id) }}"
                                            class="btn btn-sm btn-icon btn-info-light text-info"
                                            data-bs-toggle="tooltip" title="Edit Rate">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No rates configured for this
                                        property.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Addon, Discount Grid -->
                    <div class="pt-4 border-top">
                        <h5 class="mb-4 fs-16 fw-semibold">Addons & Discounts</h5>
                        <div class="row g-4">
                            <div class="col-lg-6 col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label text-muted small text-uppercase">Select Addon</label>
                                    <select name="addon_id" class="form-control select2">
                                        <option value="">No Addon</option>
                                        @foreach($addons as $addon)
                                        <option value="{{ $addon->addon_id }}" {{ $data->addon_id == $addon->addon_id ? 'selected' : '' }}>
                                            {{ $addon->addon_type }} ({{ $addon->price }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label text-muted small text-uppercase">Select Discount</label>
                                    <select name="discount_id" class="form-control select2">
                                        <option value="">No Discount</option>
                                        @foreach($discounts as $discount)
                                        <option value="{{ $discount->discount_id }}" {{ $data->discount_id == $discount->discount_id ? 'selected' : '' }}>
                                            {{ $discount->discount_type }} ({{ $discount->discount_percentage }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AVAILABILITY CALENDAR CARD -->
    <div class="card box-shadow-0 mb-5">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Availability Calendar</h3>
            <span class="badge bg-info-transparent text-info">Preview Only</span>
        </div>
        <div class="card-body p-2 p-md-3">
            <div class="calendar-professional-wrapper border bg-white overflow-hidden">
                <div id="availability-calendar" class="fc-compact"></div>
            </div>
        </div>
    </div>

    <!-- MEDIA & POLICIES CARD -->
    <div class="card box-shadow-0 mb-5">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Media & Policies</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- PROPERTY PHOTOS -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fs-16 fw-semibold mb-0">Property Photos</h5>
                    @if($data->photos->count() > 0)
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" id="select-all-photos" class="btn btn-sm btn-outline-danger">
                            <i class="fe fe-check-square me-1"></i> Select All
                        </button>
                        <button type="button" id="deselect-all-photos" class="btn btn-sm btn-outline-secondary">
                            <i class="fe fe-square me-1"></i> Deselect All
                        </button>
                        <button type="button" id="delete-selected-photos" class="btn btn-sm btn-danger d-none">
                            🗑 Delete Selected
                        </button>
                    </div>
                    @endif
                </div>
                @if($data->photos->count() > 0)
                <div class="row g-3 mb-3" id="photos-grid">
                    @foreach($data->photos->sortBy('hierarchy') as $photo)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                        <div class="card border mb-0 overflow-hidden photo-card">
                            <img src="{{ $photo->property_photo_file }}" class="card-img-top"
                                style="height: 120px; object-fit: cover; cursor: pointer;" alt="Property Photo"
                                onclick="togglePhotoDelete(this)">
                            <div class="card-body p-2 text-center">
                                <div class="form-check d-inline-flex align-items-center gap-1">
                                    <input class="form-check-input photo-delete-cb" type="checkbox" name="delete_photos[]"
                                        value="{{ $photo->property_photo_id }}"
                                        id="del-photo-{{ $photo->property_photo_id }}">
                                    <label class="form-check-label text-danger small"
                                        for="del-photo-{{ $photo->property_photo_id }}">
                                        <i class="fe fe-trash"></i> Delete
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="alert alert-light border py-2 mb-3">
                    <i class="fe fe-image me-2 text-muted"></i><span class="text-muted">No photos uploaded yet.</span>
                </div>
                @endif
                <div class="row">
                    <div class="col-lg-6">
                        <label class="form-label fw-semibold small text-uppercase text-muted">Upload New Photos</label>
                        <input type="file" name="photos[]" class="form-control form-control-sm" multiple
                            accept="image/*">
                        <small class="text-muted mt-1 d-block">Select multiple images to add more photos.</small>
                    </div>
                </div>
            </div>

            <!-- PROPERTY VIDEOS -->
            <div class="mb-5 pt-4 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fs-16 fw-semibold mb-0">Property Videos</h5>
                    <button type="button" class="btn btn-sm btn-primary-light" id="add-video">
                        <i class="fe fe-plus me-1"></i> Add Video
                    </button>
                </div>
                <div id="videos-container">
                    @foreach($data->videos as $index => $video)
                    <div class="video-row border p-3 mb-3 bg-light">
                        <div class="row g-2 align-items-end">
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label text-muted small">Video Type</label>
                                <select name="videos[{{ $index }}][video_type]"
                                    class="form-control form-control-sm video-type-select">
                                    <option value="embed" {{ $video->video_type == 'embed' ? 'selected' : '' }}>Embed
                                    </option>
                                    <option value="upload" {{ $video->video_type == 'upload' ? 'selected' : '' }}>Upload
                                    </option>
                                </select>
                            </div>
                            <div
                                class="col-lg-5 col-md-8 video-embed-group {{ $video->video_type == 'upload' ? 'd-none' : '' }}">
                                <label class="form-label text-muted small">Embed URL</label>
                                <input type="text" name="videos[{{ $index }}][embed]"
                                    class="form-control form-control-sm" value="{{ $video->embed }}"
                                    placeholder="https://youtube.com/watch?v=...">
                            </div>
                            <div
                                class="col-lg-3 col-md-6 video-upload-group {{ $video->video_type == 'embed' ? 'd-none' : '' }}">
                                <label class="form-label text-muted small">Video File
                                    {{ $video->video_file ? '✓' : '' }}</label>
                                <input type="file" name="videos[{{ $index }}][video_file]"
                                    class="form-control form-control-sm" accept="video/*">
                                @if($video->video_file)
                                <input type="hidden" name="videos[{{ $index }}][existing_video_file]"
                                    value="{{ $video->video_file }}">
                                @endif
                            </div>
                            <div
                                class="col-lg-3 col-md-6 video-poster-group {{ $video->video_type == 'embed' ? 'd-none' : '' }}">
                                <label class="form-label text-muted small">Poster
                                    {{ $video->poster_file ? '✓' : '' }}</label>
                                <input type="file" name="videos[{{ $index }}][poster_file]"
                                    class="form-control form-control-sm" accept="image/*">
                                @if($video->poster_file)
                                <input type="hidden" name="videos[{{ $index }}][existing_poster_file]"
                                    value="{{ $video->poster_file }}">
                                @endif
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-video"><i
                                        class="fe fe-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>


            <!-- PROPERTY POLICIES -->
            <div class="pt-4 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fs-16 fw-semibold mb-0">Property Policies</h5>
                    <button type="button" class="btn btn-sm btn-primary-light" id="add-policy">
                        <i class="fe fe-plus me-1"></i> Add Policy
                    </button>
                </div>
                <div id="policies-container">
                    @foreach($data->policies as $index => $policy)
                    <div class="policy-row border p-3 mb-3 bg-light">
                        <div class="row g-2 align-items-end">
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label text-muted small">Icon Type</label>
                                <input type="hidden" name="policies_data[{{ $index }}][existing_icon]"
                                    value="{{ $policy->icon }}">
                                <select name="policies_data[{{ $index }}][icon_type]"
                                    class="form-control form-control-sm policy-icon-type">
                                    <option value="image" {{ $policy->icon_type == 'image' ? 'selected' : '' }}>Image
                                    </option>
                                    <option value="code" {{ $policy->icon_type == 'code' ? 'selected' : '' }}>SVG/HTML
                                    </option>
                                </select>
                            </div>
                            <div
                                class="col-lg-3 col-md-4 policy-icon-image-group {{ $policy->icon_type != 'image' ? 'd-none' : '' }}">
                                <label class="form-label text-muted small">Icon Image</label>
                                <input type="file" name="policies_data[{{ $index }}][icon_file]"
                                    class="form-control form-control-sm" accept="image/*">
                                @if($policy->icon_type == 'image' && $policy->icon)
                                <div class="mt-1"><img src="{{ \App\Helpers\Helper::imageUrl($policy->icon) }}" alt="Icon"
                                        style="height: 24px;"></div>
                                @endif
                            </div>
                            <div
                                class="col-lg-3 col-md-4 policy-icon-code-group {{ $policy->icon_type != 'code' ? 'd-none' : '' }}">
                                <label class="form-label text-muted small">Icon Code</label>
                                <textarea name="policies_data[{{ $index }}][icon_code]"
                                    class="form-control form-control-sm"
                                    rows="1">@if($policy->icon_type == 'code'){{ $policy->icon }}@endif</textarea>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label text-muted small">Title</label>
                                <input type="text" name="policies_data[{{ $index }}][title]"
                                    class="form-control form-control-sm" value="{{ $policy->title }}">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label text-muted small">Description</label>
                                <textarea name="policies_data[{{ $index }}][description]"
                                    class="form-control form-control-sm" rows="1">{{ $policy->description }}</textarea>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-policy"><i
                                        class="fe fe-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div> <!-- End of card-body -->
    </div> <!-- End of Media & Policies Card -->

    <!-- FOOTER ACTIONS -->
    <div class="card box-shadow-0 mb-5">
        <div class="card-body d-flex justify-content-end gap-2 py-3">
            <a href="{{ route('property.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fe fe-x me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary px-5">
                <i class="fe fe-save me-1"></i> Update Property
            </button>
        </div>
    </div>
</form>

{{-- Delete Photos Confirmation Modal --}}
<div class="modal fade" id="confirmDeletePhotosModal" tabindex="-1" aria-labelledby="confirmDeletePhotosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeletePhotosLabel">
                    <i class="fe fe-trash me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to permanently delete <strong><span id="photoDeleteCount">0</span> photo(s)</strong>? This action <strong>cannot be undone</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fe fe-x me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeletePhotosBtn">
                    <i class="fe fe-trash me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- FullCalendar Integration -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        // Facilities logic
        let facilityIndex = {{ $data->facilities->count() }};

        $('#add-facility').click(function() {
            const html = `
                                                                                                                                    <div class="facility-row card mb-3 border p-3">
                                                                                                                                        <div class="row">
                                                                                                                                            <div class="col-md-2 form-group">
                                                                                                                                                <label class="form-label text-muted small">Icon Type</label>
                                                                                                                                                <select name="facilities[${facilityIndex}][icon_type]" class="form-control form-control-sm icon-type-select">
                                                                                                                                                    <option value="image">Image File</option>
                                                                                                                                                    <option value="code">SVG/Code</option>
                                                                                                                                                </select>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group icon-file-group">
                                                                                                                                                <label class="form-label text-muted small">Icon Image</label>
                                                                                                                                                <input type="file" name="facilities[${facilityIndex}][icon_file]" class="form-control form-control-sm" required>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group icon-code-group d-none">
                                                                                                                                                <label class="form-label text-muted small">Icon Code (SVG/HTML)</label>
                                                                                                                                                <textarea name="facilities[${facilityIndex}][icon_code]" class="form-control form-control-sm" rows="1"></textarea>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group">
                                                                                                                                                <label class="form-label text-muted small">Title</label>
                                                                                                                                                <input type="text" name="facilities[${facilityIndex}][title]" class="form-control form-control-sm" placeholder="e.g. Bedrooms">
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group">
                                                                                                                                                <label class="form-label text-muted small">Short Desc</label>
                                                                                                                                                <input type="text" name="facilities[${facilityIndex}][description]" class="form-control form-control-sm" placeholder="e.g. 5 Rooms">
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-1 d-flex align-items-end justify-content-center pb-3">
                                                                                                                                                <button type="button" class="btn btn-sm btn-danger remove-facility">
                                                                                                                                                    <i class="fe fe-trash"></i>
                                                                                                                                                </button>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `;
            $('#facilities-container').append(html);
            facilityIndex++;
        });

        $('#facilities-container').on('change', '.icon-type-select', function() {
            const row = $(this).closest('.facility-row');
            if ($(this).val() === 'code') {
                row.find('.icon-file-group').addClass('d-none').find('input').removeAttr('required');
                row.find('.icon-code-group').removeClass('d-none').find('textarea').attr('required', 'required');
            } else {
                row.find('.icon-file-group').removeClass('d-none');
                if (row.find('input[name*="[existing_icon]"]').length === 0) {
                    row.find('.icon-file-group').find('input[type="file"]').attr('required', 'required');
                }
                row.find('.icon-code-group').addClass('d-none').find('textarea').removeAttr('required');
            }
        });

        $('#facilities-container').on('click', '.remove-facility', function() {
            $(this).closest('.facility-row').remove();
        });

        // Amenities logic
        let amenityIndex = {{ $data->amenities->count() }};
        $('#add-amenity').click(function() {
            const html = `
                                                                                                                                    <div class="amenity-row card mb-3 border p-3">
                                                                                                                                        <div class="row">
                                                                                                                                            <div class="col-md-2 form-group">
                                                                                                                                                <label class="form-label text-muted small">Icon Type</label>
                                                                                                                                                <select name="amenities[${amenityIndex}][icon_type]" class="form-control form-control-sm icon-type-select-amenity">
                                                                                                                                                    <option value="image">Image File</option>
                                                                                                                                                    <option value="code">SVG/Code</option>
                                                                                                                                                </select>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-4 form-group icon-file-group-amenity">
                                                                                                                                                <label class="form-label text-muted small">Icon Image</label>
                                                                                                                                                <input type="file" name="amenities[${amenityIndex}][icon_file]" class="form-control form-control-sm" required>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-4 form-group icon-code-group-amenity d-none">
                                                                                                                                                <label class="form-label text-muted small">Icon Code (SVG/HTML)</label>
                                                                                                                                                <textarea name="amenities[${amenityIndex}][icon_code]" class="form-control form-control-sm" rows="1"></textarea>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-5 form-group">
                                                                                                                                                <label class="form-label text-muted small">Title</label>
                                                                                                                                                <input type="text" name="amenities[${amenityIndex}][title]" class="form-control form-control-sm" placeholder="e.g. Free WiFi">
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-1 d-flex align-items-end justify-content-center pb-3">
                                                                                                                                                <button type="button" class="btn btn-sm btn-danger remove-amenity">
                                                                                                                                                    <i class="fe fe-trash"></i>
                                                                                                                                                </button>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `;
            $('#amenities-container').append(html);
            amenityIndex++;
        });

        $('#amenities-container').on('change', '.icon-type-select-amenity', function() {
            const row = $(this).closest('.amenity-row');
            if ($(this).val() === 'code') {
                row.find('.icon-file-group-amenity').addClass('d-none').find('input').removeAttr('required');
                row.find('.icon-code-group-amenity').removeClass('d-none').find('textarea').attr('required', 'required');
            } else {
                row.find('.icon-file-group-amenity').removeClass('d-none');
                if (row.find('input[name*="[existing_icon]"]').length === 0) {
                    row.find('.icon-file-group-amenity').find('input[type="file"]').attr('required', 'required');
                }
                row.find('.icon-code-group-amenity').addClass('d-none').find('textarea').removeAttr('required');
            }
        });

        $('#amenities-container').on('click', '.remove-amenity', function() {
            $(this).closest('.amenity-row').remove();
        });

        // =============================================
        // LOCATION: Google Maps API Only
        // Cascading: Country → Region → City → Area
        // =============================================
        let map, marker, geocoder;
        let countryAutocomplete, regionAutocomplete, cityAutocomplete, areaAutocomplete;
        let currentCountryCode = '';
        let currentRegionBounds = null;
        let currentCityBounds = null;

        // ─── Apply country restriction to Region, City, Area autocomplete ───
        function applyCountryRestriction(countryCode) {
            currentCountryCode = countryCode || '';
            // Persist ISO country_code to hidden input for form submission
            $('#country_code').val(countryCode || '');
            if (countryCode) {
                const restriction = {
                    country: countryCode
                };
                if (regionAutocomplete) regionAutocomplete.setComponentRestrictions(restriction);
                if (cityAutocomplete) cityAutocomplete.setComponentRestrictions(restriction);
                if (areaAutocomplete) areaAutocomplete.setComponentRestrictions(restriction);
            } else {
                if (regionAutocomplete) regionAutocomplete.setComponentRestrictions({});
                if (cityAutocomplete) cityAutocomplete.setComponentRestrictions({});
                if (areaAutocomplete) areaAutocomplete.setComponentRestrictions({});
            }
        }

        // ─── Bias city autocomplete toward region bounds ───
        function applyRegionBias(bounds) {
            currentRegionBounds = bounds;
            if (bounds && cityAutocomplete) {
                cityAutocomplete.setBounds(bounds);
                cityAutocomplete.setOptions({
                    strictBounds: false
                });
            }
        }

        // ─── Bias area autocomplete toward city bounds ───
        function applyCityBias(bounds) {
            currentCityBounds = bounds;
            if (bounds && areaAutocomplete) {
                areaAutocomplete.setBounds(bounds);
                areaAutocomplete.setOptions({
                    strictBounds: false
                });
            }
        }

        // ─── Google Maps Init ───
        function initMap() {
            const initialLat = parseFloat($('#lat').val()) || 25.2048;
            const initialLng = parseFloat($('#lng').val()) || 55.2708;
            const initialZoom = parseInt($('#zoom').val()) || 12;

            map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: initialLat,
                    lng: initialLng
                },
                zoom: initialZoom,
                mapTypeControl: true,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                },
                streetViewControl: false,
                fullscreenControl: true
            });

            marker = new google.maps.Marker({
                position: {
                    lat: initialLat,
                    lng: initialLng
                },
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'Drag me to set property location'
            });

            geocoder = new google.maps.Geocoder();

            // ─── Places Autocomplete on main search input ───
            const searchInput = document.getElementById('map-search');
            const autocomplete = new google.maps.places.Autocomplete(searchInput, {
                fields: ['geometry', 'address_components', 'formatted_address', 'name', 'place_id']
            });
            autocomplete.bindTo('bounds', map);

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place || !place.geometry) {
                    geocoder.geocode({
                        address: searchInput.value
                    }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            applyGeoResult(results[0]);
                        }
                    });
                    return;
                }
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
                marker.setPosition(place.geometry.location);
                updateCoords(place.geometry.location);
                populateAllFieldsFromGeocode(place);
            });

            // ─── Country Autocomplete ───
            const countryInput = document.getElementById('country');
            countryAutocomplete = new google.maps.places.Autocomplete(countryInput, {
                types: ['(regions)'],
                fields: ['geometry', 'address_components', 'formatted_address', 'name', 'place_id']
            });
            countryAutocomplete.addListener('place_changed', function() {
                const place = countryAutocomplete.getPlace();
                if (!place || !place.geometry) return;
                const components = place.address_components || [];
                const addr = extractAddressComponents(components);

                // Set country and apply restriction immediately
                if (addr.country) $('#country').val(addr.country);
                if (addr.countryCode) {
                    applyCountryRestriction(addr.countryCode);
                }

                // Clear dependent fields
                $('#region').val('');
                $('#city').val('');
                $('#area').val('');
                $('#landmark').val('');
                $('#zip_code').val('');
                currentRegionBounds = null;
                currentCityBounds = null;

                // Move map to country
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(5);
                }
                marker.setPosition(place.geometry.location);
                updateCoords(place.geometry.location);
                if (place.formatted_address) $('#address').val(place.formatted_address);

                // Also fill region/city if returned by the place
                if (addr.region) $('#region').val(addr.region);
                if (addr.city) $('#city').val(addr.city);
            });

            // ─── Region/State Autocomplete (restricted to selected country) ───
            const regionInput = document.getElementById('region');
            regionAutocomplete = new google.maps.places.Autocomplete(regionInput, {
                types: ['(regions)'],
                fields: ['geometry', 'address_components', 'formatted_address', 'name', 'place_id']
            });
            regionAutocomplete.addListener('place_changed', function() {
                const place = regionAutocomplete.getPlace();
                if (!place || !place.geometry) return;
                const components = place.address_components || [];
                const addr = extractAddressComponents(components);

                // Fill region
                if (addr.region) $('#region').val(addr.region);
                else if (place.name) $('#region').val(place.name);

                // Fill country if empty and apply restriction
                if (addr.country && !$('#country').val()) $('#country').val(addr.country);
                if (addr.countryCode && !currentCountryCode) applyCountryRestriction(addr.countryCode);

                // Clear city and area
                $('#city').val('');
                $('#area').val('');
                currentCityBounds = null;

                // Bias city autocomplete to region bounds
                if (place.geometry.viewport) {
                    applyRegionBias(place.geometry.viewport);
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(8);
                }
                marker.setPosition(place.geometry.location);
                updateCoords(place.geometry.location);
                if (place.formatted_address) $('#address').val(place.formatted_address);
                if (addr.city) $('#city').val(addr.city);
                if (addr.zip_code) $('#zip_code').val(addr.zip_code);
            });

            // ─── City Autocomplete (restricted to country, biased to region) ───
            const cityInput = document.getElementById('city');
            cityAutocomplete = new google.maps.places.Autocomplete(cityInput, {
                types: ['(cities)'],
                fields: ['geometry', 'address_components', 'formatted_address', 'name', 'place_id']
            });
            cityAutocomplete.addListener('place_changed', function() {
                const place = cityAutocomplete.getPlace();
                if (!place || !place.geometry) return;
                const components = place.address_components || [];
                const addr = extractAddressComponents(components);

                // Fill city
                if (addr.city) $('#city').val(addr.city);
                else if (place.name) $('#city').val(place.name);

                // Fill country & region if empty
                if (addr.country && !$('#country').val()) $('#country').val(addr.country);
                if (addr.countryCode && !currentCountryCode) applyCountryRestriction(addr.countryCode);
                if (addr.region && !$('#region').val()) $('#region').val(addr.region);

                // Clear area
                $('#area').val('');

                // Bias area autocomplete to city bounds
                if (place.geometry.viewport) {
                    applyCityBias(place.geometry.viewport);
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(13);
                }
                marker.setPosition(place.geometry.location);
                updateCoords(place.geometry.location);
                if (place.formatted_address) $('#address').val(place.formatted_address);
                if (addr.area) $('#area').val(addr.area);
                if (addr.zip_code) $('#zip_code').val(addr.zip_code);
            });

            // ─── Area/District Autocomplete (restricted to country, biased to city) ───
            const areaInput = document.getElementById('area');
            areaAutocomplete = new google.maps.places.Autocomplete(areaInput, {
                types: ['geocode'],
                fields: ['geometry', 'address_components', 'formatted_address', 'name', 'place_id']
            });
            areaAutocomplete.addListener('place_changed', function() {
                const place = areaAutocomplete.getPlace();
                if (!place || !place.geometry) return;
                const components = place.address_components || [];
                const addr = extractAddressComponents(components);

                // Fill area
                if (addr.area) $('#area').val(addr.area);
                else if (place.name) $('#area').val(place.name);

                // Fill parent fields if empty
                if (addr.country && !$('#country').val()) $('#country').val(addr.country);
                if (addr.countryCode && !currentCountryCode) applyCountryRestriction(addr.countryCode);
                if (addr.region && !$('#region').val()) $('#region').val(addr.region);
                if (addr.city && !$('#city').val()) $('#city').val(addr.city);

                // Move map to area
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(15);
                }
                marker.setPosition(place.geometry.location);
                updateCoords(place.geometry.location);
                if (place.formatted_address) $('#address').val(place.formatted_address);
                if (addr.zip_code) $('#zip_code').val(addr.zip_code);
            });

            // ─── Initialize restrictions if country is already filled (edit mode) ───
            const existingCountry = $('#country').val() ? $('#country').val().trim() : '';
            if (existingCountry && geocoder) {
                geocoder.geocode({
                    address: existingCountry
                }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        const cc = results[0].address_components.find(c => c.types.includes('country'));
                        if (cc && cc.short_name) {
                            applyCountryRestriction(cc.short_name);
                        }
                    }
                });
                // Also initialize region/city bounds if values exist
                const existingRegion = $('#region').val() ? $('#region').val().trim() : '';
                const existingCity = $('#city').val() ? $('#city').val().trim() : '';
                if (existingRegion) {
                    const regionQuery = existingRegion + ', ' + existingCountry;
                    geocoder.geocode({
                        address: regionQuery
                    }, function(results, status) {
                        if (status === 'OK' && results[0] && results[0].geometry && results[0].geometry.viewport) {
                            applyRegionBias(results[0].geometry.viewport);
                        }
                    });
                }
                if (existingCity) {
                    const cityQuery = existingCity + (existingRegion ? ', ' + existingRegion : '') + ', ' + existingCountry;
                    geocoder.geocode({
                        address: cityQuery
                    }, function(results, status) {
                        if (status === 'OK' && results[0] && results[0].geometry && results[0].geometry.viewport) {
                            applyCityBias(results[0].geometry.viewport);
                        }
                    });
                }
            }

            // ─── Marker drag → reverse geocode ───
            marker.addListener('dragend', function() {
                const pos = marker.getPosition();
                map.panTo(pos);
                updateCoords(pos);
                reverseGeocodeAndFill(pos);
            });

            // ─── Map click → move marker + reverse geocode ───
            map.addListener('click', function(event) {
                marker.setPosition(event.latLng);
                map.panTo(event.latLng);
                updateCoords(event.latLng);
                reverseGeocodeAndFill(event.latLng);
            });

            // ─── Zoom change → update zoom field ───
            map.addListener('zoom_changed', function() {
                $('#zoom').val(map.getZoom());
            });

            // Search button fallback
            $('#search-btn').click(function() {
                const address = $('#map-search').val();
                if (address) {
                    geocoder.geocode({
                        address: address
                    }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            applyGeoResult(results[0]);
                        }
                    });
                }
            });
        }

        // ─── Helper: update lat/lng/zoom inputs ───
        function updateCoords(latLng) {
            $('#lat').val(latLng.lat().toFixed(7));
            $('#lng').val(latLng.lng().toFixed(7));
            $('#zoom').val(map.getZoom());
        }

        // ─── Reverse geocode a position ───
        function reverseGeocodeAndFill(latLng) {
            geocoder.geocode({
                location: {
                    lat: latLng.lat(),
                    lng: latLng.lng()
                }
            }, function(results, status) {
                if (status === 'OK' && results && results.length > 0) {
                    populateAllFieldsFromGeocode(results[0]);
                }
            });
        }

        // ─── Apply a geocode result to map + fields ───
        function applyGeoResult(result) {
            if (result.geometry.viewport) {
                map.fitBounds(result.geometry.viewport);
            } else {
                map.setCenter(result.geometry.location);
                map.setZoom(15);
            }
            marker.setPosition(result.geometry.location);
            updateCoords(result.geometry.location);
            populateAllFieldsFromGeocode(result);
        }

        // ─── Master populate: fill ALL fields from Google geocode ───
        function populateAllFieldsFromGeocode(result) {
            const components = result.address_components || [];
            const addr = extractAddressComponents(components);

            // Fill formatted address
            if (result.formatted_address) {
                $('#address').val(result.formatted_address);
                $('#map-search').val(result.formatted_address);
            }

            // Fill Country, Region, City
            if (addr.country) $('#country').val(addr.country);
            if (addr.region) $('#region').val(addr.region);
            if (addr.city) $('#city').val(addr.city);

            // Fill Area, Landmark, Zip Code
            $('#area').val(addr.area || '');
            if (addr.zip_code) $('#zip_code').val(addr.zip_code);
            const landmarkVal = addr.landmark || addr.route || '';
            if (landmarkVal) $('#landmark').val(landmarkVal);

            // Apply country restriction immediately from extracted ISO code
            if (addr.countryCode) {
                applyCountryRestriction(addr.countryCode);
            }

            // Geocode region to get bounds for city bias
            if (addr.region && geocoder) {
                const regionQuery = addr.region + (addr.country ? ', ' + addr.country : '');
                geocoder.geocode({
                    address: regionQuery
                }, function(results, status) {
                    if (status === 'OK' && results[0] && results[0].geometry && results[0].geometry.viewport) {
                        applyRegionBias(results[0].geometry.viewport);
                    }
                });
            }

            // Geocode city to get bounds for area bias
            if (addr.city && geocoder) {
                const cityQuery = addr.city + (addr.region ? ', ' + addr.region : '') + (addr.country ? ', ' + addr.country : '');
                geocoder.geocode({
                    address: cityQuery
                }, function(results, status) {
                    if (status === 'OK' && results[0] && results[0].geometry && results[0].geometry.viewport) {
                        applyCityBias(results[0].geometry.viewport);
                    }
                });
            }
        }

        // ─── Extract structured data from Google address_components ───
        function extractAddressComponents(components) {
            const addr = {
                country: '',
                countryCode: '',
                region: '',
                city: '',
                area: '',
                landmark: '',
                route: '',
                zip_code: ''
            };
            components.forEach(function(comp) {
                const types = comp.types;
                if (types.includes('country')) {
                    addr.country = comp.long_name;
                    addr.countryCode = comp.short_name;
                }
                if (types.includes('administrative_area_level_1')) addr.region = comp.long_name;
                if (types.includes('locality')) addr.city = comp.long_name;
                if (!addr.city && types.includes('administrative_area_level_2')) addr.city = comp.long_name;
                if (types.includes('sublocality_level_1') || types.includes('sublocality') ||
                    types.includes('neighborhood') || types.includes('administrative_area_level_3')) {
                    if (!addr.area) addr.area = comp.long_name;
                }
                if (types.includes('postal_code')) addr.zip_code = comp.long_name;
                if (types.includes('route')) addr.route = comp.long_name;
                if (types.includes('point_of_interest') || types.includes('establishment')) {
                    addr.landmark = comp.long_name;
                }
            });
            return addr;
        }

        // ─── Load Google Maps Script ───
        const mapsApiKey = '{{ config("services.google_maps.api_key") }}';
        if (mapsApiKey) {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${mapsApiKey}&libraries=places&callback=initMap`;
            script.async = true;
            script.defer = true;
            window.initMap = initMap;
            document.head.appendChild(script);
        } else {
            document.getElementById('map').innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted"><div class="text-center"><i class="fe fe-alert-triangle fs-3 d-block mb-2"></i>Google Maps API Key is missing.<br>Add <code>GOOGLE_MAPS_API_KEY</code> to your .env file.</div></div>';
        }


        // Parse bookings into calendar events
        const bookings = @json($data?->bookings ?? []);
        const bookingStatusColors = {
            confirmed: '#dc3545',
            checked_in: '#0d6efd',
            pending: '#ffc107',
            completed: '#198754',
            cancelled: '#6c757d',
            no_show: '#212529'
        };

        const events = bookings.map(b => {
            const status = b.booking_status || 'pending';
            return {
                title: `Booked: ${status}`,
                start: b.check_in,
                end: b.check_out,
                color: bookingStatusColors[status] || '#6c757d',
                allDay: true
            };
        }).filter(e => e.start && e.end);

        var calendarEl = document.getElementById('availability-calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: events,
            fixedWeekCount: false,
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth'
            },
            themeSystem: 'standard'
        });

        // Videos logic
        let videoIndex = {{ $data->videos->count() }};
        $('#add-video').click(function() {
            const html = `
                                                                                                                                    <div class="video-row card mb-3 border p-3">
                                                                                                                                        <div class="row">
                                                                                                                                            <div class="col-md-3 form-group">
                                                                                                                                                <label class="form-label text-muted small">Video Type</label>
                                                                                                                                                <select name="videos[${videoIndex}][video_type]" class="form-control form-control-sm video-type-select">
                                                                                                                                                    <option value="embed">Embed (YouTube/Vimeo)</option>
                                                                                                                                                    <option value="upload">Upload File</option>
                                                                                                                                                </select>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-7 form-group video-embed-group">
                                                                                                                                                <label class="form-label text-muted small">Embed URL / Code</label>
                                                                                                                                                <input type="text" name="videos[${videoIndex}][embed]" class="form-control form-control-sm" placeholder="e.g. https://www.youtube.com/watch?v=...">
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-4 form-group video-upload-group d-none">
                                                                                                                                                <label class="form-label text-muted small">Video File</label>
                                                                                                                                                <input type="file" name="videos[${videoIndex}][video_file]" class="form-control form-control-sm" accept="video/*">
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group video-poster-group d-none">
                                                                                                                                                <label class="form-label text-muted small">Poster Image</label>
                                                                                                                                                <input type="file" name="videos[${videoIndex}][poster_file]" class="form-control form-control-sm" accept="image/*">
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-1 d-flex align-items-end justify-content-center pb-3">
                                                                                                                                                <button type="button" class="btn btn-sm btn-danger remove-video">
                                                                                                                                                    <i class="fe fe-trash"></i>
                                                                                                                                                </button>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `;
            $('#videos-container').append(html);
            videoIndex++;
        });

        $('#videos-container').on('change', '.video-type-select', function() {
            const row = $(this).closest('.video-row');
            if ($(this).val() === 'upload') {
                row.find('.video-embed-group').addClass('d-none').find('input').removeAttr('required');
                row.find('.video-upload-group').removeClass('d-none');
                row.find('.video-poster-group').removeClass('d-none');
                // Only require file if no existing file is present
                if (row.find('input[name*="[existing_video_file]"]').length === 0) {
                    row.find('.video-upload-group').find('input[type="file"]').attr('required', 'required');
                }
            } else {
                row.find('.video-embed-group').removeClass('d-none').find('input').attr('required', 'required');
                row.find('.video-upload-group').addClass('d-none').find('input').removeAttr('required');
                row.find('.video-poster-group').addClass('d-none');
            }
        });

        $('#videos-container').on('click', '.remove-video', function() {
            $(this).closest('.video-row').remove();
        });


        // Policies logic
        let policyIndex = {{ $data->policies->count() }};
        $('#add-policy').click(function() {
            const html = `
                                                                                                                                    <div class="policy-row card mb-3 border p-3">
                                                                                                                                        <div class="row">
                                                                                                                                            <div class="col-md-2 form-group">
                                                                                                                                                <label class="form-label text-muted small">Icon Type</label>
                                                                                                                                                <select name="policies_data[${policyIndex}][icon_type]" class="form-control form-control-sm policy-icon-type">
                                                                                                                                                    <option value="image">Image Upload</option>
                                                                                                                                                    <option value="code">SVG/HTML Code</option>
                                                                                                                                                </select>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group policy-icon-image-group">
                                                                                                                                                <label class="form-label text-muted small">Icon Image</label>
                                                                                                                                                <input type="file" name="policies_data[${policyIndex}][icon_file]" class="form-control form-control-sm" accept="image/*">
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group policy-icon-code-group d-none">
                                                                                                                                                <label class="form-label text-muted small">Icon Code (SVG/HTML)</label>
                                                                                                                                                <textarea name="policies_data[${policyIndex}][icon_code]" class="form-control form-control-sm" rows="1"></textarea>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group">
                                                                                                                                                <label class="form-label text-muted small">Title</label>
                                                                                                                                                <input type="text" name="policies_data[${policyIndex}][title]" class="form-control form-control-sm" placeholder="e.g. Check-in / Check-out">
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-3 form-group">
                                                                                                                                                <label class="form-label text-muted small">Description</label>
                                                                                                                                                <textarea name="policies_data[${policyIndex}][description]" class="form-control form-control-sm" rows="1" placeholder="e.g. 4:00 PM - 8:00 PM"></textarea>
                                                                                                                                            </div>
                                                                                                                                            <div class="col-md-1 d-flex align-items-end justify-content-center pb-3">
                                                                                                                                                <button type="button" class="btn btn-sm btn-danger remove-policy">
                                                                                                                                                    <i class="fe fe-trash"></i>
                                                                                                                                                </button>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `;
            $('#policies-container').append(html);
            policyIndex++;
        });

        $('#policies-container').on('change', '.policy-icon-type', function() {
            const row = $(this).closest('.policy-row');
            if ($(this).val() === 'image') {
                row.find('.policy-icon-image-group').removeClass('d-none');
                row.find('.policy-icon-code-group').addClass('d-none').find('textarea').removeAttr('required');
                // Only require file if no existing icon is present
                if (row.find('input[name*="[existing_icon]"]').length === 0) {
                    row.find('.policy-icon-image-group').find('input[type="file"]').attr('required', 'required');
                }
            } else {
                row.find('.policy-icon-image-group').addClass('d-none').find('input').removeAttr('required');
                row.find('.policy-icon-code-group').removeClass('d-none').find('textarea').attr('required', 'required');
            }
        });

        $('#policies-container').on('click', '.remove-policy', function() {
            $(this).closest('.policy-row').remove();
        });

        // Render calendar perfectly when the page is loaded
        setTimeout(function() {
            calendar.render();
        }, 100);

        // ─── Property Photos: Select / Deselect / AJAX Delete ───
        const propertyId = {{ $data->property_id }};
        const deletePhotosUrl = '{{ route("property.delete-photos") }}';
        const csrfToken = '{{ csrf_token() }}';

        // Select All
        $('#select-all-photos').on('click', function () {
            $('.photo-delete-cb').prop('checked', true).trigger('change');
        });

        // Deselect All
        $('#deselect-all-photos').on('click', function () {
            $('.photo-delete-cb').prop('checked', false).trigger('change');
        });

        // Toggle selection when clicking on the image itself
        window.togglePhotoDelete = function (img) {
            const cb = $(img).closest('.photo-card').find('.photo-delete-cb');
            cb.prop('checked', !cb.prop('checked')).trigger('change');
        };

        // Sync red-border highlight whenever a checkbox changes
        $(document).on('change', '.photo-delete-cb', function () {
            const card = $(this).closest('.photo-card');
            if ($(this).prop('checked')) {
                card.addClass('border-danger border-2');
            } else {
                card.removeClass('border-danger border-2');
            }
            // Show/hide delete button based on selection count
            const count = $('.photo-delete-cb:checked').length;
            $('#delete-selected-photos').toggleClass('d-none', count === 0)
                .text('🗑 Delete Selected (' + count + ')');
        });

        // AJAX Delete Selected
        let pendingDeleteIds = [];

        $('#delete-selected-photos').on('click', function () {
            pendingDeleteIds = [];
            $('.photo-delete-cb:checked').each(function () {
                pendingDeleteIds.push($(this).val());
            });

            if (pendingDeleteIds.length === 0) {
                alert('No photos selected.');
                return;
            }

            // Show confirmation modal
            $('#photoDeleteCount').text(pendingDeleteIds.length);
            $('#confirmDeletePhotosModal').modal('show');
        });

        // Confirmed: actually delete
        $('#confirmDeletePhotosBtn').on('click', function () {
            $('#confirmDeletePhotosModal').modal('hide');

            if (pendingDeleteIds.length === 0) return;

            const $btn = $('#delete-selected-photos');
            $btn.prop('disabled', true).text('Deleting...');

            $.ajax({
                url: deletePhotosUrl,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                traditional: true,
                data: {
                    _token: csrfToken,
                    property_id: propertyId,
                    'photo_ids[]': pendingDeleteIds,
                },
                success: function (res) {
                    if (res['t-success']) {
                        // Remove deleted cards from DOM
                        $('.photo-delete-cb:checked').each(function () {
                            $(this).closest('[class*="col-"]').remove();
                        });
                        $btn.addClass('d-none').prop('disabled', false);
                        pendingDeleteIds = [];

                        // Hide select buttons if no photos remain
                        if ($('.photo-delete-cb').length === 0) {
                            $('#select-all-photos, #deselect-all-photos, #delete-selected-photos').addClass('d-none');
                            const $grid = $('#photos-grid');
                            if ($grid.length) {
                                $grid.replaceWith('<div class="alert alert-light border py-2 mb-3"><i class="fe fe-image me-2 text-muted"></i><span class="text-muted">No photos uploaded yet.</span></div>');
                            }
                        }

                        // Show success alert
                        const alertHtml = '<div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert"><i class="fe fe-check-circle me-2"></i>' + res.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                        const $grid2 = $('#photos-grid');
                        if ($grid2.length) $grid2.before(alertHtml);
                        else $('.photo-delete-cb').closest('.mb-5').prepend(alertHtml);
                    } else {
                        alert('Something went wrong. Please try again.');
                        $btn.prop('disabled', false);
                    }
                },
                error: function (xhr) {
                    console.error('Delete photos error:', xhr.status, xhr.responseText);
                    let msg = 'Server error (' + xhr.status + ')';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg += ': ' + xhr.responseJSON.message;
                    alert(msg);
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
