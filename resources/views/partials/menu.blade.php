<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md sidebar-main-custom">
    <div class="sidebar-mobile-toggler text-center s ">
        <a href="#" class="sidebar-mobile-main-toggle">
            <i class="icon-arrow-left8"></i>
        </a>
        <span class="font-weight-semibold">Navigation</span>
        <a href="#" class="sidebar-mobile-expand">
            <i class="icon-screen-full"></i>
            <i class="icon-screen-normal"></i>
        </a>
    </div>

    <div class="sidebar-content">
        <div class="sidebar-user">
            <div class="card-body">
                <div class="media">
                    <div class="mr-3">
                        <a href="{{ route('my_account') }}">
                            <img src="{{ Auth::user()->photo }}" width="38" height="38" class="rounded-circle" style="border: 2px solid rgba(255,255,255,0.2)" alt="photo">
                        </a>
                    </div>

                    <div class="media-body">
                        <div class="media-title font-weight-semibold">{{ Auth::user()->name }}</div>
                        <div class="font-size-xs opacity-50">
                            <i class="icon-user font-size-sm"></i> &nbsp;{{ ucwords(str_replace('_', ' ', Auth::user()->user_type)) }}
                        </div>
                    </div>

                    <div class="ml-3 align-self-center">
                        <a href="{{ route('my_account') }}" class="text-white"><i class="icon-cog3 opacity-75"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-sidebar-mobile">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{in_array(Route::currentRouteName(), ['home', 'dashboard']) ? 'active' : ''}}">
                        <i class="icon-home4"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- The rest of your menu items remain the same --}}
                @if(Qs::userIsTeamSA())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['attendance.index', 'attendance.student_report','attendance.teacher_report']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                    <a href="#" class="nav-link"><i class="icon-alarm"></i> <span> Attendance</span></a>
                    <ul class="nav nav-group-sub">
                        <li class="nav-item"><a href="{{ route('attendance.index') }}" class="nav-link {{ Route::is('attendance.index') ? 'active' : '' }}">Start Scanning</a></li>
                        <li class="nav-item"><a href="{{ route('attendance.student_report') }}" class="nav-link {{ Route::is('attendance.student_report') ? 'active' : '' }}">Students Attendance</a></li>
                        <li class="nav-item"><a href="{{ route('attendance.teacher_report') }}" class="nav-link {{ Route::is('attendance.teacher_report') ? 'active' : '' }}">Teachers Attendance</a></li>
                    </ul>
                </li>
                @endif

                {{--Academics--}}
                @if(Qs::userIsAcademic())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['tt.index', 'ttr.edit', 'ttr.show', 'ttr.manage']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                    <a href="#" class="nav-link"><i class="icon-graduation2"></i> <span> Academics</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Manage Academics">
                        <li class="nav-item"><a href="{{ route('tt.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['tt.index']) ? 'active' : '' }}">Timetables</a></li>
                    </ul>
                </li>
                @endif

                {{--Manage Students--}}
                @if(Qs::userIsTeamSAT())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.create', 'students.list', 'students.edit', 'students.show', 'students.promotion', 'students.promotion_manage', 'students.graduated']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                    <a href="#" class="nav-link"><i class="icon-users"></i> <span> Students</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Manage Students">
                        @if(Qs::userIsTeamSA())
                        <li class="nav-item">
                            <a href="{{ route('students.create') }}" class="nav-link {{ (Route::is('students.create')) ? 'active' : '' }}">Admit Student</a>
                        </li>
                        @endif

                        <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.list', 'students.edit', 'students.show']) ? 'nav-item-expanded' : '' }}">
                            <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['students.list', 'students.edit', 'students.show']) ? 'active' : '' }}">Student Information</a>
                            <ul class="nav nav-group-sub">
                                @foreach(App\Models\MyClass::orderBy('id')->get() as $c)
                                <li class="nav-item"><a href="{{ route('students.list', $c->id) }}" class="nav-link ">{{ $c->name }}</a></li>
                                @endforeach
                            </ul>
                        </li>

                        @if(Qs::userIsTeamSA())
                        <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.promotion', 'students.promotion_manage']) ? 'nav-item-expanded' : '' }}"><a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion', 'students.promotion_manage' ]) ? 'active' : '' }}">Student Promotion</a>
                            <ul class="nav nav-group-sub">
                                <li class="nav-item"><a href="{{ route('students.promotion') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion']) ? 'active' : '' }}">Promote Students</a></li>
                                <li class="nav-item"><a href="{{ route('students.promotion_manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion_manage']) ? 'active' : '' }}">Manage Promotions</a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a href="{{ route('students.graduated') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.graduated' ]) ? 'active' : '' }}">Students Graduated</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(Qs::userIsTeamSA())
                {{--Manage Users--}}
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['users.index', 'users.show', 'users.edit']) ? 'active' : '' }}"><i class="icon-users4"></i> <span> Users</span></a>
                </li>

                {{--Manage Classes--}}
                <li class="nav-item">
                    <a href="{{ route('classes.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['classes.index','classes.edit']) ? 'active' : '' }}"><i class="icon-windows2"></i> <span> Classes</span></a>
                </li>

                {{--Manage Sections--}}
                <li class="nav-item">
                    <a href="{{ route('sections.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['sections.index','sections.edit',]) ? 'active' : '' }}"><i class="icon-fence"></i> <span>Sections</span></a>
                </li>

                {{--Manage Subjects--}}
                <li class="nav-item">
                    <a href="{{ route('subjects.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['subjects.index','subjects.edit',]) ? 'active' : '' }}"><i class="icon-pin"></i> <span>Subjects</span></a>
                </li>
                @endif

                {{--Exam--}}
                @if(Qs::userIsTeamSAT())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['exams.index', 'exams.edit', 'grades.index', 'grades.edit', 'marks.index', 'marks.manage', 'marks.bulk', 'marks.tabulation', 'marks.show', 'marks.batch_fix',]) ? 'nav-item-expanded nav-item-open' : '' }} ">
                    <a href="#" class="nav-link"><i class="icon-books"></i> <span> Marking </span></a>
                    <ul class="nav nav-group-sub" data-submenu-title="Manage Exams">
                        @if(Qs::userIsTeamSA())
                        <li class="nav-item">
                            <a href="{{ route('exams.index') }}" class="nav-link {{ (Route::is('exams.index')) ? 'active' : '' }}">Marking Session List</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('grades.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['grades.index', 'grades.edit']) ? 'active' : '' }}">Grades</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('marks.tabulation') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.tabulation']) ? 'active' : '' }}">Tabulation Sheet</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('marks.bulk') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.bulk', 'marks.show']) ? 'active' : '' }}">Marksheet</a>
                        </li>
                        @endif

                        @if(Qs::userIsTeamSAT())
                        <li class="nav-item">
                            <a href="{{ route('marks.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.index']) ? 'active' : '' }}">Marks</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @include('pages.'.Qs::getUserType().'.menu')

                @if(Qs::userIsTeamSA() || Qs::userIsParent())
                <li class="nav-item">
                    <a href="{{ route('chat.index') }}" class="nav-link {{in_array(Route::currentRouteName(), ['chat.index','chat.show']) ? 'active' : ''}}">
                        <i class="icon-bubbles4"></i>
                        <span>Messages</span>
                    </a>
                </li>
                @endif

                {{--Manage Account--}}
                <li class="nav-item">
                    <a href="{{ route('my_account') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_account']) ? 'active' : '' }}"><i class="icon-user"></i> <span>My Account</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>