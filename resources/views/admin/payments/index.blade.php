@extends('admin.layouts.master')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>SAR {{ number_format($stats['total_completed'] ?? 0, 2) }}</h3>
                            <p>Completed Payments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>SAR {{ number_format($stats['total_pending'] ?? 0, 2) }}</h3>
                            <p>Pending Payments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $stats['total_failed'] ?? 0 }}</h3>
                            <p>Failed Payments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['today_count'] ?? 0 }}</h3>
                            <p>Today's Transactions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Payments Management</h2>
                            @can('export', $module)
                                <div class="float-end">
                                    <a href="{{ route('admin.payments.export') }}?{{ http_build_query(request()->query()) }}"
                                        class="btn btn-success me-2">
                                        <i class="fas fa-download"></i> Export CSV
                                    </a>
                                </div>
                            @endcan
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="">All Statuses</option>
                                                <option value="pending"
                                                    {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="completed"
                                                    {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                                                </option>
                                                <option value="failed"
                                                    {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                                <option value="refunded"
                                                    {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Date From</label>
                                            <input type="date" name="date_from" class="form-control"
                                                value="{{ request('date_from') }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Date To</label>
                                            <input type="date" name="date_to" class="form-control"
                                                value="{{ request('date_to') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Search</label>
                                            <input type="text" name="search" class="form-control"
                                                placeholder="Transaction ID, Customer name or email..."
                                                value="{{ request('search') }}">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="fas fa-filter"></i> Apply
                                            </button>
                                            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Clear
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Transaction ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Method</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $index => $payment)
                                            <tr>
                                                <td>{{ $payments->firstItem() + $index }}</td>
                                                <td>
                                                    <code>{{ Str::limit($payment->transaction_id ?? 'N/A', 20) }}</code>
                                                </td>
                                                <td>
                                                    <strong>{{ $payment->booking->customer_name ?? 'N/A' }}</strong><br>
                                                    <small>{{ $payment->booking->customer_email ?? '' }}</small>
                                                </td>
                                                <td>
                                                    <strong>SAR {{ number_format($payment->amount, 2) }}</strong>
                                                </td>
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
                                                        class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $payment->payment_method ?? 'Tap' }}</td>
                                                <td>
                                                    <small>{{ $payment->created_at->format('d M Y, H:i') }}</small>
                                                </td>
                                                <td>
                                                    @can('view', $module)
                                                        <a href="{{ route('admin.payments.show', $payment->id) }}"
                                                            class="btn btn-sm btn-info text-white">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endcan

                                                    @can('update', $module)
                                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                            data-bs-target="#updateStatusModal" data-id="{{ $payment->id }}"
                                                            data-status="{{ $payment->status }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">No payments found</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of
                                    {{ $payments->total() }} entries
                                </div>
                                <div>
                                    {{ $payments->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- UPDATE STATUS MODAL -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="updateStatusForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Update Payment Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>Select New Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Updating payment status will also update the associated booking status.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Update Status Modal - populate form
            $('#updateStatusModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const paymentId = button.data('id');
                const currentStatus = button.data('status');

                const modal = $(this);
                modal.find('select[name="status"]').val(currentStatus);
                modal.find('#updateStatusForm').attr('action', '{{ route('admin.payments.index') }}/' +
                    paymentId + '/status');
            });
        });
    </script>
@endsection
