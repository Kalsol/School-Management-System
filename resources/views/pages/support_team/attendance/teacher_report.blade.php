@extends('layouts.master')
@section('page_title', 'Teacher Attendance Report')
@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title"><i class="icon-filter3 mr-2"></i> Filter Attendance</h5>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('attendance.teacher_report') }}">
            <div class="row">
                {{-- Date Filter --}}
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="date" class="font-weight-bold">Attendance Date:</label>
                        <input type="date" id="date" name="date" class="form-control" value="{{ $date }}">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="status" class="font-weight-bold">Attendance Status:</label>
                        <select name="status" id="status" class="form-control select">
                            <option value="">All Statuses</option>
                            <option {{ $status == 'Present' ? 'selected' : '' }} value="Present">Present</option>
                            <option {{ $status == 'Late' ? 'selected' : '' }} value="Late">Late</option>
                            <option {{ $status == 'Absent' ? 'selected' : '' }} value="Absent">Absent</option>
                        </select>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="col-md-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-block">
                        Generate <i class="icon-paperplane ml-2"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Data Table Section --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white header-elements-inline">
        <h6 class="card-title font-weight-bold">
            Logs for <span class="text-primary">{{ \Carbon\Carbon::parse($date)->format('d F, Y') }}</span>
            @if($status) | Status: <span class="badge badge-secondary">{{ $status }}</span> @endif
        </h6>
    </div>

    <div class="table-responsive">
        <table class="table datatable-button-html5-columns table-hover">
            <thead class="bg-light">
                <tr>
                    <th>S/N</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Time In</th>
                    <th>Lateness</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $at)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <img src="{{ $at->user->photo }}" class="rounded-circle border" width="38" height="38" onerror="this.src='{{ asset('assets/images/user.png') }}'">
                    </td>
                    <td>
                        <div class="font-weight-semibold">{{ $at->user->name }}</div>
                    </td>
                    <td>{{ $at->time_in ? \Carbon\Carbon::parse($at->time_in)->format('h:i A') : '--:--' }}</td>
                    <td>
                        @if($at->minutes_late > 0)
                        <span class="text-danger font-weight-bold">{{ $at->minutes_late }} mins late</span>
                        @else
                        <span class="text-success small">On Time</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                        $badge = ['Present' => 'success', 'Late' => 'warning', 'Absent' => 'danger'][$at->status] ?? 'secondary';
                        @endphp
                        <span class="badge badge-pill badge-{{ $badge }} px-3">{{ $at->status }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection