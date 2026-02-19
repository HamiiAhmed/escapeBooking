@extends('admin.layouts.master')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Coupons</li>
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
                            <h4 class="card-title">{{ $title }}</h4>
                            @can('create', $module)
                                <div class="float-end">
                                    <button type="button" class="btn bg-maroon" data-bs-toggle="modal"
                                        data-bs-target="#createCouponModal">
                                        <i class="fas fa-plus"></i> Add New Coupon
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Code</th>
                                            <th>Value</th>
                                            <th>Type</th>
                                            <th>Min Amount</th>
                                            <th>Usage</th>
                                            <th>Valid</th>
                                            <th>Status</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($coupons as $coupon)
                                            <tr>
                                                <td>{{ $coupon->id }}</td>
                                                <td><code>{{ $coupon->code }}</code></td>
                                                <td>{{ number_format($coupon->discount_value, 2) }}</td>
                                                <td>
                                                    @if ($coupon->discount_type == 'fixed')
                                                        <span class="badge bg-info">Fixed</span>
                                                    @else
                                                        <span class="badge bg-warning">Percentage</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($coupon->min_amount, 2) }}</td>
                                                <td>
                                                    {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}
                                                </td>
                                                <td>
                                                    {{ $coupon->start_date->format('d M') }} -
                                                    {{ $coupon->end_date->format('d M Y') }}
                                                </td>
                                                <td>
                                                    @if ($coupon->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $coupon->created_at->format('d M Y') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-coupon-btn"
                                                        data-id="{{ $coupon->id }}" data-code="{{ $coupon->code }}"
                                                        data-type="{{ $coupon->discount_type }}"
                                                        data-value="{{ $coupon->discount_value }}"
                                                        data-min="{{ $coupon->min_amount }}"
                                                        data-limit="{{ $coupon->usage_limit }}"
                                                        data-start="{{ $coupon->start_date->format('Y-m-d') }}"
                                                        data-end="{{ $coupon->end_date->format('Y-m-d') }}"
                                                        data-active="{{ $coupon->is_active }}" data-bs-toggle="modal"
                                                        data-bs-target="#editCouponModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST"
                                                        action="{{ route('admin.coupons.destroy', $coupon) }}"
                                                        class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No coupons found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $coupons->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="createCouponModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Coupon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Coupon Code</label>
                            <input type="text" name="code" class="form-control" required maxlength="20">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Type</label>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" name="discount_type" value="fixed" class="form-check-input"
                                            id="type_fixed" required checked>
                                        <label class="form-check-label" for="type_fixed">Fixed (SAR)</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" name="discount_type" value="percent" class="form-check-input"
                                            id="type_percent">
                                        <label class="form-check-label" for="type_percent">Percent (%)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" step="0.01" class="form-control" required
                                min="0">
                        </div>
                        <!-- Min Amount field -->
                        <div class="mb-3">
                            <label class="form-label">Min Amount (Optional)</label>
                            <input type="number" name="min_amount" step="0.01" class="form-control" min="0"
                                placeholder="0 for no minimum">
                            <div class="form-text">Leave 0 for no minimum requirement</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Usage Limit (Optional)</label>
                            <input type="number" name="usage_limit" class="form-control" min="1">
                            <div class="form-text">Leave empty for unlimited</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn bg-maroon">Save Coupon</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="editCouponModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="" method="POST" id="editCouponForm">
                @csrf @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Coupon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Coupon Code</label>
                            <input type="text" name="code" id="edit_code" class="form-control" required
                                maxlength="20">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Type</label>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" name="discount_type" value="fixed"
                                            class="form-check-input" id="edit_type_fixed" required>
                                        <label class="form-check-label" for="edit_type_fixed">Fixed (Sar)</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" name="discount_type" value="percent"
                                            class="form-check-input" id="edit_type_percent">
                                        <label class="form-check-label" for="edit_type_percent">Percent (%)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" id="edit_value" step="0.01"
                                class="form-control" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Min Amount</label>
                            <input type="number" name="min_amount" id="edit_min" step="0.01" class="form-control"
                                min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usage Limit (Optional)</label>
                            <input type="number" name="usage_limit" id="edit_limit" class="form-control"
                                min="1">
                            <div class="form-text">Leave empty for unlimited</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="edit_start" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" id="edit_end" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                id="edit_active">
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn bg-maroon">Update Coupon</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // ✅ FIXED Edit button - MOST RELIABLE METHOD
            $('.edit-coupon-btn').on('click', function() {
                const id = $(this).data('id');
                const code = $(this).data('code');
                const type = $(this).data('type');
                const value = $(this).data('value');
                const min = $(this).data('min') || 0;
                const limit = $(this).data('limit');
                const start = $(this).data('start');
                const end = $(this).data('end');
                const active = $(this).data('active');

                $('#editCouponForm').attr('action', `/admin/coupons/${id}`);
                $('#edit_id').val(id);
                $('#edit_code').val(code);
                $('#edit_value').val(value);
                $('#edit_min').val(min);
                $('#edit_limit').val(limit || '');
                $('#edit_start').val(start);
                $('#edit_end').val(end);
                $('input[name="discount_type"][value="' + type + '"]').prop('checked', true);
                $('#edit_active').prop('checked', active == 1);
            });
        });
    </script>
@endsection
