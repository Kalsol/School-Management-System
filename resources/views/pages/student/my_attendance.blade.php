@extends('layouts.master')
@section('page_title', 'Attendance Records')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Attendance History</h6>
    </div>

    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    @if(Qs::userIsParent()) <th>Student</th> @endif
                    <th>Time In</th>
                    <th>Status</th>
                    <th>Remarks/Excuse</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $at)
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
                        <i>{{ $at->remarks ?? 'No remarks yet' }}</i>
                        @if($at->is_excused) 
                            <span class="text-success"><i class="icon-checkmark4 ml-1"></i> (Approved)</span> 
                        @endif
                    </td>
                    <td>
                        @if(Qs::userIsParent() && !$at->is_excused && $at->status != 'Present')
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#excuseModal{{ $at->id }}">
                            Submit Excuse
                        </button>

                        <div class="modal fade" id="excuseModal{{ $at->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <form action="{{ route('attendance.submit_excuse', $at->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reason for Absence/Lateness</h5>
                                        </div>
                                        <div class="modal-body">
                                            <textarea name="remarks" class="form-control" placeholder="Explain why the student was late or absent..." required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Submit Reason</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection