@extends('admin.layouts.master')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Booking #{{ $booking->id }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Customer Details</h6>
                                    <p><strong>Name:</strong> {{ $booking->customer_name }}</p>
                                    <p><strong>Phone:</strong> {{ $booking->customer_phone }}</p>
                                    <p><strong>Email:</strong> {{ $booking->customer_email ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Booking Details</h6>
                                    <p><strong>Package:</strong> {{ $booking->package->name ?? 'N/A' }}</p>
                                    <p><strong>Time Slot:</strong> {{ $booking->booking_start_time ?? 'N/A' }}</p>
                                    <p><strong>People:</strong> {{ $booking->people_count }}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Total Amount:</strong> SAR. {{ number_format($booking->total_amount, 2) }}</p>
                                    <p><strong>Payment ID:</strong> {{ $booking->payment_id ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        @switch($booking->status)
                                            @case('pending') <span class="badge bg-warning">Pending</span> @break
                                            @case('paid') <span class="badge bg-success">Paid</span> @break
                                            @case('confirmed') <span class="badge bg-info">Confirmed</span> @break
                                            @case('cancelled') <span class="badge bg-danger">Cancelled</span> @break
                                        @endswitch
                                    </p>
                                    <p><strong>Created:</strong> {{ $booking->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
