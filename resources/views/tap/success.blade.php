<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Escape</title>
    <!-- Bootstrap CSS (adjust path if needed) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .success-card {
            max-width: 600px;
            margin: 50px auto;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .success-header {
            background-color: #28a745;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .success-header i {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        .success-header h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .success-body {
            background: white;
            padding: 30px 25px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .btn-maroon {
            background-color: #800000;
            color: white;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            transition: all 0.3s;
        }
        .btn-maroon:hover {
            background-color: #a00000;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128,0,0,0.3);
        }
        .btn-outline-secondary {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 500;
        }
        .footer-note {
            text-align: center;
            margin-top: 25px;
            color: #888;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card">
            <div class="success-header">
                <i class="fas fa-check-circle"></i>
                <h1>Booking Confirmed!</h1>
                <p class="mb-0">Your payment was successful</p>
            </div>
            <div class="success-body">
                <h5 class="text-center mb-4" style="color:#28a745;">
                    <i class="fas fa-receipt me-2"></i>Booking Summary
                </h5>

                <!-- Dynamic details – replace with your actual data -->
                <div class="detail-row">
                    <span class="detail-label">Transaction ID</span>
                    <span class="detail-value">{{ $transactionId ?? 'TAP123456789' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Package</span>
                    <span class="detail-value">{{ $packageName ?? 'Adventure Package' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date & Time</span>
                    <span class="detail-value">{{ $bookingDate ?? '2026-02-20 10:00 AM' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Number of People</span>
                    <span class="detail-value">{{ $numPeople ?? '2' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value fw-bold" style="color:#28a745;">SAR {{ $totalAmount ?? '350.00' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value">Tap (via Card)</span>
                </div>

                <hr class="my-4">

                <div class="text-center">
                    <p class="text-muted">We've sent a confirmation email to <strong>{{ $customerEmail ?? 'you@example.com' }}</strong></p>
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="{{ route('booking.calendar') }}" class="btn btn-maroon">
                            <i class="fas fa-calendar-plus me-2"></i>New Booking
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-note">
            <i class="fas fa-lock me-1"></i> Secure payment processed by Tap Payments
        </div>
    </div>

    <!-- Optional Bootstrap JS (for any interactive elements) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>