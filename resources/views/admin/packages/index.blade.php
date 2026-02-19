@extends('admin.layouts.master')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Packages</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Packages Management</h2>
                            @can('create', $module)
                                <div class="float-end">
                                    <button type="button" class="btn bg-maroon" data-bs-toggle="modal"
                                        data-bs-target="#createPackageModal">
                                        <i class="fas fa-plus"></i> Add New Package
                                    </button>
                                </div>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Duration</th>
                                            <th>Min/Max Bookings</th
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($packages as $index => $package)
                                            <tr>
                                                <td>{{ $packages->firstItem() + $index }}</td>
                                                <td>
                                                    {{-- @if ($package->image)
                                                        <img src="{{ asset('storage/' . $package->image) }}"
                                                            class="img-thumbnail" style="width: 50px;">
                                                    @endif --}}
                                                    {{ $package->name }}
                                                </td>
                                                <td>SAR. {{ number_format($package->price, 2) }}</td>
                                                <td>{{ $package->duration_minutes }} mins</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $package->min_bookings }}</span> /
                                                    <span class="badge bg-success">{{ $package->max_bookings }}</span>
                                                </td>
                                                <td>
                                                    @if ($package->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-warning">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('update', $module)
                                                        <button class="edit-package-btn btn btn-sm btn-warning"
                                                            data-id="{{ $package->id }}" data-name="{{ $package->name }}"
                                                            data-price="{{ $package->price }}"
                                                            data-duration="{{ $package->duration_minutes }}"
                                                            data-min="{{ $package->min_bookings }}"
                                                            data-max="{{ $package->max_bookings }}"
                                                            data-description="{{ $package->description }}"
                                                            data-image="{{ $package->image }}"
                                                            data-active="{{ $package->is_active }}" data-bs-toggle="modal"
                                                            data-bs-target="#editPackageModal">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endcan

                                                    @can('delete', $module)
                                                        <form action="{{ route('admin.packages.destroy', $package) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Delete this package?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No packages found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $packages->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CREATE MODAL -->
    <div class="modal fade" id="createPackageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.packages.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-maroon">
                        <h5 class="modal-title text-white">Add New Package</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Price (SAR) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="price"
                                        class="form-control @error('price') is-invalid @enderror"
                                        value="{{ old('price') }}" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Duration (mins) <span class="text-danger">*</span></label>
                                    <input type="number" name="duration_minutes"
                                        class="form-control @error('duration_minutes') is-invalid @enderror"
                                        value="{{ old('duration_minutes', 60) }}" required>
                                    @error('duration_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Min Bookings <span class="text-danger">*</span></label>
                                    <input type="number" name="min_bookings"
                                        class="form-control @error('min_bookings') is-invalid @enderror"
                                        value="{{ old('min_bookings', 1) }}" required>
                                    @error('min_bookings')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Max Bookings <span class="text-danger">*</span></label>
                                    <input type="number" name="max_bookings"
                                        class="form-control @error('max_bookings') is-invalid @enderror"
                                        value="{{ old('max_bookings', 10) }}" required>
                                    @error('max_bookings')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Description</label>
                                    <textarea name="description"
                                        class="form-control package-description-editor @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Image</label>
                                    <input type="file" name="image"
                                        class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                        id="is_active_create" checked>
                                    <label class="form-check-label" for="is_active_create">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-maroon">Create Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade" id="editPackageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editPackageForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Package</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Price (SAR) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="price" id="edit_price"
                                        class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Duration (mins) <span class="text-danger">*</span></label>
                                    <input type="number" name="duration_minutes" id="edit_duration"
                                        class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Min Bookings <span class="text-danger">*</span></label>
                                    <input type="number" name="min_bookings" id="edit_min" class="form-control"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Max Bookings <span class="text-danger">*</span></label>
                                    <input type="number" name="max_bookings" id="edit_max" class="form-control"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Description</label>
                                    <textarea name="description" id="edit_description"
                                        class="form-control package-description-editor @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Current Image</label>
                                    <div id="current_image_preview" class="mb-2">
                                    </div>
                                    <label>New Image (optional)</label>
                                    <input type="file" name="image" id="edit_image" class="form-control"
                                        accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>
                            </div>


                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                        id="edit_active" checked> 
                                    <label class="form-check-label" for="edit_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Existing TinyMCE script already present -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.0/tinymce.min.js"></script>


    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '.package-description-editor',
            height: 200,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'anchor', 'searchreplace', 'visualblocks',
                'code', 'fullscreen', 'insertdatetime', 'media', 'table',
                'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic forecolor | ' +
                'bullist numlist outdent indent | removeformat | ' +
                'table link image | code fullscreen',
            content_style: 'body { font-family:Inter,Arial,sans-serif; font-size:14px }',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        $(document).ready(function() {
            // Edit button click - populate modal
            $('.edit-package-btn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const price = $(this).data('price');
                const duration = $(this).data('duration');
                const min = $(this).data('min');
                const max = $(this).data('max');
                const description = $(this).data('description');
                const image = $(this).data('image');
                const active = $(this).data('active');

                // Fill all fields
                $('#edit_name').val(name);
                $('#edit_price').val(price);
                $('#edit_duration').val(duration);
                $('#edit_min').val(min);
                $('#edit_max').val(max);

                // ✅ TINYMCE UPDATE FIX
                tinymce.get('edit_description').setContent(description || '');

                $('#edit_active').prop('checked', active == 1);

                // Image preview
                if (image) {
                    $('#current_image_preview').html(
                        `<img src="{{ asset('storage/') }}/${image}" class="img-thumbnail" style="max-width: 150px;">`
                    );
                } else {
                    $('#current_image_preview').html('<span class="text-muted">No image</span>');
                }

                $('#editPackageForm').attr('action', '{{ route('admin.packages.index') }}/' + id);
            });

            // ✅ FORM SUBMIT - SYNC TINYMCE BEFORE SUBMIT
            $('#editPackageForm').on('submit', function() {
                tinymce.triggerSave();
            });

            $('#createPackageModal form').on('submit', function() {
                tinymce.triggerSave();
            });
        });
    </script>
@endsection
