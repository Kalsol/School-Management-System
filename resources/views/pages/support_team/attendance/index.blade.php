@extends('layouts.master')
@section('page_title', 'Biometric Attendance Session')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Attendance Control Center</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-12">
                    <div class="mb-3">
                        
                        <i class="icon-camera icon-4x text-success-400 border-success-400 border-3 rounded-round p-3"></i>
                    </div>
                    <h2 class="font-weight-semibold">Face Recognition is Active</h2>
                    <p class="text-muted mb-4">The system is currently configured to mark students <strong>Present</strong> until 2:20 PM.<br> 
                    After 2:20 PM, students will be automatically marked as <strong>Late</strong>.</p>
                </div>
            </div>
           
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-info border-0 alert-dismissible">
                        <span class="font-weight-semibold">Admin Instructions:</span>
                        <ul class="list list-unstyled mt-2">
                            <li><i class="icon-check-circle2 mr-2"></i> Ensure your IP Webcam is running on the phone.</li>
                            <li><i class="icon-check-circle2 mr-2"></i> Run <code>python studentAttendance.py</code> on your computer.</li>
                            <li><i class="icon-check-circle2 mr-2"></i> Do not close this browser tab during the session.</li>
                        </ul>
                    </div>
                    <!--<div class="text-center mb-4">
                        <form action="{{ route('attendance.start') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="icon-camera icon-1x text-success-400 border-success-400 "></i>
                                
                                Launch Student Attendance Camera
                            </button>
                        </form>   
                    </div>-->
                    
                    <div class="text-center ">
                        <a href="{{ route('attendance.student_report') }}" class="btn btn-primary btn-lg">
                            <i class="icon-stats-bars2 mr-2"></i> View Students Live Attendance Report
                        </a>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('attendance.teacher_report') }}" class="btn btn-primary btn-lg">
                            <i class="icon-stats-bars2 mr-2"></i> View Teachers Live Attendance Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection