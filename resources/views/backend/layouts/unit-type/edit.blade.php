@extends('backend.app')

@section('title', 'Edit Unit Type')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Unit Type</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
                <li class="breadcrumb-item active" aria-current="page">Unit Type</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Unit Type Details</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('unit-type.update', $data->unit_type_id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $data->title) }}"
                                required>
                            @error('title')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="slug">Slug <i class="fe fe-help-circle text-warning fs-14"
                                    title="Unique URL-friendly name"></i></label>
                            <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $data->slug) }}">
                            @error('slug')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label fs-16">Status <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active_yes" value="active"
                                    {{ $data->status === 'active' ? 'checked' : '' }}>
                                <label class="form-check-label fs-15" for="active_yes">Active</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active_no" value="inactive"
                                    {{ $data->status === 'inactive' ? 'checked' : '' }}>
                                <label class="form-check-label fs-15" for="active_no">Inactive</label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">Update</button>
                            <a href="{{ route('unit-type.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
