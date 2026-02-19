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
                        <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
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
                            <h2 class="card-title">Update Profile</h2>
                        </div>
                        <!-- /.card-header -->
                        <form action="{{ route('admin.updateProfile') }}" method="POST" id="updateProfile" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="Name" class="form-label">Name</label>
                                        <input type="text" class="form-control" value="{{ $user->name }}"
                                            name="name" id="Name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="Email" class="form-label">Email</label>
                                        <input type="email" class="form-control" value="{{ $user->email }}"
                                            name="email" id="Email" required>
                                    </div>
                                    <div class="col-md-12">
                                        <h5>Update Password:</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="oldPassword" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="oldPassword" id="oldPassword">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="password" id="password">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="confirmPassword"
                                            id="confirmPassword">
                                        <p id="errorMessage" style="color: red; display: none;">Passwords do not match!</p>
                                    </div>

                                    <!-- Profile Picture Display & Upload -->
                                    <div class="col-md-12">
                                        <div class="mb-3 mt-3 text-center">
                                            <p>Current Profile Image</p>
                                            <img id="profilePreview"
                                                src="{{ asset('images/users/' . $user->profile_pic) }}"
                                                alt="Profile Picture" class="img-thumbnail" style="max-width: 250px;">
                                        </div>
                                        <label for="profilePic" class="form-label">Upload New Profile Image</label>
                                        <input type="file" class="form-control" name="profile_pic" id="profilePic"
                                            accept="image/*">
                                    </div>

                                </div>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer clearfix">
                                <input type="hidden" value="" id="updateId">
                                <button type="button" id="updateProfileSubmit" class="btn bg-maroon  float-end">Update
                                    Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>


    <script>
        $(document).ready(function() {
            $('#updateProfileSubmit').click(function(e) {
                e.preventDefault();

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
                    $('#updateProfile').submit();
                }
            });

            // Optional: Real-time validation as the user types
            $('#password').keyup(function(e) {
                var password = $('#password').val();
                var confirmPassword = $('#confirmPassword').val();

                if (confirmPassword != '' && password !== confirmPassword) {
                    $('#errorMessage').show();
                } else {
                    $('#errorMessage').hide();
                }
            });

            $('#confirmPassword').keyup(function(e) {
                var password = $('#password').val();
                var confirmPassword = $('#confirmPassword').val();

                if (password !== confirmPassword) {
                    $('#errorMessage').show();
                } else {
                    $('#errorMessage').hide();
                }
            });

            // Handle image preview when user selects a file
            $('#profilePic').on('change', function() {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#profilePreview').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            });
        });
    </script>
@endsection
