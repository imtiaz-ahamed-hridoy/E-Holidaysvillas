@extends('backend.app')

@section('title', 'Create Currency')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Create Currency</h1>
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
                    <h3 class="card-title">Currency Details</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('currency.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}"
                                placeholder="e.g. Euro" required>
                            @error('title')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="currency_code">Currency Code <span class="text-danger">*</span>
                                <i class="fe fe-info text-info fs-14" title="e.g. EUR, GBP"></i></label>
                            <input type="text" name="currency_code" id="currency_code" class="form-control"
                                value="{{ old('currency_code') }}" placeholder="e.g. EUR" required>
                            @error('currency_code')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="currency_rate">Exchange Rate <span class="text-danger">*</span>
                                <i class="fe fe-help-circle text-warning fs-14"
                                    title="Rate relative to the default currency"></i></label>
                            <input type="number" step="0.0001" name="currency_rate" id="currency_rate" class="form-control"
                                value="{{ old('currency_rate', 1.0) }}" required>
                            @error('currency_rate')
                                <span class="text-danger d-block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Active Status</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active_yes" value="active"
                                    checked>
                                <label class="form-check-label" for="active_yes">Active</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active_no"
                                    value="inactive">
                                <label class="form-check-label" for="active_no">Inactive</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Is Default?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_default" id="default_yes" value="yes">
                                <label class="form-check-label" for="default_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_default" id="default_no" value="no"
                                    checked>
                                <label class="form-check-label" for="default_no">No</label>
                            </div>
                            <small class="text-muted d-block mt-1">Setting this to 'Yes' will automatically unset any other
                                default currency.</small>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" type="submit">save Currency</button>
                            <a href="{{ route('currency.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
