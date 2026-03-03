<!-- resources/views/teacher/pin-terminal.blade.php -->
@extends('layouts.attendance')

@section('title', 'PIN Attendance Terminal')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-keyboard"></i> Attendance Terminal
                    </h5>
                    <div class="terminal-status">
                        <span id="connectionStatus" class="badge bg-success">
                            <i class="bi bi-wifi"></i> Connected
                        </span>
                        <span class="badge bg-info ms-2">
                            <i class="bi bi-clock"></i> {{ now()->format('h:i:s A') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Terminal Screen -->
                    <div class="terminal-screen mb-4" id="terminalScreen">
                        <div class="terminal-content">
                            <div class="text-center mb-3">
                                <h4 class="text-success">SCHOOL ATTENDANCE SYSTEM</h4>
                                <hr class="bg-success">
                            </div>
                            
                            <!-- Session Info -->
                            <div id="sessionInfo" class="mb-4">
                                <div class="text-center">
                                    <h5 class="text-warning blink">NO ACTIVE SESSION</h5>
                                    <p>Please select a session to begin</p>
                                </div>
                            </div>
                            
                            <!-- PIN Input -->
                            <div id="pinInputSection" class="text-center" style="display: none;">
                                <p class="mb-2">ENTER YOUR 6-DIGIT PIN</p>
                                <div class="mb-3">
                                    <input type="password" id="pinInput" class="form-control-lg text-center terminal-input" 
                                           maxlength="6" placeholder="------" autocomplete="off" readonly
                                           style="font-size: 2rem; letter-spacing: 10px; background: #000; color: #0f0; border: 2px solid #0f0;">
                                </div>
                                <div id="pinDots" class="mb-3">
                                    <span class="pin-dot" data-position="1">_</span>
                                    <span class="pin-dot" data-position="2">_</span>
                                    <span class="pin-dot" data-position="3">_</span>
                                    <span class="pin-dot" data-position="4">_</span>
                                    <span class="pin-dot" data-position="5">_</span>
                                    <span class="pin-dot" data-position="6">_</span>
                                </div>
                                <p id="inputMessage" class="text-info"></p>
                            </div>
                            
                            <!-- Result Display -->
                            <div id="resultDisplay" class="text-center" style="display: none;">
                                <div id="successResult" style="display: none;">
                                    <h4 class="text-success">✓ ATTENDANCE RECORDED</h4>
                                    <p id="studentInfo"></p>
                                    <p id="attendanceTime"></p>
                                </div>
                                <div id="errorResult" style="display: none;">
                                    <h4 class="text-danger">✗ ERROR</h4>
                                    <p id="errorMessage"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Keypad -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="keypad">
                                        <div class="row mb-2">
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="1">
                                                    <h2>1</h2>
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="2">
                                                    <h2>2</h2>
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="3">
                                                    <h2>3</h2>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="4">
                                                    <h2>4</h2>
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="5">
                                                    <h2>5</h2>
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="6">
                                                    <h2>6</h2>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="7">
                                                    <h2>7</h2>
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="8">
                                                    <h2>8</h2>
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="9">
                                                    <h2>9</h2>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4">
                                                <button class="btn btn-outline-secondary" id="btnClear">
                                                    <i class="bi bi-backspace"></i> Clear
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-outline-primary btn-keypad" data-key="0">
                                                    <h2>0</h2>
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-outline-success" id="btnSubmit">
                                                    <i class="bi bi-check-circle"></i> Submit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Session Control Panel -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-calendar-check"></i> Session Control
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Active Sessions -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-3">Select Active Session</h6>
                        <div id="sessionList">
                            @foreach($activeSessions as $session)
                            <div class="card session-card mb-2" data-session-id="{{ $session->id }}">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $session->subject->name }}</h6>
                                            <small class="text-muted">
                                                {{ $session->classSection->name }} | 
                                                {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                                            </small>
                                        </div>
                                        <button class="btn btn-sm btn-primary select-session">
                                            Select
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            
                            @if($activeSessions->isEmpty())
                            <div class="text-center py-3">
                                <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2">No active sessions</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Current Session Info -->
                    <div class="card border-primary" id="currentSessionCard" style="display: none;">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-primary">Current Session</h6>
                            <div id="currentSessionInfo">
                                <!-- Will be populated by JavaScript -->
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-danger" id="btnEndSession">
                                    <i class="bi bi-stop-circle"></i> End Session
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" id="btnChangeSession">
                                    Change Session
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Stats -->
                    <div class="mt-4">
                        <h6 class="font-weight-bold mb-3">Attendance Statistics</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <h4 id="presentCount">0</h4>
                                        <small class="text-success">Present</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <h4 id="absentCount">0</h4>
                                        <small class="text-danger">Absent</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            <small class="text-muted" id="attendanceProgress">Total: 0/0 (0%)</small>
                            <div class="progress mt-1" style="height: 5px;">
                                <div id="attendanceBar" class="progress-bar bg-success" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="mt-4">
                        <button class="btn btn-outline-secondary btn-sm w-100 mb-2" id="btnRefresh">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                        <button class="btn btn-outline-info btn-sm w-100" id="btnTestMode">
                            <i class="bi bi-bug"></i> Test Mode
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-keypad {
        width: 100%;
        height: 80px;
        font-size: 1.5rem;
    }
    
    .terminal-input:focus {
        box-shadow: none;
        border-color: #0f0;
    }
    
    .pin-dot {
        display: inline-block;
        width: 30px;
        height: 30px;
        margin: 0 5px;
        font-size: 1.5rem;
        color: #0f0;
        text-align: center;
        border-bottom: 2px solid #0f0;
    }
    
    .pin-dot.filled::after {
        content: "•";
    }
    
    .session-card {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .session-card:hover {
        background-color: #f8f9fa;
        border-color: #4e73df;
    }
    
    .session-card.active {
        border-color: #4e73df;
        background-color: #e3f2fd;
    }
</style>
@endpush

@push('scripts')
<script>
class PinAttendanceTerminal {
    constructor() {
        this.currentSession = null;
        this.pinInput = '';
        this.isProcessing = false;
        this.testMode = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
        
        // Load session if in URL
        const urlParams = new URLSearchParams(window.location.search);
        const sessionId = urlParams.get('session');
        if (sessionId) {
            this.loadSession(sessionId);
        }
    }
    
    bindEvents() {
        // Keypad buttons
        $('.btn-keypad').on('click', (e) => this.handleKeyPress($(e.currentTarget).data('key')));
        
        // Control buttons
        $('#btnClear').on('click', () => this.clearPin());
        $('#btnSubmit').on('click', () => this.submitPin());
        $('#btnRefresh').on('click', () => this.refreshData());
        $('#btnEndSession').on('click', () => this.endSession());
        $('#btnChangeSession').on('click', () => this.changeSession());
        $('#btnTestMode').on('click', () => this.toggleTestMode());
        
        // Session selection
        $('.select-session').on('click', (e) => {
            e.stopPropagation();
            const sessionId = $(e.currentTarget).closest('.session-card').data('session-id');
            this.loadSession(sessionId);
        });
        
        // Session card click
        $('.session-card').on('click', function() {
            $('.session-card').removeClass('active');
            $(this).addClass('active');
        });
        
        // Keyboard input
        $(document).on('keydown', (e) => {
            if (e.key >= '0' && e.key <= '9') {
                this.handleKeyPress(e.key);
            } else if (e.key === 'Enter') {
                this.submitPin();
            } else if (e.key === 'Escape' || e.key === 'Backspace') {
                this.clearPin();
            }
        });
        
        // Prevent form submission on Enter
        $('#pinInput').on('keydown', (e) => e.preventDefault());
    }
    
    handleKeyPress(key) {
        if (this.isProcessing || !this.currentSession) return;
        
        if (this.pinInput.length < 6) {
            this.pinInput += key;
            this.updatePinDisplay();
        }
        
        if (this.pinInput.length === 6) {
            // Auto-submit after 6 digits
            setTimeout(() => this.submitPin(), 300);
        }
    }
    
    clearPin() {
        this.pinInput = '';
        this.updatePinDisplay();
        $('#inputMessage').text('');
    }
    
    updatePinDisplay() {
        const pinInput = $('#pinInput');
        const pinDots = $('.pin-dot');
        
        pinInput.val(this.pinInput.padEnd(6, '-'));
        
        pinDots.each(function() {
            const position = $(this).data('position');
            if (position <= PinAttendanceTerminal.this.pinInput.length) {
                $(this).addClass('filled').text('•');
            } else {
                $(this).removeClass('filled').text('_');
            }
        });
    }
    
    async submitPin() {
        if (!this.currentSession || this.pinInput.length !== 6 || this.isProcessing) {
            return;
        }
        
        this.isProcessing = true;
        $('#inputMessage').text('Processing...').removeClass('text-danger').addClass('text-info');
        
        try {
            const response = await $.ajax({
                url: '/api/attendance/pin/mark',
                method: 'POST',
                data: {
                    pin: this.pinInput,
                    session_id: this.currentSession.id,
                    test_mode: this.testMode
                }
            });
            
            if (response.success) {
                this.showSuccess(response.data);
                this.clearPin();
                this.updateSessionStats();
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError(error.responseJSON?.message || 'Network error');
        } finally {
            this.isProcessing = false;
        }
    }
    
    showSuccess(data) {
        $('#resultDisplay').show();
        $('#successResult').show();
        $('#errorResult').hide();
        
        $('#studentInfo').html(`
            <strong>${data.student_name}</strong><br>
            Roll No: ${data.roll_number}<br>
            Class: ${data.class_section}
        `);
        
        $('#attendanceTime').text(`Time: ${data.time}`);
        
        // Play success sound (optional)
        this.playSound('success');
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            $('#resultDisplay').hide();
        }, 3000);
    }
    
    showError(message) {
        $('#resultDisplay').show();
        $('#successResult').hide();
        $('#errorResult').show();
        
        $('#errorMessage').text(message);
        $('#inputMessage').text(message).removeClass('text-info').addClass('text-danger');
        
        // Play error sound (optional)
        this.playSound('error');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $('#resultDisplay').hide();
        }, 5000);
    }
    
    async loadSession(sessionId) {
        try {
            const response = await $.ajax({
                url: `/api/teacher/sessions/${sessionId}`,
                method: 'GET'
            });
            
            this.currentSession = response.session;
            this.displaySessionInfo();
            this.updateSessionStats();
            
            // Show PIN input section
            $('#pinInputSection').show();
            $('#sessionInfo').hide();
            $('#currentSessionCard').show();
            
            // Update URL
            window.history.pushState({}, '', `?session=${sessionId}`);
            
        } catch (error) {
            alert('Failed to load session: ' + error.responseJSON?.message);
        }
    }
    
    displaySessionInfo() {
        const session = this.currentSession;
        const startTime = new Date(`2000-01-01T${session.start_time}`);
        const endTime = new Date(`2000-01-01T${session.end_time}`);
        
        $('#currentSessionInfo').html(`
            <p class="mb-1"><strong>Subject:</strong> ${session.subject.name}</p>
            <p class="mb-1"><strong>Class:</strong> ${session.class_section.name}</p>
            <p class="mb-1"><strong>Time:</strong> ${startTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${endTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
            <p class="mb-0"><strong>Teacher:</strong> ${session.teacher.name}</p>
        `);
        
        $('#sessionInfo').html(`
            <div class="text-center">
                <h5 class="text-success">ACTIVE SESSION</h5>
                <p>${session.subject.name} - ${session.class_section.name}</p>
                <p>${session.teacher.name} | ${session.date}</p>
                <hr class="bg-success">
                <p class="text-info">Ready for PIN input</p>
            </div>
        `);
    }
    
    async updateSessionStats() {
        if (!this.currentSession) return;
        
        try {
            const response = await $.ajax({
                url: `/api/teacher/sessions/${this.currentSession.id}/stats`,
                method: 'GET'
            });
            
            const stats = response.stats;
            const total = stats.present + stats.absent + stats.late + stats.excused;
            const presentPercentage = total > 0 ? Math.round((stats.present / total) * 100) : 0;
            
            $('#presentCount').text(stats.present);
            $('#absentCount').text(stats.absent);
            $('#attendanceProgress').text(`Total: ${stats.present}/${total} (${presentPercentage}%)`);
            $('#attendanceBar').css('width', `${presentPercentage}%`);
            
        } catch (error) {
            console.error('Failed to update stats:', error);
        }
    }
    
    endSession() {
        if (!confirm('End current session? Attendance will be finalized.')) return;
        
        $.ajax({
            url: `/api/teacher/sessions/${this.currentSession.id}/end`,
            method: 'POST',
            success: () => {
                alert('Session ended successfully');
                this.currentSession = null;
                this.resetTerminal();
                location.reload();
            }
        });
    }
    
    changeSession() {
        this.currentSession = null;
        this.resetTerminal();
    }
    
    resetTerminal() {
        this.clearPin();
        $('#pinInputSection').hide();
        $('#sessionInfo').show();
        $('#currentSessionCard').hide();
        $('#resultDisplay').hide();
        
        $('#sessionInfo').html(`
            <div class="text-center">
                <h5 class="text-warning blink">NO ACTIVE SESSION</h5>
                <p>Please select a session to begin</p>
            </div>
        `);
    }
    
    refreshData() {
        if (this.currentSession) {
            this.updateSessionStats();
        }
        // Refresh session list
        $.get('/api/teacher/sessions/active', (data) => {
            this.updateSessionList(data.sessions);
        });
    }
    
    updateSessionList(sessions) {
        // Update session list UI
    }
    
    toggleTestMode() {
        this.testMode = !this.testMode;
        $('#btnTestMode').toggleClass('btn-outline-info btn-warning');
        $('#btnTestMode').html(
            this.testMode ? 
            '<i class="bi bi-bug-fill"></i> Test Mode ON' :
            '<i class="bi bi-bug"></i> Test Mode'
        );
        
        const status = this.testMode ? 'TEST MODE ACTIVE' : 'NORMAL MODE';
        $('#connectionStatus').html(`<i class="bi bi-wifi"></i> ${status}`);
        $('#connectionStatus').toggleClass('bg-success bg-warning');
    }
    
    updateClock() {
        const now = new Date();
        $('.terminal-status .badge.bg-info').html(
            `<i class="bi bi-clock"></i> ${now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'})}`
        );
    }
    
    playSound(type) {
        // Implement sound effects if needed
        // Example: new Audio(`/sounds/${type}.mp3`).play();
    }
}

// Initialize terminal when page loads
$(document).ready(function() {
    window.terminal = new PinAttendanceTerminal();
});
</script>
@endpush