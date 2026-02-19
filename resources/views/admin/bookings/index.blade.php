@extends('admin.layouts.master')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bookings</li>
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
                            <h2 class="card-title">All Bookings</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Time Slot</th>
                                            <th>People</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bookings as $booking)
                                            <tr>
                                                <td>{{ $booking->id }}</td>
                                                <td>
                                                    {{ $booking->customer_name }}<br>
                                                    <small>{{ $booking->customer_phone }}</small>
                                                </td>
                                                <td>{{ $booking->package->name ?? 'N/A' }}</td>
                                                <td>{{ $booking->booking_start_time ?? 'N/A' }}</td>
                                                <td>{{ $booking->people_count }}</td>
                                                <td>{{ number_format($booking->total_amount, 2) }}</td>
                                                <td>
                                                    @switch($booking->status)
                                                        @case('pending')
                                                            <span class="badge bg-warning">Pending</span>
                                                        @break

                                                        @case('paid')
                                                            <span class="badge bg-success">Paid</span>
                                                        @break

                                                        @case('confirmed')
                                                            <span class="badge bg-info">Confirmed</span>
                                                        @break

                                                        @case('cancelled')
                                                            <span class="badge bg-danger">Cancelled</span>
                                                        @break
                                                    @endswitch
                                                </td>
                                                <td>{{ $booking->created_at->format('d M Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                                        class="btn btn-sm btn-info">View</a>
                                                    <form method="POST"
                                                        action="{{ route('admin.bookings.destroy', $booking) }}"
                                                        class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center">No bookings found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $bookings->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
