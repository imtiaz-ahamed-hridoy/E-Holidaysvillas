@extends('backend.app')

@section('title', 'View Support Contact')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Support Message Details</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('support.contact.index') }}">Support Contact</a></li>
                <li class="breadcrumb-item active" aria-current="page">View Details</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Message from {{ $data->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $data->name }}
                                @if($data->user_id) <span class="badge bg-info ms-2">Registered User</span> @endif
                            </p>
                            <p><strong>Email:</strong> {{ $data->email }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p><strong>Date:</strong> {{ $data->created_at->format('M d, Y H:i A') }}</p>
                            <p><strong>Subject:</strong> {{ $data->subject }}</p>
                            <p><strong>Priority:</strong>
                                @if($data->priority === 'high')
                                    <span class="badge bg-danger">High</span>
                                @elseif($data->priority === 'medium')
                                    <span class="badge bg-warning">Medium</span>
                                @else
                                    <span class="badge bg-info">Low</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label class="form-label">Message Content:</label>
                        <div class="p-3 bg-light border">
                            @if($data->message)
                                {!! nl2br(e($data->message)) !!}
                            @else
                                <span class="text-muted fst-italic">User did not provide a message.</span>
                            @endif
                        </div>
                    </div>

                    @if($data->is_replied)
                        <hr>
                        <div class="form-group">
                            <label class="form-label text-success">Our Previous Reply (Sent on
                                {{ $data->replied_at?->format('M d, Y H:i A') }}):</label>
                            <div class="p-3 bg-success-transparent border border-success ">
                                {!! nl2br(e($data->reply)) !!}
                            </div>
                        </div>
                    @endif

                    <hr>

                    <div class="form-group text-end mt-3">
                        <a href="{{ route('support.contact.index') }}" class="btn btn-secondary me-2">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection