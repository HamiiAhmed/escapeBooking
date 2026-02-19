@extends('admin.layouts.master')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Working Hours</li>
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
                                        data-bs-target="#createWorkingHourModal">
                                        <i class="fas fa-plus"></i> Add Working Hours
                                    </button>
                                </div>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Day</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Status</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($workingHours as $workingHour)
                                            <tr>
                                                <td>{{ $workingHour->id }}</td>
                                                <td>
                                                    @switch($workingHour->day_type)
                                                        @case('monday')
                                                            Monday
                                                        @break

                                                        @case('tuesday')
                                                            Tuesday
                                                        @break

                                                        @case('wednesday')
                                                            Wednesday
                                                        @break

                                                        @case('thursday')
                                                            Thursday
                                                        @break

                                                        @case('friday')
                                                            Friday
                                                        @break

                                                        @case('saturday')
                                                            Saturday
                                                        @break

                                                        @case('sunday')
                                                            Sunday
                                                        @break

                                                        @default
                                                            All Days
                                                    @endswitch
                                                </td>
                                                <td>{{ $workingHour->start_time->format('h:i A') }}</td>
                                                <td>{{ $workingHour->end_time->format('h:i A') }}</td>
                                                <td>
                                                    @if ($workingHour->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $workingHour->created_at->format('d M Y') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-hour-btn"
                                                        data-id="{{ $workingHour->id }}"
                                                        data-day="{{ $workingHour->day_type }}"
                                                        data-start="{{ $workingHour->start_time->format('H:i') }}"
                                                        data-end="{{ $workingHour->end_time->format('H:i') }}"
                                                        data-active="{{ $workingHour->is_active }}"\
                                                        data-overnight="{{ $workingHour->is_overnight }}"
                                                        data-bs-toggle="modal" data-bs-target="#editWorkingHourModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST"
                                                        action="{{ route('admin.working-hours.destroy', $workingHour) }}"
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
                                                    <td colspan="7" class="text-center">No working hours found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CREATE MODAL --}}
        <div class="modal fade" id="createWorkingHourModal" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('admin.working-hours.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Working Hours</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        @php
                            $days = [
                                'monday' => 'Monday',
                                'tuesday' => 'Tuesday',
                                'wednesday' => 'Wednesday',
                                'thursday' => 'Thursday',
                                'friday' => 'Friday',
                                'saturday' => 'Saturday',
                                'sunday' => 'Sunday',
                            ];

                            $availableDays = array_diff_key($days, array_flip($addedDays));
                        @endphp
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Day</label>
                                <select name="day_type" class="form-select" required>
                                    @foreach ($availableDays as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if (empty($availableDays))
                                    <div class="alert alert-success mt-3 mb-0">
                                        <i class="fas fa-check-circle"></i>
                                        All days have been configured! You can edit existing entries.
                                    </div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" class="form-control" value="10:00" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control" value="01:00" required>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_overnight" value="1" class="form-check-input" checked>
                                <label class="form-check-label">OverNight</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn bg-maroon">Save Hours</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="editWorkingHourModal" tabindex="-1">
            <div class="modal-dialog">
                <form action="" method="POST" id="editWorkingHourForm">
                    @csrf @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Working Hours</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="mb-3">
                                <label class="form-label">Day</label>
                                <select name="day_type" class="form-select" id="edit_day_type" required>
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                    <option value="sunday">Sunday</option>
                                    <option value="daily">All Days</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" class="form-control" id="edit_start_time" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control" id="edit_end_time" required>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                    id="edit_is_active">
                                <label class="form-check-label">Active</label>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_overnight" value="1" class="form-check-input"
                                    id="edit_is_overnight">
                                <label class="form-check-label">OverNight?</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn bg-maroon">Update Hours</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                // MOST RELIABLE METHOD
                $('.edit-hour-btn').on('click', function() {
                    const id = $(this).data('id');
                    const day = $(this).data('day');
                    const start = $(this).data('start');
                    const end = $(this).data('end');
                    const active = $(this).data('active');
                    const overnight = $(this).data('overnight');

                    // Direct DOM manipulation
                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_day_type').value = day;
                    document.getElementById('edit_start_time').value = start;
                    document.getElementById('edit_end_time').value = end;
                    document.getElementById('edit_is_active').checked = (active == 1);
                    document.getElementById('edit_is_overnight').checked = (overnight == 1);

                    // Form action
                    $('#editWorkingHourForm').attr('action', `/admin/working-hours/${id}`);
                });

            });
        </script>
    @endsection
