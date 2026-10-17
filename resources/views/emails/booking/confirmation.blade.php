<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 20px;
        }

        .content p {
            margin: 0 0 15px;
        }

        .booking-details {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .booking-details h3 {
            margin-top: 0;
            color: #1e3a8a;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
        }

        .detail-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .detail-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #4b5563;
        }

        .detail-value {
            display: table-cell;
            width: 60%;
        }

        .price-summary {
            margin-top: 20px;
            border-top: 2px solid #e5e7eb;
            padding-top: 15px;
        }

        .total-row {
            font-size: 18px;
            font-weight: bold;
            color: #166534;
        }

        .footer {
            background-color: #f3f4f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .btn {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Booking Confirmation</h1>
        </div>

        <div class="content">
            <p>Dear {{ $booking->guest_name }},</p>
            <p>Thank you for choosing <strong>{{ $booking->property->title }}</strong>. We are thrilled to confirm your
                reservation. Your booking details are below.</p>

            <div class="booking-details">
                <h3>Reservation Summary</h3>
                <div class="detail-row">
                    <span class="detail-label">Booking Code:</span>
                    <span class="detail-value">{{ $booking->booking_code }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Property:</span>
                    <span class="detail-value">{{ $booking->property->title }}</span>
                </div>
                @if($booking->property->location)
                    <div class="detail-row">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value">
                            {{ $booking->property->location->city }}, {{ $booking->property->location->country }}
                        </span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Check-in:</span>
                    <span class="detail-value">{{ $booking->check_in->format('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Check-out:</span>
                    <span class="detail-value">{{ $booking->check_out->format('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Guests:</span>
                    <span class="detail-value">
                        {{ $booking->number_adults }} Adults, {{ $booking->number_children }} Children
                    </span>
                </div>

                <div class="price-summary">
                    <div class="detail-row">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value">€{{ number_format($booking->grand_total, 2) }}</span>
                    </div>
                    <div class="detail-row total-row">
                        <span class="detail-label">Balance Due:</span>
                        <span class="detail-value">€{{ number_format($booking->balance_due, 2) }}</span>
                    </div>
                </div>
            </div>

            <p>If you have any questions or require modifications to your booking, please don't hesitate to reply to
                this email or contact our support team.</p>
            <p>We look forward to hosting you!</p>

            <p>Best Regards,<br><strong>Admin Team</strong></p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Booking Service. All rights reserved.
        </div>
    </div>
</body>

</html>
