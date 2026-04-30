@extends('layouts.master')
@section('content')

<div class="card border-0 shadow-sm col-md-6 mx-auto">
    <div class="card-header bg-primary text-white">
        <h6 class="card-title">Submit Excuse for {{ $attendance->user->name }}</h6>
    </div>

    <div class="card-body">
        <div class="alert alert-secondary border-0">
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('l, d-M-Y') }} <br>
            <strong>Status:</strong> <span class="badge badge-danger">{{ $attendance->status }}</span>
        </div>

        <form action="{{ route('attendance.submit_excuse', $attendance->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="font-weight-bold">Reason for Absence/Lateness:</label>
                <textarea name="remarks" class="form-control" rows="4" required placeholder="Type your reason here..."></textarea>
            </div>

            <div class="text-right">
                <a href="{{ route('attendance.my_attendance') }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary">Submit Reason</button>
            </div>
        </form>
    </div>
</div>

@endsection