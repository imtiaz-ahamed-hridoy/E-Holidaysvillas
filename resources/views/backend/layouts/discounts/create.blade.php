@extends('backend.app')

@section('title', 'Create Discount')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title fs-2">Create New Discount</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('discounts.index') }}">Discounts</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Discount</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Discount Details</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('discounts.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">Property <span class="text-danger">*</span></label>
                            <select name="property_id" class="form-control select2 @error('property_id') is-invalid @enderror" required>
                                <option value="">Select Property</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->property_id }}" {{ old('property_id') == $property->property_id ? 'selected' : '' }}>
                                        {{ $property->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('property_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <input type="text" name="discount_type" class="form-control @error('discount_type') is-invalid @enderror" value="{{ old('discount_type') }}"
                                required maxlength="255">
                            @error('discount_type') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Discount Percentage <span class="text-danger">*</span></label>
                            <input type="text" name="discount_percentage" class="form-control @error('discount_percentage') is-invalid @enderror"
                                value="{{ old('discount_percentage') }}" required>
                            @error('discount_percentage') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Persons</label>
                            <input type="number" name="persons" class="form-control @error('persons') is-invalid @enderror" value="{{ old('persons') }}" min="1">
                            @error('persons') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                        value="{{ old('start_date') }}" required>
                                    @error('start_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}"
                                        required>
                                    @error('end_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">Submit</button>
                            <a href="{{ route('discounts.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush