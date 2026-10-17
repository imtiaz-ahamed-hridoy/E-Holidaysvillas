<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
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
            background-color: #166534;
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
            color: #166534;
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
            background-color: #ecfdf5;
            padding: 15px;
            border-radius: 5px;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Payment Receipt</h1>
        </div>

        <div class="content">
            <p>Dear {{ $booking->guest_name }},</p>
            <p>We have successfully received your payment for your upcoming stay at
                <strong>{{ $booking->property->title }}</strong>.</p>

            <div class="booking-details">
                <h3>Payment Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Receipt Amount:</span>
                    <span class="detail-value"
                        style="font-size: 18px; font-weight: bold; color: #166534;">€{{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date Paid:</span>
                    <span class="detail-value">{{ $payment->payment_date->format('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Booking Code:</span>
                    <span class="detail-value">{{ $booking->booking_code }}</span>
                </div>
            </div>

            <div class="price-summary">
                <div class="detail-row">
                    <span class="detail-label">Total Booking Amount:</span>
                    <span class="detail-value">€{{ number_format($booking->grand_total, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Paid So Far:</span>
                    <span class="detail-value">€{{ number_format($booking->total_paid, 2) }}</span>
                </div>
                <div class="detail-row total-row"
                    style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #166534;">
                    <span class="detail-label">Remaining Balance:</span>
                    <span class="detail-value">€{{ number_format($booking->balance_due, 2) }}</span>
                </div>
            </div>

            <p style="margin-top: 20px;">Thank you for your prompt payment.</p>
            <p>Best Regards,<br><strong>Admin Team</strong></p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Booking Service. All rights reserved.
        </div>
    </div>
</body>

</html>
