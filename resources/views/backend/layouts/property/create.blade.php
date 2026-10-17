@extends('backend.app')

@section('title', 'Create Property')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .section-title {
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 20px;
        font-weight: bold;
    }

    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        color: #007bff;
        border-bottom: 3px solid #007bff;
    }

    .tab-content {
        padding-top: 20px;
    }

    .amenity-card {
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 15px;
        background: #fafafa;
        margin-bottom: 20px;
    }

    /* Form spacing */
    .form-group {
        margin-bottom: 1rem;
    }

    /* Google Maps search input styling */
    #map-search {
        border-radius: 6px;
        font-size: 1rem;
    }

    #map-search:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    }

    /* Map container */
    #map {
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    /* Google Places autocomplete dropdown fix */
    .pac-container {
        z-index: 10000 !important;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        border: 1px solid #e2e8f0;
        font-family: inherit;
    }

    .pac-item {
        padding: 8px 12px;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .pac-item:hover {
        background: #f0f7ff;
    }

    /* Section headers */
    .mb-5>h4 {
        color: #1e293b;
        font-size: 1.15rem;
    }

    /* Responsive map */
    @media (max-width: 768px) {
        #map {
            height: 280px !important;
        }

        .form-group {
            margin-bottom: 0.75rem;
        }
    }

    /* Select2 height fix inside form */
    .select2-container .select2-selection--single {
        height: 38px;
        display: flex;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title fs-2">Create New Property</h1>
    </div>
    <div class="ms-auto pageheader-btn">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('property.index') }}">Properties</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Property</li>
        </ol>
    </div>
</div>

<form action="{{ route('property.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row row-sm">
        <div class="col-lg-12 col-md-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Property Details</h3>
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

                    <!-- BASICS -->
                    <div class="mb-5">
                        <h4 class="mb-4 border-bottom pb-2">Basics</h4>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="form-label">Attached to User <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="form-label">Property Status</label>
                                <select name="property_status" class="form-control @error('property_status') is-invalid @enderror">
                                    <option value="active" {{ old('property_status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('property_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="pending" {{ (old('property_status') ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="sold" {{ old('property_status') == 'sold' ? 'selected' : '' }}>Sold</option>
                                    <option value="withdrawn" {{ old('property_status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                </select>
                                @error('property_status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="form-label">Contract Type</label>
                                <select name="property_main_type" class="form-control @error('property_main_type') is-invalid @enderror">
                                    <option value="rent" {{ old('property_main_type') == 'rent' ? 'selected' : '' }}>Rent</option>
                                    <option value="sale" {{ old('property_main_type') == 'sale' ? 'selected' : '' }}>Sale</option>
                                </select>
                                @error('property_main_type') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label">Property Type</label>
                                <select name="property_type_id" class="form-control select2 @error('property_type_id') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    @foreach($propertyTypes as $type)
                                    <option value="{{ $type->property_type_id }}" {{ old('property_type_id') == $type->property_type_id ? 'selected' : '' }}>{{ $type->title }}</option>
                                    @endforeach
                                </select>
                                @error('property_type_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label">Unit Type</label>
                                <select name="unit_type_id" class="form-control select2 @error('unit_type_id') is-invalid @enderror">
                                    <option value="">Select Unit</option>
                                    @foreach($unitTypes as $ut)
                                    <option value="{{ $ut->unit_type_id }}" {{ old('unit_type_id') == $ut->unit_type_id ? 'selected' : '' }}>{{ $ut->title }}</option>
                                    @endforeach
                                </select>
                                @error('unit_type_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label">Property Category</label>
                                <select name="property_category_id" class="form-control select2 @error('property_category_id') is-invalid @enderror">
                                    <option value="">Select Category</option>
                                    @foreach($propertyCategories as $cat)
                                    <option value="{{ $cat->property_category_id }}" {{ old('property_category_id') == $cat->property_category_id ? 'selected' : '' }}>{{ $cat->title }}</option>
                                    @endforeach
                                </select>
                                @error('property_category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="form-label">Property Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="Villa with Sea View"
                                    value="{{ old('title') }}" required>
                                @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="form-label">Full Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="8" required>{{ old('description') }}</textarea>
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
                                    <option value="{{ $curr->currency_id }}">{{ $curr->title }} ({{ $curr->currency_code }})</option>
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Stars <span class="text-danger">*</span></label>
                            <select name="stars" class="form-control @error('stars') is-invalid @enderror" required>
                                @foreach(['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'] as $star)
                                <option value="{{ $star }}" {{ old('stars') == $star ? 'selected' : '' }}>{{ $star }}</option>
                                @endforeach
                            </select>
                            @error('stars') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Available From <span class="text-danger">*</span></label>
                            <input type="date" name="available_from" class="form-control @error('available_from') is-invalid @enderror"
                                value="{{ old('available_from') ?? date('Y-m-d') }}" required>
                            @error('available_from') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Total Square Feet (Sq. Meters) <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="total_square_feet" class="form-control @error('total_square_feet') is-invalid @enderror" step="0.01" value="{{ old('total_square_feet') }}" required>
                            @error('total_square_feet') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">No. Bedrooms <span class="text-danger">*</span></label>
                            <input type="number" name="no_bedrooms" class="form-control @error('no_bedrooms') is-invalid @enderror" value="{{ old('no_bedrooms') }}" required>
                            @error('no_bedrooms') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">No. Bathrooms <span class="text-danger">*</span></label>
                            <input type="number" name="no_bathrooms" class="form-control @error('no_bathrooms') is-invalid @enderror" value="{{ old('no_bathrooms') }}" required>
                            @error('no_bathrooms') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">No. Floors <span class="text-danger">*</span></label>
                            <input type="number" name="no_floors" class="form-control @error('no_floors') is-invalid @enderror" value="{{ old('no_floors') }}" required>
                            @error('no_floors') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">No. Guests <span class="text-danger">*</span></label>
                            <input type="number" name="no_guests" class="form-control @error('no_guests') is-invalid @enderror" value="{{ old('no_guests') }}" required>
                            @error('no_guests') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Min Stay (Nights) <span class="text-danger">*</span></label>
                            <input type="number" name="min_no_of_stay" class="form-control @error('min_no_of_stay') is-invalid @enderror" value="{{ old('min_no_of_stay') ?? 1 }}" required>
                            @error('min_no_of_stay') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Visibility Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-3 mt-4">
                            <label class="form-label d-block">Parking Available</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="parking" id="parkingYes" value="1" checked>
                                <label class="form-check-label" for="parkingYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="parking" id="parkingNo" value="0">
                                <label class="form-check-label" for="parkingNo">No</label>
                            </div>
                        </div>
                        <div class="col-md-3 mt-4">
                            <label class="form-label d-block">Car Essential</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="car_essential" id="carEssentialYes" value="1">
                                <label class="form-check-label" for="carEssentialYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="car_essential" id="carEssentialNo" value="0" checked>
                                <label class="form-check-label" for="carEssentialNo">No</label>
                            </div>
                        </div>
                        <div class="col-md-4 mt-4">
                            <label class="form-label d-block">Allow Direct Bookings</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="allow_bookings" id="allowBookingsYes" value="1" checked>
                                <label class="form-check-label" for="allowBookingsYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="allow_bookings" id="allowBookingsNo" value="0">
                                <label class="form-check-label" for="allowBookingsNo">No</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LOCATION -->
                <div class="mb-5 mt-5">
                    <h4 class="mb-4 border-bottom pb-2"><i class="fe fe-map-pin me-2"></i>Choose Property Location</h4>

                    {{-- Search + Map --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Search Location on Google Maps</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-white"><i class="fe fe-search"></i></span>
                                <input type="text" id="map-search" class="form-control form-control-lg"
                                    placeholder="Type an address, city, or landmark to search...">
                            </div>
                            <div id="map" style="height: 800px; width: 100%; border: 1px solid #dee2e6; border-radius: 8px;"></div>
                            <small class="text-muted mt-1 d-block"><i class="fe fe-info me-1"></i>Click on the map or drag the red marker to pinpoint exact location. All fields below auto-fill.</small>
                        </div>
                    </div>

                    {{-- Location Fields (Google Maps powered) --}}
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" name="country" id="country" class="form-control @error('country') is-invalid @enderror"
                                placeholder="Type to search country..." value="{{ old('country') }}" autocomplete="off">
                            @error('country') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">State / Region</label>
                            <input type="text" name="region" id="region" class="form-control @error('region') is-invalid @enderror"
                                placeholder="Type to search region..." value="{{ old('region') }}" autocomplete="off">
                            @error('region') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror"
                                placeholder="Type to search city..." value="{{ old('city') }}" autocomplete="off">
                            @error('city') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Additional Location Fields --}}
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Area / District</label>
                            <input type="text" name="area" id="area" class="form-control @error('area') is-invalid @enderror"
                                placeholder="Type to search area..." value="{{ old('area') }}" autocomplete="off">
                            @error('area') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Landmark</label>
                            <input type="text" name="landmark" id="landmark" class="form-control @error('landmark') is-invalid @enderror"
                                placeholder="e.g. Near Central Park" value="{{ old('landmark') }}">
                            @error('landmark') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Zip / Postal Code</label>
                            <input type="text" name="zip_code" id="zip_code" class="form-control @error('zip_code') is-invalid @enderror"
                                placeholder="e.g. 10001" value="{{ old('zip_code') }}">
                            @error('zip_code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">Full Address</label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2"
                                placeholder="Full street address (auto-filled from map)">{{ old('address') }}</textarea>
                            @error('address') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Hidden country_code field (auto-filled by JS) --}}
                    <input type="hidden" name="country_code" id="country_code" value="{{ old('country_code') }}">

                    {{-- Coordinates (collapsible for clean UI) --}}
                    <div class="row">
                        <div class="col-12 mb-2">
                            <a class="small text-muted" data-bs-toggle="collapse" href="#coordsCollapse" role="button" aria-expanded="false">
                                <i class="fe fe-chevron-down me-1"></i>Show Latitude / Longitude / Zoom
                            </a>
                        </div>
                        <div class="collapse" id="coordsCollapse">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="form-label text-muted small">Latitude</label>
                                    <input type="text" name="lat" id="lat" class="form-control form-control-sm bg-light" value="{{ old('lat') }}" readonly>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label text-muted small">Longitude</label>
                                    <input type="text" name="lng" id="lng" class="form-control form-control-sm bg-light" value="{{ old('lng') }}" readonly>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label text-muted small">Zoom Level</label>
                                    <input type="number" name="zoom" id="zoom" class="form-control form-control-sm bg-light" value="{{ old('zoom') ?? 12 }}" readonly>
                                </div>
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
                        <!-- Dynamic rows will appear here -->
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
                        <!-- Dynamic rows will appear here -->
                    </div>
                </div>

                <!-- ADDON, DISCOUNT SELECTIONS -->
                <div class="mb-5 mt-5">
                    <h4 class="mb-4 border-bottom pb-2">Addons & Discounts</h4>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Select Addon</label>
                            <select name="addon_id" class="form-control select2">
                                <option value="">No Addon</option>
                                @foreach($addons as $addon)
                                <option value="{{ $addon->addon_id }}">{{ $addon->addon_type }}
                                    ({{ $addon->price }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Select Discount</label>
                            <select name="discount_id" class="form-control select2">
                                <option value="">No Discount</option>
                                @foreach($discounts as $discount)
                                <option value="{{ $discount->discount_id }}">{{ $discount->discount_type }}
                                    ({{ $discount->discount_percentage }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- PROPERTY PHOTOS -->
                <div class="mb-5 mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                        <h4 class="mb-0">Property Photos</h4>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Photos (multiple allowed)</label>
                        <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Select multiple images at once. First image will be the featured
                            photo.</small>
                    </div>
                </div>

                <!-- PROPERTY VIDEOS -->
                <div class="mb-5 mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                        <h4 class="mb-0">Property Videos</h4>
                        <button type="button" class="btn btn-sm btn-success" id="add-video">
                            <i class="fe fe-plus"></i> Add Video
                        </button>
                    </div>
                    <div id="videos-container">
                        <!-- Dynamic rows will appear here -->
                    </div>
                </div>


                <!-- PROPERTY POLICIES -->
                <div class="mb-5 mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                        <h4 class="mb-0">Property Policies</h4>
                        <button type="button" class="btn btn-sm btn-success" id="add-policy">
                            <i class="fe fe-plus"></i> Add Policy
                        </button>
                    </div>
                    <div id="policies-container">
                        <!-- Dynamic rows will appear here -->
                    </div>
                </div>

                <!-- SEO / META -->
                <div class="mb-5 mt-5">
                    <h4 class="mb-4 border-bottom pb-2">SEO / Meta Details</h4>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="property_meta_title" class="form-control"
                                placeholder="Meta Title for SEO">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Meta Keywords</label>
                            <input type="text" name="property_meta_keywords" class="form-control"
                                placeholder="keyword1, keyword2, keyword3">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Meta Description</label>
                            <textarea name="property_meta_description" class="form-control" rows="3"
                                placeholder="Short description for search engines"></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card-footer mt-4">
                <button type="submit" class="btn btn-primary">Create Property</button>
                <a href="{{ route('property.index') }}" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </div>
    </div>
    </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        // Facilities logic
        let facilityIndex = 0;
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
                                                                                                                                                                                                        <input type="file" name="facilities[${facilityIndex}][icon_file]" class="form-control form-control-sm">
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
                row.find('.icon-file-group').addClass('d-none');
                row.find('.icon-code-group').removeClass('d-none');
            } else {
                row.find('.icon-file-group').removeClass('d-none');
                row.find('.icon-code-group').addClass('d-none');
            }
        });


        $('#facilities-container').on('click', '.remove-facility', function() {
            $(this).closest('.facility-row').remove();
        });

        // Amenities logic
        let amenityIndex = 0;
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
                                                                                                                                                                                                        <input type="file" name="amenities[${amenityIndex}][icon_file]" class="form-control form-control-sm">
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
                row.find('.icon-file-group-amenity').addClass('d-none');
                row.find('.icon-code-group-amenity').removeClass('d-none');
            } else {
                row.find('.icon-file-group-amenity').removeClass('d-none');
                row.find('.icon-code-group-amenity').addClass('d-none');
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
            const initialLat = parseFloat('{{ old("lat") }}') || 25.2048;
            const initialLng = parseFloat('{{ old("lng") }}') || 55.2708;
            const initialZoom = parseInt('{{ old("zoom", 5) }}') || 5;

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

            // ─── Initialize restrictions if country is already filled (e.g. old() values) ───
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


        // Videos logic
        let videoIndex = 0;
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
                row.find('.video-upload-group').removeClass('d-none').find('input').attr('required', 'required');
                row.find('.video-poster-group').removeClass('d-none');
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
        let policyIndex = 0;
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
                row.find('.policy-icon-image-group').removeClass('d-none').find('input').attr('required', 'required');
                row.find('.policy-icon-code-group').addClass('d-none').find('textarea').removeAttr('required');
            } else {
                row.find('.policy-icon-image-group').addClass('d-none').find('input').removeAttr('required');
                row.find('.policy-icon-code-group').removeClass('d-none').find('textarea').attr('required', 'required');
            }
        });

        $('#policies-container').on('click', '.remove-policy', function() {
            $(this).closest('.policy-row').remove();
        });

        // Trigger defaults
        $('#add-facility').trigger('click');
        $('#add-amenity').trigger('click');
    });
</script>
@endpush