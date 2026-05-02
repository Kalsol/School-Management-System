@extends('layouts.master')
@section('page_title', 'Student Attendance Excuse')
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

        <form action="{{ route('attendance.submit_excuse', $attendance->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- New: Excuse Type --}}
            <div class="form-group">
                <label class="font-weight-bold">Type of Excuse:</label>
                <select name="excuse_type" class="form-control select" required>
                    <option value="">Select Type</option>
                    <option value="Medical">Medical (Sickness/Doctor Appointment)</option>
                    <option value="Family Emergency">Family Emergency</option>
                    <option value="Travel">Travel</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Detailed Reason:</label>
                <textarea name="remarks" class="form-control" rows="4" required placeholder="Type your detailed reason here..."></textarea>
            </div>

            {{-- New: Evidence Upload --}}
            <div class="form-group">
                <label class="font-weight-bold">Attachment / Evidence (Optional):</label>
                <input type="file" name="evidence" class="form-control-plaintext border p-1" accept=".pdf,.jpg,.jpeg,.png">
                <small class="text-muted">Upload doctor's note or letter (PDF, JPG, PNG)</small>
            </div>

            <div class="text-right">
                <a href="{{ route('attendance.my_attendance') }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary">Submit Reason</button>
            </div>
        </form>
    </div>
</div>
@endsection