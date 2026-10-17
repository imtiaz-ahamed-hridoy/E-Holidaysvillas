@extends('backend.app')

@section('title', 'About Us Settings')

@section('content')
    {{-- PAGE-HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">About Us Settings</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">About Us</li>
            </ol>
        </div>
    </div>
    {{-- PAGE-HEADER --}}

    <div class="row">
        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
            <div class="card box-shadow-0">
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="post" action="{{ route('about_us.update') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Hero Section --}}
                        <h4 class="card-title">Hero Section</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Hero Title:</label>
                                    <input type="text" class="form-control" name="hero_title"
                                        value="{{ old('hero_title', optional($aboutUs)->hero_title ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Hero Subtitle:</label>
                                    <input type="text" class="form-control" name="hero_subtitle"
                                        value="{{ old('hero_subtitle', optional($aboutUs)->hero_subtitle ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Hero Image:</label>
                                    <input type="file" class="dropify" name="hero_image"
                                        data-default-file="{{ optional($aboutUs)->hero_image ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Intro Section --}}
                        <h4 class="card-title">Intro Section</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Our Mission:</label>
                                    <textarea class="form-control" name="intro_mission"
                                        rows="4">{{ old('intro_mission', optional($aboutUs)->intro_mission ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Our Vision:</label>
                                    <textarea class="form-control" name="intro_vision"
                                        rows="4">{{ old('intro_vision', optional($aboutUs)->intro_vision ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Our Story:</label>
                                    <textarea class="form-control" name="intro_story"
                                        rows="6">{{ old('intro_story', optional($aboutUs)->intro_story ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Intro Image:</label>
                                    <input type="file" class="dropify" name="intro_image"
                                        data-default-file="{{ optional($aboutUs)->intro_image ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Travel Section --}}
                        <h4 class="card-title">Travel Section</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Title:</label>
                                    <input type="text" class="form-control" name="travel_title"
                                        value="{{ old('travel_title', optional($aboutUs)->travel_title ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Description:</label>
                                    <textarea class="form-control" name="travel_text"
                                        rows="3">{{ old('travel_text', optional($aboutUs)->travel_text ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Travel Image 1:</label>
                                    @php
                                        $travelImages = optional($aboutUs)->travel_images ?? [];
                                    @endphp
                                    <input type="file" class="dropify" name="travel_image_1"
                                        data-default-file="{{ $travelImages[0] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Travel Image 2:</label>
                                    <input type="file" class="dropify" name="travel_image_2"
                                        data-default-file="{{ $travelImages[1] ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Why Choose Us (Repeater-like or fixed 4 items) --}}
                        <h4 class="card-title">Why Choose Us</h4>
                        @php
                            $features = optional($aboutUs)->why_choose_us ?? [];
                            $loops = 4; // Display 4 slots as per design
                        @endphp

                        <div class="row">
                            @for($i = 0; $i < $loops; $i++)
                                <div class="col-md-6 border p-2 mb-2">
                                    <h6>Feature {{ $i + 1 }}</h6>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" class="form-control mb-2" name="why_choose_us[{{ $i }}][title]"
                                            value="{{ $features[$i]['title'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control mb-2" name="why_choose_us[{{ $i }}][description]"
                                            rows="2">{{ $features[$i]['description'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Icon Type</label>
                                        <select class="form-control mb-2 icon-type-select"
                                            name="why_choose_us[{{ $i }}][icon_type]">
                                            <option value="file" {{ ($features[$i]['icon_type'] ?? '') == 'file' ? 'selected' : '' }}>Icon File (Image/SVG)</option>
                                            <option value="html" {{ ($features[$i]['icon_type'] ?? '') == 'html' ? 'selected' : '' }}>Icon HTML (SVG Code/FontAwesome)</option>
                                        </select>
                                    </div>
                                    <div class="form-group icon-file-group"
                                        style="{{ ($features[$i]['icon_type'] ?? 'file') == 'file' ? '' : 'display:none;' }}">
                                        <label>Upload Icon</label>
                                        <input type="file" class="form-control mb-2" name="why_choose_us[{{ $i }}][icon_file]">
                                        @if(($features[$i]['icon_type'] ?? '') == 'file' && !empty($features[$i]['icon_content']))
                                            <div class="mt-2">
                                                <img src="{{ $features[$i]['icon_content'] }}" alt="icon"
                                                    style="height: 40px; background: #eee;">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group icon-html-group"
                                        style="{{ ($features[$i]['icon_type'] ?? '') == 'html' ? '' : 'display:none;' }}">
                                        <label>Icon HTML / SVG Code</label>
                                        <textarea class="form-control mb-2 icon-html-input"
                                            name="why_choose_us[{{ $i }}][icon_html]"
                                            rows="3">{{ ($features[$i]['icon_type'] ?? '') == 'html' ? ($features[$i]['icon_content'] ?? '') : '' }}</textarea>
                                        <div class="mt-2 icon-html-preview"
                                            style="height: 40px; width: 40px; background: #eee; overflow: hidden;">
                                            {!! ($features[$i]['icon_type'] ?? '') == 'html' ? ($features[$i]['icon_content'] ?? '') : '' !!}
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        @push('scripts')
                            <script>
                                $(document).ready(function () {
                                    $('.icon-type-select').on('change', function () {
                                        let container = $(this).closest('.col-md-6');
                                        if ($(this).val() === 'file') {
                                            container.find('.icon-file-group').show();
                                            container.find('.icon-html-group').hide();
                                        } else {
                                            container.find('.icon-file-group').hide();
                                            container.find('.icon-html-group').show();
                                        }
                                    });

                                    $('.icon-html-input').on('input', function () {
                                        let container = $(this).closest('.icon-html-group');
                                        container.find('.icon-html-preview').html($(this).val());
                                    });
                                });
                            </script>
                        @endpush

                        <div class="form-group mt-4">
                            <button class="btn btn-primary" type="submit">Update Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection