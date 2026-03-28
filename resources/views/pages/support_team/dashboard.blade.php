@extends('layouts.master')
@section('page_title', 'My Dashboard')
@section('content')

{{-- SYSTEM ADMINISTRATOR / ADMIN VIEW --}}
@if(Qs::userIsTeamSA() || Qs::userIsAdmin())
<div class="row">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-teal-400 has-bg-image">
            <div class="media">
                <div class="media-body">
                    <h3 class="mb-0">{{ $users->where('user_type', 'student')->count() }}</h3>
                    <span class="text-uppercase font-size-xs font-weight-bold">Total Students</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-users4 icon-3x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-orange-400 has-bg-image">
            <div class="media">
                <div class="media-body">
                    <h3 class="mb-0">{{ $users->where('user_type', 'teacher')->count() }}</h3>
                    <span class="text-uppercase font-size-xs font-weight-bold">Total Teachers</span>
                </div>
                <div class="ml-3 align-self-center">
                    <i class="icon-users2 icon-3x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-slate-600 has-bg-image">
            <div class="media">
                <div class="mr-3 align-self-center">
                    <i class="icon-pointer icon-3x opacity-75"></i>
                </div>
                <div class="media-body text-right">
                    <h3 class="mb-0">{{ $users->where('user_type', 'admin')->count() }}</h3>
                    <span class="text-uppercase font-size-xs font-weight-bold">Total Administrators</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-body bg-pink-400 has-bg-image">
            <div class="media">
                <div class="mr-3 align-self-center">
                    <i class="icon-user icon-3x opacity-75"></i>
                </div>
                <div class="media-body text-right">
                    <h3 class="mb-0">{{ $users->where('user_type', 'parent')->count() }}</h3>
                    <span class="text-uppercase font-size-xs font-weight-bold">Total Parents</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- TEACHER SPECIFIC VIEW --}}
@if(Qs::userIsTeacher())
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <a href="{{ route('marks.index') }}" class="text-primary">
                            <i class="icon-graduation2 icon-2x"></i>
                            <div class="font-weight-semibold">Enter Marks</div>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="{{ route('attendance.index') }}" class="text-success">
                            <i class="icon-alarm icon-2x"></i>
                            <div class="font-weight-semibold">Take Attendance</div>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="{{ route('chat.index') }}" class="text-warning">
                            <i class="icon-bubbles4 icon-2x"></i>
                            <div class="font-weight-semibold">Messages</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-indigo-400 text-white">
            <div class="card-body">
                <div class="d-flex">
                    <h3 class="font-weight-semibold mb-0">My Classes</h3>
                </div>
                <div>Currently assigned to {{ $my_classes->count() ?? 0 }} sections</div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 1. PARENT / STUDENT DASHBOARD (TOP SECTION) --}}
@if(Qs::userIsParent() || Qs::userIsStudent())
@if(Qs::userIsParent() && isset($firstLoadChild))
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card bg-light border-left-info border-left-3 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center py-2">
                <div>
                    <span class="font-weight-semibold text-muted">CHILD CONTEXT:</span>
                    <span class="text-primary font-weight-bold ml-1">
                        {{ $firstLoadChild->user->name }} ({{ $firstLoadChild->my_class->name }}{{ $firstLoadChild->section->name }})
                    </span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-info dropdown-toggle shadow-0" data-toggle="dropdown">Switch Child</button>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($childrenList as $child)
                        <a href="{{ route('dashboard', ['student_id' => Qs::hash($child->id)]) }}"
                            class="dropdown-item {{ $firstLoadChild->id == $child->id ? 'active' : '' }}">
                            {{ $child->user->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-sm-6 col-xl-4">
        <div class="card card-body border-left-success border-left-3">
            <div class="media">
                <div class="mr-3 align-self-center">
                    <i class="icon-graduation2 icon-3x text-success-400"></i>
                </div>
                <div class="media-body text-right">
                    <h6 class="media-title font-weight-semibold">Academic Results</h6>
                    <span class="text-muted">Latest Average: <strong>{{ $avg_mark }}</strong></span>
                    <br>
                    <small class="text-muted">Total Score: {{ $total_score }}</small>
                </div>
            </div>
            @if(Qs::userIsParent())
            <a href="{{ route('marks.year_selector', Qs::hash($firstLoadChild->id)) }}" class="btn bg-success-400 btn-block btn-sm mt-2">View Full Mark Sheet</a>
            @else
            <a href="{{ route('marks.year_selector', Qs::hash(Auth::user()->id)) }}" class="btn bg-success-400 btn-block btn-sm mt-2">View Mark Sheet</a>
            @endif
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card card-body border-left-primary border-left-3">
            <div class="media">
                <div class="mr-3 align-self-center"><i class="icon-check icon-3x text-primary-400"></i></div>
                <div class="media-body text-right">
                    <h6 class="media-title font-weight-semibold">Attendance</h6>
                    <span class="text-muted">Today:
                        <span class="badge badge-success" style="background-color: {{ $today_status == 'Present' ? '#28a745' : '#dc3545' }};">
                            {{ $today_status }}
                        </span>
                    </span>
                </div>
            </div>
            <div class="mt-2">
                <div class="progress progress-xxs mb-1">
                    <div class="progress-bar bg-primary" style="width: {{ $attendance_val }}%"></div>
                </div>
            </div>
            <button class="btn bg-primary-400 btn-block btn-sm mt-1">
                <a href="{{ route('attendance.my_attendance')}}" class="text-white">Attendance Log</a>
            </button>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card card-body border-left-info border-left-3">
            <div class="media">
                <div class="mr-3 align-self-center"><i class="icon-alarm icon-3x text-info-400"></i></div>
                <div class="media-body text-right">
                    <h6 class="media-title font-weight-semibold">Live Timetable</h6>
                    <span class="text-muted">Now: <strong>{{ $current_subject }}</strong></span>
                </div>
            </div>
            <div class="bg-light p-1 mt-2 text-center rounded">
                <span class="text-muted font-size-xs">{{ $next_class_info }}</span>
            </div>
            {{--<a href="{{ route('tims.show', Qs::hash($firstLoadChild->id)) }}" class="btn bg-info-400 btn-block btn-sm mt-1">Weekly Schedule</a>--}}
        </div>
    </div>
</div>
@endif

{{-- 2. DYNAMIC CONTENT & CONVERSATIONS (MIDDLE SECTION) --}}
<div class="row">

    {{-- Dynamic Notice Board (Interactive) --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header header-elements-inline bg-transparent">
                <h6 class="card-title font-weight-bold">Recent Notices <sup class="badge badge-danger">{{ $unviewed_count }}</sup></h6>
                @if(Qs::userIsAdministrative())
                <a class="btn-link" href="{{ route('notices.index') }}">Manage</a>
                @endif
                {!! Qs::getPanelOptions() !!}
            </div>

            <div class="notices">
                {{-- This include uses your Snippet 1 logic (the 'working one') --}}
                @include('pages/support_team/notices/show')
            </div>
        </div>
    </div>

    {{-- Conversations (Only for Parents) --}}
    @if(Qs::userIsParent())
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header header-elements-inline text-white bg-dark">
                <h6 class="card-title"><i class="icon-bubbles4 mr-2"></i> Recent Conversations</h6>
            </div>

            <div class="card-body p-0">
                <ul class="media-list media-list-linked">
                    @forelse($conversations as $conv)
                    <li>
                        <a href="{{ route('chat.show', Qs::hash($conv->id)) }}" class="media d-flex align-items-center p-3 text-dark">
                            <div class="mr-3">
                                <img src="{{ $conv->photo }}" class="rounded-circle" width="40" height="40">
                            </div>
                            <div class="media-body">
                                <div class="d-flex justify-content-between">
                                    <span class="font-weight-bold">{{ $conv->name }}</span>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($conv->last_interaction)->diffForHumans(null, true) }}</small>
                                </div>
                                <div class="text-muted text-truncate" style="max-width: 250px;">
                                    {{ $conv->latest_message_text }}
                                </div>
                            </div>
                            @if($conv->unread_count > 0)
                            <div class="ml-3">
                                <span class="badge badge-pill bg-primary pulse">{{ $conv->unread_count }}</span>
                            </div>
                            @endif
                        </a>
                    </li>
                    @empty
                    <li class="p-4 text-center text-muted font-italic">No recent messages</li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer text-center p-1 bg-light">
                <a href="{{ route('chat.index') }}" class="btn btn-sm">Open Messenger</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection