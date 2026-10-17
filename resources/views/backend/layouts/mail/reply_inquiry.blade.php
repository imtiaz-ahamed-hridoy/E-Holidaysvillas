<!DOCTYPE html>
<html>

<head>
    <title>Property Inquiry Reply</title>
</head>

<body>
    <h2>Hello {{ $inquiry->name }},</h2>
    <p>Thank you for your inquiry regarding our property.</p>

    <p><strong>Your Message:</strong></p>
    <blockquote style="border-left: 5px solid #ccc; padding-left: 10px; font-style: italic;">
        {{ $inquiry->message }}
    </blockquote>

    <p><strong>Our Reply:</strong></p>
    <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px;">
        {!! nl2br(e($replyMessage)) !!}
    </div>

    <p>Best regards,<br>
        Property Management Team</p>
</body>

</html>