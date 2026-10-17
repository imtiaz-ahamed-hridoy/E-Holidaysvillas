<!DOCTYPE html>
<html>

<head>
    <title>Support Reply</title>
</head>

<body>
    <h2>Hello {{ $contact->name }},</h2>
    <p>Thank you for contacting us regarding: <strong>{{ $contact->subject }}</strong></p>

    <p><strong>Your Message:</strong></p>
    <blockquote style="border-left: 5px solid #ccc; padding-left: 10px; font-style: italic;">
        {{ $contact->message }}
    </blockquote>

    <p><strong>Our Reply:</strong></p>
    <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px;">
        {!! nl2br(e($replyMessage)) !!}
    </div>

    <p>Best regards,<br>
        Support Team</p>
</body>

</html>