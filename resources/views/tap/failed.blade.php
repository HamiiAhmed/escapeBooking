<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Failed - Escape</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .failed-card {
            max-width: 600px;
            margin: 50px auto;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .failed-header {
            background-color: #dc3545;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .failed-header i {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        .failed-header h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .failed-body {
            background: white;
            padding: 30px 25px;
        }
        .error-details {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
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
            text-decoration: none;
            display: inline-block;
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
            text-decoration: none;
            display: inline-block;
        }
        .help-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 25px 0;
            flex-wrap: wrap;
        }
        .help-option {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background-color: #f8f9fa;
            flex: 1;
            min-width: 150px;
        }
        .help-option i {
            font-size: 2rem;
            color: #800000;
            margin-bottom: 10px;
        }
        .help-option h5 {
            font-size: 1rem;
            margin-bottom: 5px;
        }
        .help-option p {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 10px;
        }
        .help-option a {
            color: #800000;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .help-option a:hover {
            text-decoration: underline;
        }
        .footer-note {
            text-align: center;
            margin-top: 25px;
            color: #888;
            font-size: 0.9rem;
        }
        .error-message {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="failed-card">
            <div class="failed-header">
                <i class="fas fa-times-circle"></i>
                <h1>Booking Failed!</h1>
                <p class="mb-0">Your payment could not be processed</p>
            </div>
            <div class="failed-body">
                <!-- Display error message from session if available -->
                @if(session('error'))
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="error-details">
                    <h5><i class="fas fa-info-circle me-2"></i>What went wrong?</h5>
                    <p class="mb-0">The payment was not successful. This could be due to:</p>
                    <ul class="mt-2 mb-0">
                        <li>Insufficient funds in your account</li>
                        <li>Incorrect card details entered</li>
                        <li>Bank authorization failure</li>
                        <li>Payment gateway timeout</li>
                        <li>Transaction cancelled by user</li>
                    </ul>
                </div>


                <div class="text-center mt-4">
                    <a href="{{ route('booking.calendar') }}" class="btn btn-maroon">
                        <i class="fas fa-calendar-plus me-2"></i>Try New Booking
                    </a>
                </div>

                <!-- Transaction reference if available -->
                @if(session('tap_id') || isset($tapId))
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Transaction ID: {{ session('tap_id') ?? $tapId ?? 'N/A' }}
                    </small>
                </div>
                @endif

                <hr class="my-4">

                <div class="text-center">
                    <p class="text-muted mb-0">
                        <i class="fas fa-lock me-1"></i> 
                        Your card has not been charged. If you see a pending charge, 
                        it will be automatically released within 5-7 business days.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>