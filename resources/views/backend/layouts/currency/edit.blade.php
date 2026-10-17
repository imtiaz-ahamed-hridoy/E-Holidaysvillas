@extends('backend.app')

@section('title', 'Edit Currency')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Currency</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
                <li class="breadcrumb-item active" aria-current="page">Currency</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Edit Currency Details</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('currency.update', $data->currency_id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="{{ old('title', $data->title) }}" required>
                            @error('title')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="currency_code">Currency Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="currency_code" id="currency_code" class="form-control"
                                value="{{ old('currency_code', $data->currency_code) }}" required>
                            @error('currency_code')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="currency_rate">Exchange Rate <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.0001" name="currency_rate" id="currency_rate" class="form-control"
                                value="{{ old('currency_rate', $data->currency_rate) }}" required>
                            @error('currency_rate')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Active Status</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active_yes" value="active"
                                    {{ $data->status === 'active' ? 'checked' : '' }}>
                                <label class="form-check-label" for="active_yes">Active</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active_no"
                                    value="inactive" {{ $data->status === 'inactive' ? 'checked' : '' }}>
                                <label class="form-check-label" for="active_no">Inactive</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Is Default?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_default" id="default_yes" value="yes"
                                    {{ $data->is_default === 'yes' ? 'checked' : '' }}>
                                <label class="form-check-label" for="default_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_default" id="default_no" value="no" {{ $data->is_default === 'no' ? 'checked' : '' }}>
                                <label class="form-check-label" for="default_no">No</label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">Update Currency</button>
                            <a href="{{ route('currency.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection