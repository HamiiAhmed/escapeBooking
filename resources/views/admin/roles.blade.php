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
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
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
                        <h2 class="card-title">Roles</h2>
                        @can('create', $module)
                        <div class="float-end">
                            <button class="btn bg-maroon" data-bs-toggle="modal" data-bs-target="#addRoleModal">Add Role</button>
                        </div>
                        @endcan
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body" style="min-height:50vh;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%">#</th>
                                    <th style="width: 65%">Role Title</th>
                                    <th class="text-center" style="width: 15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $index => $role)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}.</td>
                                    <td>{{$role->title}}</td>
                                    <td class="text-center">
                                        <!-- Dropdown Button -->
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" type="button" id="dropdownMenuButton{{ $role->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $role->id }}">
                                                @if($role->id != 1)
                                                @can('create', $module)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.permissions.edit', $role->id) }}">
                                                        <i class="fa-solid fa-user-lock"></i> Permissions
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('update', $module)
                                                <li>
                                                    <a class="dropdown-item editRole" data-id="{{$role->id}}" href="#">
                                                        <i class="fa-solid fa-edit"></i> Edit
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('delete', $module)
                                                <li>
                                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this role?')">
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

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleTitle" class="form-label">Add Role Title</label>
                        <input type="text" class="form-control" id="roleTitle" name="title" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-maroon">Add Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Role Modal -->
<div class="modal fade" id="updateModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="updateRoleTitle" class="form-label">Role Title</label>
                        <input type="text" class="form-control" id="updateRoleTitle" name="title" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" value="" id="updateRoleId">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-maroon">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('.editRole').click(function(e) {
        var roleId = $(this).data('id');
        var url = "{{ route('admin.roles.show', ':id') }}";
        url = url.replace(':id', roleId);

        e.preventDefault();
        $.ajax({
            type: "GET",
            url: url,
            data: "data",
            dataType: 'json',
            success: function(response) {
                // Set the hidden input values
                $('#updateRoleId').val(response.id);

                $('#updateRoleTitle').val(response.title);

                // Dynamically update the form action URL to include the role id
                var updateUrl = "{{ route('admin.roles.update', ':id') }}";
                updateUrl = updateUrl.replace(':id', roleId);
                $('#updateModal form').attr('action', updateUrl);
                // Show the update modal
                $('#updateModal').modal('show');
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });

    });
</script>
@endsection