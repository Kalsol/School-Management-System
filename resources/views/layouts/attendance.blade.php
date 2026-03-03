<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>School Attendance System - @yield('title')</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 0.75rem 1rem;
            margin: 2px 0;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
            border-radius: 5px;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        .content-header {
            padding: 20px 0;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .stat-icon {
            font-size: 2rem;
            opacity: 0.7;
        }
        
        .pin-display {
            font-family: 'Courier New', monospace;
            font-size: 2.5rem;
            letter-spacing: 10px;
            background: linear-gradient(45deg, #f8f9fc, #e3e6f0);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 2px dashed #4e73df;
        }
        
        .attendance-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-present { background-color: #d1e7dd; color: #0f5132; }
        .status-absent { background-color: #f8d7da; color: #842029; }
        .status-late { background-color: #fff3cd; color: #664d03; }
        .status-excused { background-color: #cff4fc; color: #055160; }
        
        .session-card {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .session-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15) !important;
        }
        
        .terminal-screen {
            background-color: #1a1a1a;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            padding: 20px;
            border-radius: 10px;
            min-height: 300px;
            border: 3px solid #333;
        }
        
        .blink {
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar d-none d-md-block">
                <div class="text-center py-4">
                    <h4><i class="bi bi-mortarboard"></i> SchoolSys</h4>
                    <small>Attendance System</small>
                </div>
                <nav class="nav flex-column">
                    @auth
                        @if(auth()->user()->role === 'teacher')
                            <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" 
                               href="{{ route('teacher.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a class="nav-link {{ request()->routeIs('teacher.sessions.*') ? 'active' : '' }}" 
                               href="{{ route('teacher.sessions.index') }}">
                                <i class="bi bi-calendar-check"></i> Attendance Sessions
                            </a>
                            <a class="nav-link {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}" 
                               href="{{ route('teacher.attendance.index') }}">
                                <i class="bi bi-clipboard-data"></i> Mark Attendance
                            </a>
                            <a class="nav-link {{ request()->routeIs('teacher.reports.*') ? 'active' : '' }}" 
                               href="{{ route('teacher.reports.index') }}">
                                <i class="bi bi-bar-chart"></i> Reports
                            </a>
                            <a class="nav-link {{ request()->routeIs('teacher.students.*') ? 'active' : '' }}" 
                               href="{{ route('teacher.students.index') }}">
                                <i class="bi bi-people"></i> Students
                            </a>
                            <a class="nav-link" href="{{ route('teacher.pin.terminal') }}">
                                <i class="bi bi-keyboard"></i> PIN Terminal
                            </a>
                        @elseif(auth()->user()->role === 'student')
                            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" 
                               href="{{ route('student.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}" 
                               href="{{ route('student.attendance.index') }}">
                                <i class="bi bi-calendar-week"></i> My Attendance
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.pin.*') ? 'active' : '' }}" 
                               href="{{ route('student.pin.show') }}">
                                <i class="bi bi-shield-lock"></i> My PIN
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.excuse.*') ? 'active' : '' }}" 
                               href="{{ route('student.excuse.create') }}">
                                <i class="bi bi-file-earmark-text"></i> Excuse Request
                            </a>
                            <a class="nav-link" href="{{ route('student.schedule') }}">
                                <i class="bi bi-clock"></i> Schedule
                            </a>
                        @elseif(auth()->user()->role === 'admin')
                            <!-- Admin links -->
                        @endif
                        
                        <hr class="text-white-50">
                        <a class="nav-link" href="">
                            <i class="bi bi-person"></i> Profile
                        </a>
                        <a class="nav-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    @endauth
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 ms-auto">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto">
                                @auth
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" 
                                           role="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-person-circle"></i> 
                                            {{ auth()->user()->name }}
                                            <span class="badge bg-{{ 
                                                auth()->user()->role === 'teacher' ? 'primary' : 
                                                (auth()->user()->role === 'student' ? 'success' : 'danger')
                                            }}">
                                                {{ ucfirst(auth()->user()->role) }}
                                            </span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="">
                                                <i class="bi bi-person"></i> Profile
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="{{ route('logout') }}"
                                               onclick="event.preventDefault(); 
                                                        document.getElementById('logout-form').submit();">
                                                <i class="bi bi-box-arrow-right"></i> Logout
                                            </a>
                                        </div>
                                    </li>
                                @endauth
                            </ul>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <main class="p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
    
    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Global CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                pageLength: 25,
                responsive: true
            });
            
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
    
    @stack('scripts')
</body>
</html>