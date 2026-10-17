@extends('backend.app')

@section('title', 'Edit Rate')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Rate</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('rate.index') }}">Rates</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Rate</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Rate Details</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('rate.update', $data->rate_id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="property_id">Property <span
                                            class="text-danger">*</span></label>
                                    <select name="property_id" id="property_id" class="form-control" required>
                                        <option value="">Select Property</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->property_id }}" {{ old('property_id', $data->property_id) == $property->property_id ? 'selected' : '' }}>
                                                {{ $property->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('property_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        value="{{ old('start_date', $data->start_date->format('Y-m-d')) }}" required>
                                    @error('start_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                        value="{{ old('end_date', $data->end_date->format('Y-m-d')) }}" required>
                                    @error('end_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="nightly_rate">Nightly Rate <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="nightly_rate" id="nightly_rate"
                                        class="form-control" value="{{ old('nightly_rate', $data->nightly_rate) }}"
                                        required>
                                    @error('nightly_rate')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="nightly_rate_weekend">Weekend Rate</label>
                                    <input type="number" step="0.01" name="nightly_rate_weekend" id="nightly_rate_weekend"
                                        class="form-control"
                                        value="{{ old('nightly_rate_weekend', $data->nightly_rate_weekend) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="min_stay">Min Stay (Nights)</label>
                                    <input type="number" name="min_stay" id="min_stay" class="form-control"
                                        value="{{ old('min_stay', $data->min_stay) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="persons">Base Persons</label>
                                    <input type="number" name="persons" id="persons" class="form-control"
                                        value="{{ old('persons', $data->persons) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="extra_person_price_night">Extra Person / Night</label>
                                    <input type="number" step="0.01" name="extra_person_price_night"
                                        id="extra_person_price_night" class="form-control"
                                        value="{{ old('extra_person_price_night', $data->extra_person_price_night) }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="changeover_day">Changeover Day</label>
                                    <select name="changeover_day" id="changeover_day" class="form-control">
                                        <option value="0" {{ old('changeover_day', $data->changeover_day) == 0 ? 'selected' : '' }}>Any</option>
                                        <option value="1" {{ old('changeover_day', $data->changeover_day) == 1 ? 'selected' : '' }}>Monday</option>
                                        <option value="2" {{ old('changeover_day', $data->changeover_day) == 2 ? 'selected' : '' }}>Tuesday</option>
                                        <option value="3" {{ old('changeover_day', $data->changeover_day) == 3 ? 'selected' : '' }}>Wednesday</option>
                                        <option value="4" {{ old('changeover_day', $data->changeover_day) == 4 ? 'selected' : '' }}>Thursday</option>
                                        <option value="5" {{ old('changeover_day', $data->changeover_day) == 5 ? 'selected' : '' }}>Friday</option>
                                        <option value="6" {{ old('changeover_day', $data->changeover_day) == 6 ? 'selected' : '' }}>Saturday</option>
                                        <option value="7" {{ old('changeover_day', $data->changeover_day) == 7 ? 'selected' : '' }}>Sunday</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="unit_type_id">Unit Type</label>
                                    <select name="unit_type_id" id="unit_type_id" class="form-control">
                                        <option value="">Select Unit Type</option>
                                        @foreach($unitTypes as $type)
                                            <option value="{{ $type->unit_type_id }}" {{ old('unit_type_id', $data->unit_type_id) == $type->unit_type_id ? 'selected' : '' }}>
                                                {{ $type->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Options</label>
                                    {{-- Hidden inputs MUST come first so checkbox overrides them --}}
                                    <input type="hidden" name="status" value="inactive">
                                    <input type="hidden" name="is_special_offer" value="inactive">
                                    <input type="hidden" name="is_rate_overwrite" value="inactive">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="status" id="status"
                                            value="active" {{ old('status', $data->status) === 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">Active</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="is_special_offer"
                                            id="is_special_offer" value="active" {{ old('is_special_offer', $data->is_special_offer) === 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_special_offer">Special Offer</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="is_rate_overwrite"
                                            id="is_rate_overwrite" value="active" {{ old('is_rate_overwrite', $data->is_rate_overwrite) === 'active' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_rate_overwrite">Rate Overwrite</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">Update Rate</button>
                            <a href="{{ route('rate.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection