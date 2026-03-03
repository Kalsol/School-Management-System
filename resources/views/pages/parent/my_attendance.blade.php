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
                <select name="student_id" class="form-control select shadow-none">
                    <option value="">All Students</option>
                    @foreach($my_children as $child)
                        <option value="{{ $child->id }}" {{ request('student_id') == $child->id ? 'selected' : '' }}>{{ $child->user->name }}</option>
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
                        <small><i>{{ $at->remarks ?? 'No remarks yet' }}</i></small>
                        @if($at->is_excused) 
                            <span class="text-success small d-block"><i class="icon-checkmark4 mr-1"></i>Approved</span> 
                        @endif
                    </td>
                    <td class="text-center">
                        @if(Qs::userIsParent() && !$at->is_excused && $at->status != 'Present')
                        <button type="button" class="btn btn-sm btn-light border" data-toggle="modal" data-target="#excuseModal{{ $at->id }}">
                            <i class="icon-paperplane mr-1"></i> Submit Excuse
                        </button>
                        {{-- Modal code remains the same --}}
                        <div class="modal fade" id="excuseModal{{ $at->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <form action="{{ route('attendance.submit_excuse', $at->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content text-left">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Reason for Absence/Lateness</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="font-weight-semibold">Explain why the student was late or absent:</label>
                                                <textarea name="remarks" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary px-4">Submit Reason</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No attendance records found for the selected criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection