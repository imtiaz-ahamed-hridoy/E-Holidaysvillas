@extends('backend.app')

@section('title', 'Create Amenity')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title fs-2">Create Amenity</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Amenity</li>
            </ol>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card box-shadow-0">
                <div class="card-header border-bottom"
                    style="margin-bottom: 0; display: flex; justify-content: space-between;">
                    <h3 class="card-title">Amenity Information</h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('amenity.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="title" class="form-label fs-16">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="title"
                                    value="{{ old('title') }}" placeholder="Enter Amenity Title" required>
                                @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="icon_file" class="form-label fs-16">Icon (Image File)</label>
                                <input type="file" class="form-control" name="icon_file" id="icon_file" accept="image/*">
                                <small class="text-muted">Upload an image file OR provide SVG code below.</small>
                                @error('icon_file')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="icon" class="form-label fs-16">Icon (SVG Code/HTML)</label>
                                <textarea class="form-control" name="icon" id="icon" rows="3"
                                    placeholder="Enter SVG Code here">{{ old('icon') }}</textarea>
                                @error('icon')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>



                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary">Save Amenity</button>
                            <a href="{{ route('amenity.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
