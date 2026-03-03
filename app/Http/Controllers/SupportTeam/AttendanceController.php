<?php

namespace App\Http\Controllers\SupportTeam;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\StudentRecord;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\Qs;
use App\Mail\AbsenteeNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendAttendanceSms;
use App\Repositories\MyClassRepo;
use App\Models\TeachersAttendance;
use App\Models\StaffRecord;
use Symfony\Component\Process\Process; 
use Symfony\Component\Process\Exception\ProcessFailedException;

class AttendanceController extends Controller
{
    public function __construct(MyClassRepo $my_class)
    {
        $this->my_class =  $my_class;
        // Protect web routes; allow Python to access API methods
        $this->middleware('teamSA')->except(['markStudentByFace', 'markTeacherByFace', 'getTeacherMap', 'getStudentMap', 'myAttendance']);
    }

    /**
     * Display the main attendance dashboard
     */
    public function index()
    {
        return view('pages.support_team.attendance.index');
    }

    /**
     * API: Provide Python with a mapping of Photo Filenames to Student IDs
     */
     public function getStudentMap()
     {
         $users = User::whereNotNull('photo')->get(['id', 'photo']);
         $map = $users->mapWithKeys(function ($user) {
             // This gets 'photo.jpg' from 'http://.../STU123/photo.jpg'
             return [basename($user->photo) => $user->id];
         });
         return response()->json($map);
     }
     
     public function getTeacherMap()
     {
         $users = User::whereNotNull('photo')->get(['id', 'photo']);
         $map = $users->mapWithKeys(function ($user) {
             // This gets 'photo.jpg' from 'http://.../Teach123/photo.jpg'
             return [basename($user->photo) => $user->id];
         });
         return response()->json($map);
     }

    /**
     * API: Mark attendance from Python detection
     */
    public function markStudentByFace(Request $request)
    {
        $studentId = $request->student_id;
        $sessionDate = $request->session_id; // Date sent from Python (YYYY-MM-DD)
        $currentTime = now();

        // CONFIG: 8:00 AM Start with 20-minute Grace Period
        $sessionStartTime = "08:00:00"; 
        $gracePeriodMinutes = 20;

        // 1. Validate Student exists in academic records
        $student = StudentRecord::where('user_id', $studentId)->first();
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }

        // 2. Prevent duplicate for the SAME session date
        $exists = Attendance::where('student_id', $studentId)
            ->where('attendance_date', $sessionDate)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already marked for today'], 200);
        }

        // 3. Timing Logic (Present vs Late)
        $startTime = Carbon::createFromFormat('H:i:s', $sessionStartTime);
        $diff = $startTime->diffInMinutes($currentTime, false);

        // If current time is more than 20 mins past 2:00 PM, mark as Late
        $status = ($diff > $gracePeriodMinutes) ? 'Late' : 'Present';

        // 4. Save the record
        Attendance::create([
            'student_id' => $studentId,
            'class_id' => $student->my_class_id,
            'section_id' => $student->section_id,
            'session_id' => $student->session, // The academic year session
            'attendance_date' => $sessionDate,
            'time_in' => $currentTime->toTimeString(),
            'minutes_late' => ($diff > 0) ? $diff : 0,
            'status' => $status,
        ]);

        return response()->json([
            'message' => "Success: Marked as $status",
            'status' => $status,
            'time' => $currentTime->format('h:i A')
        ], 201);
    }
    
    public function markTeacherByFace(Request $request)
    {
        $teacherId = $request->teacher_id;
        $teacher = StaffRecord::find($teacherId);
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $currentTime = Carbon::now();
        $sessionDate = $currentTime->toDateString();
        $startTime = Carbon::createFromFormat('H:i', '02:00');
        $gracePeriodMinutes = 20;

        $diff = $startTime->diffInMinutes($currentTime, false);

        // If current time is more than 20 mins past 2:00 PM, mark as Late
        $status = ($diff > $gracePeriodMinutes) ? 'Late' : 'Present';

        // 4. Save the record
        TeacherAttendance::create([
            'teacher_id' => $teacherId,
            'session_id' => $teacher->session, // The academic year session
            'attendance_date' => $sessionDate,
            'time_in' => $currentTime->toTimeString(),
            'minutes_late' => ($diff > 0) ? $diff : 0,
            'status' => $status,
        ]);

        return response()->json([
            'message' => "Success: Marked as $status",
            'status' => $status,
            'time' => $currentTime->format('h:i A')
        ], 201);
    }

    /**
     * WEB: Display Attendance Reports
     */
     public function studentReport(Request $request)
     {
         $date = $request->date ?? now()->toDateString();
         $my_class_id = $request->my_class_id;
         $section_id = $request->section_id;
     
         $my_classes = $this->my_class->all();
         $sections = $this->my_class->getAllSections();
         
         // Determine if a filter has been applied
         $selected = $request->has('my_class_id');
     
         // Start the query with relationships
         $query = Attendance::with(['user', 'student_record.my_class', 'student_record.section'])
             ->where('attendance_date', $date);
     
         // Apply Class Filter
         if ($my_class_id) {
             $query->whereRelation('student_record', 'my_class_id', $my_class_id);
         }
     
         // Apply Section Filter
         if ($section_id) {
             $query->whereRelation('student_record', 'section_id', $section_id);
         }
     
         $attendances = $query->orderBy('time_in', 'desc')->get();
     
         return view('pages.support_team.attendance.student_report', compact(
             'attendances', 
             'date', 
             'my_classes', 
             'sections', 
             'selected', 
             'my_class_id', 
             'section_id'
         ));
     }
     
     // show teachers Attendance
     public function teacherReport(Request $request)
     {
         $date = $request->date ?? now()->toDateString();
         $my_class_id = $request->my_class_id;
         $section_id = $request->section_id;
     
         $my_classes = $this->my_class->all();
         $sections = $this->my_class->getAllSections();
         
         // Determine if a filter has been applied
         $selected = $request->has('my_class_id');
         $query = TeachersAttendance::with(['user', 'session'])
             ->where('attendance_date', $request->date);
     
         // Apply Class Filter
         if ($request->my_class_id) {
             $query->whereRelation('staffRecord', 'my_class_id', $request->my_class_id);
         }
     
         $attendances = $query->orderBy('time_in', 'desc')->get();
     
         return view('pages.support_team.attendance.teacher_report', compact(
             'attendances', 
             'date', 
             'my_classes', 
             'sections', 
             'selected', 
             'my_class_id', 
             'section_id'
         ));
     }
    
     
    /**
     * TRIGGER 1: Daily Absentee Alert (Run every day at 3:00 PM)
     */
     public function notifyDailyAbsentees()
     {
         $today = now()->toDateString();
         $presentIds = Attendance::where('attendance_date', $today)->pluck('student_id');
     
         $absentees = StudentRecord::whereNotIn('user_id', $presentIds)
             ->with(['user', 'my_parent'])
             ->get();
     
         foreach ($absentees as $student) {
             $parent = $student->my_parent;
             if (!$parent) continue;
     
             // Queue Email
             if ($parent->email) {
                 Mail::to($parent->email)->queue(new AbsenteeNotification($student, $today));
             }
     
             // Queue SMS Job
             if ($parent->phone) {
                 $msg = "Attendance Alert: " . $student->user->name . " is absent today (" . $today . ").";
                 SendAttendanceSms::dispatch($parent->phone, $msg);
             }
         }
     }
    
    /**
    * TRIGGER 2: Chronic Latecomer Alert (Run every 3 days or weekly)
    * Checks for students with 3+ "Late" statuses this week.
    */
    public function notifyChronicLatecomers()
    {
        $startOfWeek = now()->startOfWeek()->toDateString();
        
        // Find students with 3 or more 'Late' status this week
        $lateRecords = Attendance::where('status', 'Late')
            ->where('attendance_date', '>=', $startOfWeek)
            ->select('student_id')
            ->selectRaw('COUNT(*) as late_count')
            ->groupBy('student_id')
            ->having('late_count', '>=', 3)
            ->get();
    
        foreach ($lateRecords as $record) {
            $student = StudentRecord::where('user_id', $record->student_id)->with('user', 'my_parent')->first();
            if ($student && $student->my_parent) {
                $msg = "Attendance Notice: " . $student->user->name . " has been late " . $record->late_count . " times this week.";
                $this->sendSMS($student->my_parent->phone, $msg);
            }
        }
    }
    
    public function viewExcuses()
    {
        $excuses = Attendance::whereNotNull('remarks')
            ->where('is_excused', false)
            ->with('user')
            ->get();
    
        return view('pages.support_team.attendance.excuses', compact('excuses'));
    }
    
    public function approveExcuse($id)
    {
        Attendance::findOrFail($id)->update(['is_excused' => true, 'status' => 'Present']);
        return back()->with('flash_success', 'Excuse Approved. Student status updated to Present.');
    }
    
    public function submitExcuse(Request $request, $attendance_id)
    {
        $at = Attendance::findOrFail($attendance_id);
        
        // Ensure the parent is the one editing their child's record
        $at->update([
            'remarks' => $request->remarks,
            'is_excused' => false // Admin still needs to approve it
        ]);
    
        return back()->with('flash_success', 'Excuse submitted. Pending Admin approval.');
    }
    
    public function myAttendance(Request $request)
    {
        // dd($request->all());
        $user = auth()->user();
        $data = [];
    
        if (Qs::userIsStudent($user->id)) {
            // Get the student record and their specific attendance
            $student = StudentRecord::where('user_id', $user->id)->first();
            $data['attendances'] = Attendance::where('student_id', $user->id)
                ->orderBy('attendance_date', 'desc')
                ->get();
            $data['student_name'] = $user->name;
        } 
        
        elseif (Qs::userIsParent($user->id)) {
            // Get all children associated with this parent
            $data['my_children'] = StudentRecord::with('user')
            ->where('my_parent_id', $user->id)
            ->get();
            // dd($data['my_children']);
            
            // Get attendance for all those children
            $data['attendances'] = Attendance::with('user')
                ->whereIn('student_id', $data['my_children']->pluck('id'))
                ->orderBy('attendance_date', 'desc')
                ->get();
        }
    
        return view('pages.parent.my_attendance', $data);
    }
    
    public function notifyAbsentees(Request $request)
    {    
        $today = now()->toDateString();
        $presentIds = Attendance::where('attendance_date', $today)
            ->where('status', 'Absent')
            ->pluck('student_id');
        
        $absentees = StudentRecord::whereIn('user_id', $presentIds)
            ->with(['user', 'my_parent'])
            ->get();

        foreach ($absentees as $student) {
            $parent = $student->my_parent;
            if (!$parent) continue;
    
            // Queue Email
            if ($parent->email) {
                Mail::to($parent->email)->queue(new AbsenteeNotification($student, $today));
            }
    
            // Queue SMS Job
            if ($parent->phone) {
                $msg = "Attendance Alert: " . $student->user->name . " is absent today (" . $today . ").";
                dispatch(new SendAttendanceSms($parent->phone, $msg));
            }
        }

        return back()->with('flash_success', 'Notifications sent successfully to parents.');
    }
    
    public function startStudentFaceRecognition()
    {
        $scriptPath = 'C:\Users\Tati\Desktop\lav_sms\storage\scripts\attendance.py';
            
            // 'start /B' runs the command in the background on Windows
            shell_exec("start /B python \"$scriptPath\"");
        
            return back()->with('flash_success', __('Camera Started'));
    }
}
