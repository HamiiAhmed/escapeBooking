@extends('admin.layouts.master')
@section('content')
<style>
    /* Custom checkbox styling */
    .custom-checkbox {
        display: none;
        /* Hide the default checkbox */
    }

    .custom-checkbox+label {
        display: inline-block;
        width: 24px;
        height: 24px;
        cursor: pointer;
        position: relative;
    }

    /* Green tick for checked state */
    .custom-checkbox:checked+label::before {
        content: "\f14a";
        /* FontAwesome square-check */
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 24px;
        color: green;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Red cross for unchecked state */
    .custom-checkbox:not(:checked)+label::before {
        content: "\f2d3";
        /* FontAwesome square-xmark */
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 24px;
        color: red;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
</style>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <!-- <h3 class="mb-0">Roles</h3> -->
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
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
                        <h2 class="card-title">Manage Permissions for Role: <b>{{ $role->title }}</b></h2>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive" style="min-height:50vh;">
                        <form action="{{ route('admin.permissions.update', $role->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th class="text-center">View</th>
                                        <th class="text-center">Create</th>
                                        <th class="text-center">Update</th>
                                        <th class="text-center">Delete</th>
                                        <!-- <th>View Report</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modules as $module)
                                    @php
                                    $permission = $role->permissions->where('module_id', $module->id)->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $module->name }}</td>
                                        <!-- <td class="text-center"><input type="checkbox" name="permissions[{{ $module->id }}][can_view]" value="1" {{ $permission && $permission->can_view ? 'checked' : '' }}></td>
                                        <td class="text-center"><input type="checkbox" name="permissions[{{ $module->id }}][can_create]" value="1" {{ $permission && $permission->can_create ? 'checked' : '' }}></td>
                                        <td class="text-center"><input type="checkbox" name="permissions[{{ $module->id }}][can_update]" value="1" {{ $permission && $permission->can_update ? 'checked' : '' }}></td>
                                        <td class="text-center"><input type="checkbox" name="permissions[{{ $module->id }}][can_delete]" value="1" {{ $permission && $permission->can_delete ? 'checked' : '' }}></td> -->
                                        <td class="text-center">
                                            <input type="checkbox" class="custom-checkbox" id="view_{{ $module->id }}" name="permissions[{{ $module->id }}][can_view]" value="1" {{ $permission && $permission->can_view ? 'checked' : '' }}>
                                            <label for="view_{{ $module->id }}"></label>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="custom-checkbox" id="create_{{ $module->id }}" name="permissions[{{ $module->id }}][can_create]" value="1" {{ $permission && $permission->can_create ? 'checked' : '' }}>
                                            <label for="create_{{ $module->id }}"></label>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="custom-checkbox" id="update_{{ $module->id }}" name="permissions[{{ $module->id }}][can_update]" value="1" {{ $permission && $permission->can_update ? 'checked' : '' }}>
                                            <label for="update_{{ $module->id }}"></label>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="custom-checkbox" id="delete_{{ $module->id }}" name="permissions[{{ $module->id }}][can_delete]" value="1" {{ $permission && $permission->can_delete ? 'checked' : '' }}>
                                            <label for="delete_{{ $module->id }}"></label>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <button type="submit" class="btn bg-maroon float-end">Update Permissions</button>
                        </form>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
</div>

@endsection