@component('mail::message')
    # Attendance Alert
    
    Dear Parent/Guardian,
    
    This is to inform you that **{{ $student->user->name }}** was marked **Absent** from school today, {{ $date }}.
    
    If you are aware of this absence, please log in to the portal to submit an excuse request.
    
    @component('mail::button', ['url' => route('attendance.my_attendance')])
    View Attendance Portal
    @endcomponent
    
    Thanks,<br>
    {{ config('app.name') }}
@endcomponent