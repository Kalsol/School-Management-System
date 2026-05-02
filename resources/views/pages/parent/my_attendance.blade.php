@extends('layouts.master')
@section('page_title', 'Attendance Records')
@section('content')

{{-- Filter Section --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3"><i class="icon-filter3 mr-2 text-primary"></i>Filter History</h6>
        <form action="{{ Request::url() }}" method="GET" class="row align-items-end">

            {{-- Student Filter (Only for Parents) --}}
            @if(Qs::userIsParent())
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold small text-uppercase text-muted">Student</label>
                {{-- CHANGE THIS SECTION --}}
                <select name="student_id" class="form-control select shadow-none">
                    <option value="">All Students</option>
                    @foreach($my_children as $child)
                    {{-- Change $child->id to $child->user_id --}}
                    <option value="{{ $child->user_id }}" {{ request('student_id') == $child->user_id ? 'selected' : '' }}>
                        {{ $child->user->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold small text-uppercase text-muted">Status</label>
                <select name="status" class="form-control select shadow-none">
                    <option value="">All Statuses</option>
                    <option {{ request('status') == 'Present' ? 'selected' : '' }} value="Present">Present</option>
                    <option {{ request('status') == 'Absent' ? 'selected' : '' }} value="Absent">Absent</option>
                    <option {{ request('status') == 'Late' ? 'selected' : '' }} value="Late">Late</option>
                </select>
            </div>

            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold small text-uppercase text-muted">From Date</label>
                <input type="date" name="from_date" class="form-control shadow-none" value="{{ request('from_date') }}">
            </div>

            <div class="col-md-2 mb-2 mb-md-0">
                <label class="font-weight-bold small text-uppercase text-muted">To Date</label>
                <input type="date" name="to_date" class="form-control shadow-none" value="{{ request('to_date') }}">
            </div>

            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-block p-2">
                    <i class="icon-search4"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-bold">Attendance History Table</h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr class="bg-light">
                    <th>Date</th>
                    @if(Qs::userIsParent()) <th>Student</th> @endif
                    <th>Time In</th>
                    <th>Status</th>
                    <th>Remarks/Excuse</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $at)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($at->attendance_date)->format('d-M-Y') }}</td>
                    @if(Qs::userIsParent()) <td>{{ $at->user->name }}</td> @endif
                    <td>{{ $at->time_in ? \Carbon\Carbon::parse($at->time_in)->format('h:i A') : 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $at->status == 'Present' ? 'badge-success' : ($at->status == 'Late' ? 'badge-warning' : 'badge-danger') }}">
                            {{ $at->status }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            {{-- Show Type & Remarks --}}
                            @if($at->remarks)
                            <span class="text-dark font-weight-bold small">{{ $at->excuse_type }}</span>
                            <small class="text-muted"><i>{{ \Illuminate\Support\Str::limit($at->remarks, 30) }}</i></small>
                            @else
                            <small class="text-muted">No remarks yet</small>
                            @endif

                            {{-- Trigger for Feedback Modal --}}
                            @if($at->admin_response)
                            <a href="#" class="mt-1 small font-weight-semibold text-primary" data-toggle="modal" data-target="#feedback{{ $at->id }}">
                                <i class="icon-comment-discussion mr-1"></i> View Admin Response
                            </a>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">
                        @if(Qs::userIsParent())

                        {{-- Case 1: Approved --}}
                        @if($at->is_excused)
                        <span class="badge badge-flat border-success text-success-600 px-2">
                            <i class="icon-checkmark4 mr-1"></i> Approved
                        </span>

                        {{-- Case 2: Rejected (Admin responded, but is_excused is false) --}}
                        @elseif($at->admin_id && !$at->is_excused)
                        <span class="badge badge-flat border-danger text-danger-600 px-2">
                            <i class="icon-cross2 mr-1"></i> Rejected
                        </span>
                        <div class="mt-1">
                            <a href="{{ route('attendance.create_excuse', $at->id) }}" class="small text-muted font-weight-bold"><u>Try Again?</u></a>
                        </div>

                        {{-- Case 3: Pending Review --}}
                        @elseif($at->remarks)
                        <span class="badge badge-flat border-info text-info-600 px-2">
                            <i class="icon-spinner2 spinner mr-1"></i> Pending Review
                        </span>

                        {{-- Case 4: Not yet submitted --}}
                        @elseif($at->status != 'Present')
                        <a href="{{ route('attendance.create_excuse', $at->id) }}" class="btn btn-xs btn-primary">
                            Submit Excuse
                        </a>
                        @else
                        <span class="text-muted small">---</span>
                        @endif

                        @endif

                        {{-- ADMIN FEEDBACK MODAL --}}
                        @if($at->admin_response)
                        <div class="modal fade" id="feedback{{ $at->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content text-left">
                                    <div class="modal-header bg-light">
                                        <h6 class="modal-title">Admin Feedback</h6>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="small text-muted mb-2">Processed: {{ \Carbon\Carbon::parse($at->handled_at)->format('d M, Y') }}</p>
                                        <div class="p-2 border-left-warning border-left-3 bg-light">
                                            "{{ $at->admin_response }}"
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection