{{--Marksheet--}}
<li class="nav-item">
    <a href="{{ route('marks.year_select', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.show', 'marks.year_selector', 'pins.enter']) ? 'active' : '' }}"><i class="icon-book"></i> Marksheet</a>
</li>
{{-- Attendance (Student/Parent) --}}
@if(Qs::userIsStudent() || Qs::userIsParent())
    <li class="nav-item">
        <a href="{{ route('attendance.my_attendance') }}" class="nav-link {{ Request::is('my-attendance') ? 'active' : '' }}">
            <i class="icon-alarm"></i> <span>My Attendance</span>
        </a>
    </li>
@endif