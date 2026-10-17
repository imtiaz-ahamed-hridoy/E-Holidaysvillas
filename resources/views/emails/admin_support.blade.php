<div>
    <h3>New support message #[{{ $support->id }}] - {{ ucfirst($support->subject) }}</h3>
    <p><strong>Name:</strong> {{ $support->name }}</p>
    <p><strong>Email:</strong> {{ $support->email }}</p>
    <p><strong>Subject:</strong> {{ ucfirst($support->subject) }}</p>
    <p>----------------------</p>
    <p>{{ $support->message }}</p>
</div>
