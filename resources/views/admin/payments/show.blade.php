@extends('admin.layouts.master')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Payment Details #{{ $payment->id }}</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payment #{{ $payment->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <!-- Payment Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-credit-card me-2"></i>Payment Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Payment ID:</th>
                                    <td><strong>#{{ $payment->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Transaction ID:</th>
                                    <td>
                                        <code>{{ $payment->transaction_id ?? 'N/A' }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td>
                                        <h4 class="text-primary mb-0">SAR {{ number_format($payment->amount, 2) }}</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Currency:</th>
                                    <td>{{ $payment->currency }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                'refunded' => 'info',
                                            ];
                                        @endphp
                                        <span
                                            class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }} p-2 fs-6">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ $payment->payment_method ?? 'Tap' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $payment->created_at->format('F d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $payment->updated_at->diffForHumans() }}</td>
                                </tr>
                            </table>

                            <!-- Card Details if available -->
                            @if (isset($payment->metadata['card_brand']) || isset($payment->metadata['card_last4']))
                                <hr>
                                <h6 class="mb-3">Card Details</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="150">Card Brand:</th>
                                        <td>{{ $payment->metadata['card_brand'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Card Number:</th>
                                        <td>•••• {{ $payment->metadata['card_last4'] ?? '****' }}</td>
                                    </tr>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Booking Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>Associated Booking
                            </h5>
                        </div>
                        <div class="card-body">
                            @if ($payment->booking)
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="150">Booking ID:</th>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $payment->booking->id) }}"
                                                class="fw-bold">
                                                #{{ $payment->booking->id }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Customer:</th>
                                        <td>
                                            <strong>{{ $payment->booking->customer_name }}</strong><br>
                                            <small>{{ $payment->booking->customer_email }}</small><br>
                                            <small>{{ $payment->booking->customer_phone }}</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Package:</th>
                                        <td>{{ $payment->booking->package->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date & Time:</th>
                                        <td>{{ \Carbon\Carbon::parse($payment->booking->booking_start_time)->format('F d, Y h:i A') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>People Count:</th>
                                        <td>{{ $payment->booking->people_count }}</td>
                                    </tr>
                                    <tr>
                                        <th>Booking Status:</th>
                                        <td>
                                            <span
                                                class="badge bg-{{ $payment->booking->status === 'paid' ? 'success' : 'warning' }}">
                                                {{ ucfirst($payment->booking->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No associated booking found</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata / Raw Response -->
            @if ($payment->metadata)
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-code me-2"></i>Payment Metadata
                                </h5>
                            </div>
                            <div class="card-body">
                                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow: auto;"><code>{{ json_encode($payment->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h5 class="card-title mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Update Payment Status</h6>
                                    <form action="{{ route('admin.payments.update-status', $payment->id) }}" method="POST"
                                        class="row g-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="col-md-6">
                                            <select name="status" class="form-select">
                                                <option value="pending"
                                                    {{ $payment->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="completed"
                                                    {{ $payment->status === 'completed' ? 'selected' : '' }}>Completed
                                                </option>
                                                <option value="failed"
                                                    {{ $payment->status === 'failed' ? 'selected' : '' }}>Failed</option>
                                                <option value="refunded"
                                                    {{ $payment->status === 'refunded' ? 'selected' : '' }}>Refunded
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-save me-2"></i>Update
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to List
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
