{{--My Children--}}
<li class="nav-item">
    <a href="{{ route('my_children') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_children']) ? 'active' : '' }}"><i class="icon-users4"></i> My Children</a>
</li>
{{-- Attendance (Student/Parent) --}}
@if(Qs::userIsStudent() || Qs::userIsParent())
    <li class="nav-item">
        <a href="{{ route('attendance.my_attendance') }}" class="nav-link {{ Request::is('my-attendance') ? 'active' : '' }}">
            <i class="icon-alarm"></i> <span>My Attendance</span>
        </a>
    </li>
@endif