@extends('backend.app')

@section('title', 'View Property Inquiry')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Property Inquiry Details</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('inquiry.index') }}">Property Inquiry</a></li>
                <li class="breadcrumb-item active" aria-current="page">View Details</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Inquiry from {{ $data->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $data->name }}</p>
                            <p><strong>Email:</strong> {{ $data->email }}</p>
                            <p><strong>Phone:</strong> {{ $data->phone }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p><strong>Date:</strong> {{ $data->created_at->format('M d, Y H:i A') }}</p>
                            <p><strong>Property:</strong> {{ $data->property->title ?? 'N/A' }} (ID:
                                {{ $data->property_id }})</p>
                            <p><strong>Guests:</strong> {{ $data->no_of_guests }}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Check-in:</strong> {{ $data->checkin_date }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p><strong>Check-out:</strong> {{ $data->checkout_date }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label class="form-label">Message Content:</label>
                        <div class="p-3 bg-light border">
                            {!! nl2br(e($data->message)) !!}
                        </div>
                    </div>

                    @if($data->is_replied == 'yes' && !empty($data->reply))
                        <hr>
                        <div class="form-group">
                            <label class="form-label text-success">Our Previous Reply (Sent on
                                {{ $data->replied_at ? \Carbon\Carbon::parse($data->replied_at)->format('M d, Y H:i A') : ($data->updated_at ? $data->updated_at->format('M d, Y H:i A') : 'N/A') }}):</label>
                            <div class="p-3 bg-success-transparent border border-success ">
                                {!! nl2br(e($data->reply)) !!}
                            </div>
                        </div>
                    @endif

                    <hr>

                    <div class="form-group text-end mt-3">
                        <a href="{{ route('inquiry.index') }}" class="btn btn-secondary me-2">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection