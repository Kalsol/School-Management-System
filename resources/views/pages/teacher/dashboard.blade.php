<!-- resources/views/teacher/dashboard.blade.php -->
@extends('layouts.attendance')

@section('title', 'Teacher Dashboard')
@section('content')
<div class="container-fluid">
    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Sessions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todaySessions }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check stat-icon text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Students Present Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $presentToday }}/{{ $totalStudents }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill stat-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Excuses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingExcuses }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history stat-icon text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Attendance %</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $avgAttendance }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up stat-icon text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('teacher.sessions.create') }}" class="btn btn-primary btn-block">
                                <i class="bi bi-plus-circle"></i> New Session
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('teacher.pin.terminal') }}" class="btn btn-success btn-block">
                                <i class="bi bi-keyboard"></i> Open PIN Terminal
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('teacher.attendance.manual') }}" class="btn btn-warning btn-block">
                                <i class="bi bi-pencil"></i> Manual Entry
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('teacher.reports.daily') }}" class="btn btn-info btn-block">
                                <i class="bi bi-file-earmark-text"></i> Daily Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today's Sessions -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Today's Attendance Sessions</h6>
                    <span class="badge bg-primary">{{ now()->format('l, F j, Y') }}</span>
                </div>
                <div class="card-body">
                    @if($todaySessionsList->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No sessions scheduled for today</h5>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Attendance</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaySessionsList as $session)
                                    <tr>
                                        <td>
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - 
                                            {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                        </td>
                                        <td>{{ $session->subject->name }}</td>
                                        <td>{{ $session->classSection->name }}</td>
                                        <td>
                                            @php
                                                $attended = $session->attendances->count();
                                                $total = $session->classSection->students->count();
                                            @endphp
                                            <span class="badge bg-{{ $attended == $total ? 'success' : ($attended > 0 ? 'warning' : 'secondary') }}">
                                                {{ $attended }}/{{ $total }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($session->status == 'ongoing')
                                                <span class="badge bg-success">In Progress</span>
                                            @elseif($session->status == 'scheduled')
                                                @if(now()->format('H:i') >= $session->start_time)
                                                    <span class="badge bg-warning">Ready to Start</span>
                                                @else
                                                    <span class="badge bg-info">Upcoming</span>
                                                @endif
                                            @elseif($session->status == 'completed')
                                                <span class="badge bg-secondary">Completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->status == 'scheduled' && now()->format('H:i') >= $session->start_time)
                                                <button class="btn btn-sm btn-primary start-session" 
                                                        data-session-id="{{ $session->id }}">
                                                    Start Session
                                                </button>
                                            @elseif($session->status == 'ongoing')
                                                <a href="{{ route('teacher.attendance.session', $session->id) }}" 
                                                   class="btn btn-sm btn-success">
                                                    Take Attendance
                                                </a>
                                            @else
                                                <a href="{{ route('teacher.sessions.show', $session->id) }}" 
                                                   class="btn btn-sm btn-outline-secondary">
                                                    View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Upcoming Sessions -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming This Week</h6>
                </div>
                <div class="card-body">
                    <div id="weeklyCalendar">
                        <!-- Calendar will be populated via JavaScript -->
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!--<script>
$(document).ready(function() {
    // Start Session
    $('.start-session').click(function() {
        const sessionId = $(this).data('session-id');
        
        $.ajax({
            url: '/api/teacher/sessions/' + sessionId + '/start',
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    alert('Session started successfully!');
                    location.reload();
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Error starting session');
            }
        });
    });
    
    // Load Weekly Calendar
    function loadWeeklyCalendar() {
        $.get('/api/teacher/weekly-schedule', function(data) {
            $('#weeklyCalendar').html(data.html);
        });
    }
    
    // Initialize Chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Excused'],
            datasets: [{
                data: [{{ $stats['present'] }}, {{ $stats['absent'] }}, 
                       {{ $stats['late'] }}, {{ $stats['excused'] }}],
                backgroundColor: [
                    '#1cc88a',
                    '#e74a3b',
                    '#f6c23e',
                    '#36b9cc'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Load initial data
    loadWeeklyCalendar();
});
</script>-->
@endpush