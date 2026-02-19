@extends('admin.layouts.master')
@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <!-- <h3 class="mb-0">Roles</h3> -->
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="card-title">Users</h2>
                        <div class="float-end">
                            <button class="btn bg-maroon" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive" style="min-height: 50vh;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th width="20%">User Name</th>
                                    <th width="20%">Email</th>
                                    <th width="20%">Role</th>
                                    <th width="10%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($users->isNotEmpty())
                                @foreach ($users as $index => $user)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}.</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->role->title}}</td>
                                    <td class="text-center">
                                        <!-- Dropdown Button -->
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" type="button" id="dropdownMenuButton{{ $user->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $user->id }}">
                                                @if($user->id != 1)
                                                @can('update', $module)
                                                <li>
                                                    <a class="dropdown-item editUser" data-id="{{$user->id}}" href="#">
                                                        <i class="fa-solid fa-edit"></i> Edit
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('delete', $module)
                                                <li>
                                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                                            <i class="fa-solid fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                                @endcan
                                                @else
                                                <li>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fa-solid fa-circle-xmark text-danger"></i> Can't change anything in Main Super Admin
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr class="text-center">
                                    <td colspan="5">No Data Found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <!-- <div class="card-footer clearfix">
                            <ul class="pagination pagination-sm m-0 float-end">
                                <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                            </ul>
                        </div> -->
                </div>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
</div>

<!-- Add Modal -->
<div class="modal fade" id="addUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="inputName" class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="inputName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="inputEmail4" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="inputEmail4" required>
                        </div>
                        <div class="col-md-6">
                            <label for="inputName" class="form-label">Role</label>
                            <select class="form-select" name="role_id" aria-label="select role" required>
                                <option selected>Select Role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" required>
                        </div>
                        <!-- Profile Picture Display & Upload -->
                        <div class="col-md-12 text-center">
                            <label for="profilePic" class="form-label">Upload New Profile Image</label>
                            <input type="file" class="form-control" name="profile_pic" id="profilePic" accept="image/*">
                            <div class="mb-3 mt-3">
                                <img id="profilePreview" src="" alt="Profile Picture" class="img-thumbnail" style="max-width: 150px; display: none;">
                            </div>
                        </div>
                        <p id="errorMessage" style="color: red; display: none;">Passwords do not match!</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-maroon">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="updateName" class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="updateName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="updateEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="updateEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label for="updateRole" class="form-label">Role</label>
                            <select class="form-select" name="role_id" aria-label="select role" id="updateRole" required>
                                <option selected>Select Role</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="updatePassword" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="updatePassword">
                        </div>
                        <div class="col-md-6">
                            <label for="updateConfirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirmPassword" id="updateConfirmPassword">
                        </div>

                        <!-- Profile Picture Display & Upload -->
                        <div class="col-md-12">
                            <label for="updateProfilePic" class="form-label">Upload New Profile Image</label>
                            <input type="file" class="form-control" name="profile_pic" id="updateProfilePic" accept="image/*">
                            <div class="mb-3 mt-3">
                                <p>Current Profile Image</p>
                                <img id="updateProfilePreview" src="" alt="Profile Picture" class="img-thumbnail" style="max-width: 150px; display: none;">
                            </div>
                        </div>

                        <p id="errorMessage" style="color: red; display: none;">Passwords do not match!</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" value="" id="updateId">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-maroon">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#passwordForm').on('submit', function(event) {
            // Get the values of the password fields
            var password = $('#password').val();
            var confirmPassword = $('#confirmPassword').val();

            // Check if passwords match
            if (password !== confirmPassword) {
                // Show error message
                $('#errorMessage').show();
                // Prevent form submission
                event.preventDefault();
            } else {
                // Hide error message if passwords match
                $('#errorMessage').hide();
            }
        });

        // Optional: Real-time validation as the user types
        $('#confirmPassword').on('keyup', function() {
            var password = $('#password').val();
            var confirmPassword = $('#confirmPassword').val();

            if (password !== confirmPassword) {
                $('#errorMessage').show();
            } else {
                $('#errorMessage').hide();
            }
        });
    });

    $('.editUser').click(function(e) {
        var userId = $(this).data('id');
        var url = "{{ route('admin.users.show', ':id') }}";
        url = url.replace(':id', userId);

        e.preventDefault();
        $.ajax({
            type: "GET",
            url: url,
            data: "data",
            dataType: 'json',
            success: function(user) {
                // Set the hidden input values
                $('#updateName').val(user.name);
                $('#updateEmail').val(user.email);
                $('#updateRole').val(user.role_id);

                // Show profile image if available
                if (user.profile_pic) {
                    $('#updateProfilePreview').attr('src', '/images/users/' + user.profile_pic).show();
                } else {
                    $('#updateProfilePreview').hide();
                }

                // Dynamically update the form action URL to include the role id
                var updateUrl = "{{ route('admin.users.update', ':id') }}";
                updateUrl = updateUrl.replace(':id', userId);
                $('#updateModal form').attr('action', updateUrl);
                // Show the update modal
                $('#updateModal').modal('show');
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });

    });

    // Handle image preview when user selects a file
    $('#profilePic').on('change', function() {
        let reader = new FileReader();
        reader.onload = function(e) {
            $('#profilePreview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(this.files[0]);
    });

    $('#updateProfilePic').on('change', function() {
        let reader = new FileReader();
        reader.onload = function(e) {
            $('#updateProfilePreview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(this.files[0]);
    });
</script>
@endsection