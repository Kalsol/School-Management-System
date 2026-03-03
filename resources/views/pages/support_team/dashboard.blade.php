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

    {{-- PARENT / STUDENT SPECIFIC VIEW --}}
    @if(Qs::userIsParent() || Qs::userIsStudent())
    <div class="row">
        <div class="col-sm-6 col-xl-4">
            <div class="card card-body border-left-success border-left-3 ">
                <div class="media">
                    <div class="mr-3 align-self-center">
                        <i class="icon-book-play icon-3x text-success-400"></i>
                    </div>
                    <div class="media-body">
                        <h6 class="media-title font-weight-semibold">Exam Reports</h6>
                        <span class="text-muted">View latest results</span>
                    </div>
                </div>
                <a href="" class="btn bg-danger-400 btn-block btn-sm mt-2">View Results</a>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card card-body border-left-primary border-left-3">
                <div class="media">
                    <div class="mr-3 align-self-center">
                        <i class="icon-calendar52 icon-3x text-primary-400"></i>
                    </div>
                    <div class="media-body">
                        <h6 class="media-title font-weight-semibold">Attendance</h6>
                        <span class="text-muted">Track school presence</span>
                    </div>
                </div>
                <button class="btn bg-danger-400 btn-block btn-sm mt-2">View Report</button>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card card-body border-left-danger border-left-3">
                <div class="media">
                    <div class="mr-3 align-self-center">
                        <i class="icon-cash3 icon-3x text-danger-400"></i>
                    </div>
                    
                    <div class="media-body">
                        <h6 class="media-title font-weight-semibold">Time Table</h6>
                        <span class="text-muted">Track Chiled time table</span>
                    </div>
                </div>

                <a href="{{ route('payments.index') }}" class="btn bg-danger-400 btn-block btn-sm mt-2">View Time Table</a>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!--Notice-->
        <div class="col-md-6">
            <div class="card card-collapsed">
                <div class="card-header header-elements-inline">
                    <h5 class="card-title">Notices <sup class="badge">{{ $unviewed_count }}</sup></h5>
                    @if(Qs::userIsAdministrative())
                        <a class="btn-link" href="{{ route('notices.index') }}">Manage</a>
                    @endif
                    {!! Qs::getPanelOptions() !!}
                </div>
                <div class=" ">
                    @include('pages/support_team/notices/show')
                </div>
            </div>
        </div>
        @if(Qs::userIsParent())
        <!--Conversation-->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header header-elements-inline text-white bg-dark">
                    <h5 class="card-title"><i class="icon-bubbles4 mr-2"></i> Recent Conversations</h5>
                </div>
        
                <div class="card-body p-0">
                    <ul class="media-list media-list-linked">
                        @forelse($conversations as $conv)
                            <li class="">
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
                            <li class="p-4 text-center text-muted">No recent messages</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer text-center p-1">
                    <a href="{{ route('chat.index') }}" class="btn btn-link btn-sm text-white">Go to Messenger</a>
                </div>
            </div>
        </div>
        @endif
    </div>

@endsection