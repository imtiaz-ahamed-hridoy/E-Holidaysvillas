<div>
    <h3>New inquiry #[{{ $inquiry->inquiry_id }}] - Availability request</h3>
    <p><strong>Listing:</strong> <a href="{{ url('/properties/' . $inquiry->property_id) }}">{{ $property ? $property->title : 'Unknown Property' }}</a></p>
    <p><strong>Name:</strong> {{ $inquiry->name }}</p>
    <p><strong>Phone:</strong> {{ $inquiry->phone }}</p>
    <p><strong>Subject:</strong> Availability request</p>
    <p>----------------------</p>
    <p><strong>Traveling from:</strong> {{ \Carbon\Carbon::parse($inquiry->checkin_date)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($inquiry->checkout_date)->format('d-M-Y') }}</p>
    <p><strong>Guests:</strong> {{ $inquiry->no_of_guests }}</p>
    <p>{{ $inquiry->message }}</p>
</div>
