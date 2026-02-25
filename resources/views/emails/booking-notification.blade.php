<!DOCTYPE html>
<html>

<head>
    <title>Booking Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }

        .booking-details {
            margin: 20px 0;
        }

        .detail-row {
            margin-bottom: 10px;
        }

        .label {
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>
                @if ($recipientType === 'customer')
                    Booking Confirmation
                @else
                    New Booking Notification
                @endif
            </h1>
        </div>

        <div class="content">
            @if ($recipientType === 'customer')
                <p>Dear {{ $booking->customer_name }},</p>
                <p>Thank you for your booking! Here are your booking details:</p>
            @else
                <p>Dear Owner,</p>
                <p>A new booking has been made. Here are the details:</p>
            @endif

            <div class="booking-details">
                <div class="detail-row">
                    <span class="label">Package:</span>
                    <span>{{ $booking->package->name ?? 'N/A' }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Customer Name:</span>
                    <span>{{ $booking->customer_name }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Customer Email:</span>
                    <span>{{ $booking->customer_email }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Customer Phone:</span>
                    <span>{{ $booking->customer_phone ?? 'N/A' }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Booking Date:</span>
                    <span>{{ $booking->booking_start_time->format('F j, Y') }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Booking Time:</span>
                    <span>{{ $booking->booking_start_time->format('g:i A') }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Number of People:</span>
                    <span>{{ $booking->people_count ?? '1' }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Total Amount:</span>
                    <span>${{ number_format($booking->total_amount, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="label">Status:</span>
                    <span>{{ ucfirst($booking->status) }}</span>
                </div>

                @if ($booking->notes)
                    <div class="detail-row">
                        <span class="label">Notes:</span>
                        <span>{{ $booking->notes }}</span>
                    </div>
                @endif
            </div>

            @if ($recipientType === 'customer')
                <p>If you need to make any changes to your booking, please contact us.</p>
            @else
                <p>Please review this booking in your dashboard.</p>
            @endif
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Escape. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
