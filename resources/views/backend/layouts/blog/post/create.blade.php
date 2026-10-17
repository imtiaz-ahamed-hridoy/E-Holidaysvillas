@extends('backend.app')

@section('title', 'Create Blog Post')

@push('styles')
    <style>
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .image-preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Create Blog Post</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Blog</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Posts</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </div>
    </div>

    <form action="{{ route('blog.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">Post Content</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                                name="title" placeholder="Enter Post Title" value="{{ old('title') }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="summernote" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="summernote"
                                name="content">{{ old('content') }}</textarea>
                            @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">Blog Images <span class="text-muted fs-12">(Multiple)</span></h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="images" class="form-label">Upload Images</label>
                            <input type="file" name="images[]" id="images"
                                class="form-control @error('images.*') is-invalid @enderror" multiple accept="image/*">
                            @error('images.*')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="image-preview-container" id="image-preview"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h3 class="card-title">Post Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="categories" class="form-label">Categories <span class="text-danger">*</span></label>
                            <select name="categories[]" id="categories"
                                class="form-control select2 @error('categories') is-invalid @enderror" multiple required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}" {{ (is_array(old('categories')) && in_array($category->category_id, old('categories'))) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categories')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tags" class="form-label">Tags</label>
                            <select name="tags[]" id="tags" class="form-control select2 @error('tags') is-invalid @enderror"
                                multiple>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->tag_id }}" {{ (is_array(old('tags')) && in_array($tag->tag_id, old('tags'))) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tags')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="approx_read_time" class="form-label">Approx Read Time</label>
                            <input type="text" class="form-control @error('approx_read_time') is-invalid @enderror"
                                id="approx_read_time" name="approx_read_time" placeholder="e.g. 5 min read"
                                value="{{ old('approx_read_time') }}">
                            @error('approx_read_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror"
                                required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100">Publish Post</button>
                            <a href="{{ route('blog.index') }}" class="btn btn-danger w-100 mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "Select options",
                width: '100%'
            });

            // Image Preview
            $('#images').on('change', function () {
                const preview = $('#image-preview');
                preview.empty();
                if (this.files) {
                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            preview.append(`
                                                <div class="image-preview-item">
                                                    <img src="${e.target.result}" alt="Preview">
                                                </div>
                                            `);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });
        });
    </script>
@endpush