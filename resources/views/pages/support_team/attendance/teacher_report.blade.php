@extends('layouts.master')
@section('page_title', 'Daily Attendance Report')
@section('content')

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold m-0">
                        <i class="icon-filter3 mr-2 text-primary"></i>Filter Attendance
                    </h6>
                    <!--<form action="{{ route('attendance.notify_absentees') }}" method="post">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-success font-weight-bold text-uppercase px-4 shadow-sm">
                            <i class="icon-bell8 mr-1"></i> Send Absent Alerts
                        </button>
                    </form>-->
                </div>
        
                <hr class="mt-0 mb-3 opacity-50">
        
                <form action="{{ route('attendance.student_report') }}" method="GET" class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="my_class_id" class="font-weight-bold small text-uppercase text-muted">Class</label>
                        <select required onchange="getClassData(this.value)" id="my_class_id" name="my_class_id" class="form-control select shadow-none">
                            <option value="">Select Class</option>
                            @foreach($my_classes as $c)
                                <option {{ ($selected && $my_class_id == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
        
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="section_id" class="font-weight-bold small text-uppercase text-muted">Section</label>
                        <select required id="section_id" name="section_id" class="form-control select shadow-none">
                            @if($selected)
                                @foreach($sections->where('my_class_id', $my_class_id) as $s)
                                    <option {{ $section_id == $s->id ? 'selected' : '' }} value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            @else
                                <option value="">Select Class First</option>
                            @endif
                        </select>
                    </div>
        
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="date" class="font-weight-bold small text-uppercase text-muted">Date</label>
                        <input type="date" id="date" name="date" class="form-control shadow-none" value="{{ $date }}">
                    </div>
        
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold text-uppercase">
                            <i class="icon-search4 mr-1"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        {{-- Note: The Table below must be wrapped in <form id="massNotifyForm" ...> --}}
        
        
    </div>

    {{-- Data Table Section --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white header-elements-inline">
            <h6 class="card-title font-weight-bold">
                Attendance Logs for <span class="text-primary">{{ \Carbon\Carbon::parse($date)->format('d F, Y') }}</span>
            </h6>
            <div class="header-elements">
                <span class="badge badge-flat border-grey text-grey-800">{{ count($attendances) }} Records Found</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table datatable-button-html5-columns table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="font-weight-bold">S/N</th>
                        <th class="font-weight-bold">Photo</th>
                        <th class="font-weight-bold">Name</th>
                        <th class="font-weight-bold">ADM No</th>
                        <th class="font-weight-bold">Time In</th>
                        <th class="font-weight-bold">Lateness</th>
                        <th class="font-weight-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($attendances as $at)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <img src="{{ $at->user->photo }}" class="rounded-circle border border-light" width="38" height="38" alt="photo" onerror="this.src='{{ asset('assets/images/user.png') }}'">
                        </td>
                        <td><div class="font-weight-semibold text-dark">{{ $at->user->name }}</div></td>
                        <td><span class="text-muted small">{{ $at->student_record->adm_no ?? 'N/A' }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($at->time_in)->format('h:i A') }}</td>
                        <td>
                            @if($at->minutes_late > 0)
                                <span class="badge badge-light-danger text-danger font-weight-semibold">
                                    {{ $at->minutes_late }} mins late
                                </span>
                            @else
                                <span class="text-success small font-weight-bold"><i class="icon-checkmark4 mr-1"></i>On Time</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($at->status == 'Present')
                                <span class="badge badge-pill badge-success px-3">Present</span>
                            @elseif($at->status == 'Late')
                                <span class="badge badge-pill badge-warning px-3">Late</span>
                            @else
                                <span class="badge badge-pill badge-danger px-3">Absent</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection