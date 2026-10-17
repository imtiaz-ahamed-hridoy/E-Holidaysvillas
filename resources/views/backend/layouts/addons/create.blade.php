@extends('backend.app')

@section('title', 'Create Addon')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title fs-2">Create New Addon</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('addons.index') }}">Addons</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Addon</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Addon Details</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('addons.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Property <span class="text-danger">*</span></label>
                            <select name="property_id" class="form-control select2" required>
                                <option value="">Select Property</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->property_id }}">{{ $property->title }}</option>
                                @endforeach
                            </select>
                            @error('property_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Addon Name (Type) <span class="text-danger">*</span></label>
                            <input type="text" name="addon_type"
                                class="form-control @error('addon_type') is-invalid @enderror"
                                value="{{ old('addon_type') }}" required maxlength="100">
                            @error('addon_type') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                                value="{{ old('price') }}" required step="0.01">
                            @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">Submit</button>
                            <a href="{{ route('addons.index') }}" class="btn btn-secondary ms-2">Cancel</a>
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