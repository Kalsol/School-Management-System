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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AttendanceController extends Controller
{
    protected $my_class;
    public function __construct(MyClassRepo $my_class)
    {
        $this->my_class =  $my_class;
        // Protect web routes; allow Python to access API methods
        $this->middleware('teamSA')->except(['markStudentByFace', 'markTeacherByFace', 'getTeacherMap', 'getStudentMap', 'myAttendance', 'createExcuse', 'submitExcuse']);
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
        $users = User::whereNotNull('photo')->where('user_type', 'student')->get(['id', 'photo']);
        //  dd($users->pluck('photo'));
        $map = $users->mapWithKeys(function ($user) {

            $folder = basename(dirname($user->photo));

            // ❌ Skip default/shared folders
            if ($folder === 'images' || empty($folder)) {
                return [];
            }

            return [$folder => $user->id];
        });
        // dd($map);
        return response()->json($map);
    }

    public function getTeacherMap()
    {
        $users = User::whereNotNull('photo')->where('user_type', 'student')->get(['id', 'photo']);
        $map = $users->mapWithKeys(function ($user) {
            // This gets 'photo.jpg' from 'http://.../Teach123/photo.jpg'
            return [basename($user->photo) => $user->id];
        });
        return response()->json($map);
    }

    /**
     * API: Mark attendance from Python detection
     */
    public function markStudentByFace(Request $request): JsonResponse
    {
        Log:
        info($request->all());
        $studentId = $request->student_id;
        $sessionDate = $request->session_id;
        $currentTime = now();

        $sessionStartTime = "08:00:00";
        $gracePeriodMinutes = 20;

        $student = StudentRecord::where('user_id', $studentId)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $exists = Attendance::where('student_id', $studentId)
            ->where('attendance_date', $sessionDate)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already marked'], 200);
        }

        $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $sessionStartTime);
        $diff = $startTime->diffInMinutes($currentTime, false);

        $status = ($diff > $gracePeriodMinutes) ? 'Late' : 'Present';

        Attendance::create([
            'student_id' => $studentId,
            'class_id' => $student->my_class_id,
            'section_id' => $student->section_id,
            'session_id' => $student->session,
            'attendance_date' => $sessionDate,
            'time_in' => $currentTime->toTimeString(),
            'minutes_late' => max(0, $diff),
            'status' => $status,
        ]);

        return response()->json([
            'message' => "Marked as $status",
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
        TeachersAttendance::create([
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

        // dd($attendances);

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

    public function updateExcuse(Request $request, $attendance_id)
    {
        Log::info("Update Excuse Request: " . json_encode($request->all()) . " for Attendance ID: " . $attendance_id);
        try {
            $at = Attendance::findOrFail($attendance_id);

            // Safety check: ensure user is logged in
            if (!auth()->check()) {
                return response()->json(['ok' => false, 'msg' => 'Session expired. Please login.'], 401);
            }

            $updateData = [
                'admin_response' => $request->admin_response,
                'admin_id'       => auth()->id(),
                'handled_at'     => now(),
            ];

            if ($request->action == 'approve') {
                $updateData['is_excused'] = true;
                $updateData['status']     = 'Present';
                $message = "Excuse approved. Status changed to Present.";
            } else {
                $updateData['is_excused'] = false;
                // We keep the original status (Absent/Late) if rejected
                $message = "Excuse has been rejected.";
            }

            $at->update($updateData);

            return response()->json([
                'ok' => true,
                'msg' => $message
            ]);
        } catch (\Exception $e) {
            // Log the error so you can see it in storage/logs/laravel.log
            \Log::error("Attendance Update Error: " . $e->getMessage());

            return response()->json([
                'ok' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function createExcuse($attendance_id)
    {
        // Find the attendance record or fail if not found
        // We include 'user' so we can show the student's name on the page
        $attendance = Attendance::with('user')->findOrFail($attendance_id);

        // Security: Ensure the logged-in parent actually owns this child
        $childIds = StudentRecord::where('my_parent_id', auth()->id())->pluck('user_id')->toArray();

        if (!in_array($attendance->student_id, $childIds)) {
            return back()->with('flash_danger', 'Access Denied: You cannot submit an excuse for this student.');
        }

        return view('pages.parent.attendance_excuse', compact('attendance'));
    }

    // Updated  will check
    public function submitExcuse(Request $request, $attendance_id)
    {
        $request->validate([
            'remarks' => 'required|string|max:500',
            'excuse_type' => 'required|string',
            'evidence' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        $attendance = Attendance::findOrFail($attendance_id);

        // Security check
        if (Qs::userIsParent()) {
            $childIds = StudentRecord::where('my_parent_id', auth()->id())->pluck('user_id')->toArray();
            if (!in_array($attendance->student_id, $childIds)) {
                return back()->with('flash_danger', 'Unauthorized action.');
            }
        }

        $data = [
            'remarks' => $request->remarks,
            'excuse_type' => $request->excuse_type,
            'is_excused' => false,
        ];

        // Handle File Upload
        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filename = 'excuse_' . $attendance_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/attendance_evidence'), $filename);
            $data['evidence'] = $filename;
        }

        $attendance->update($data);

        return redirect()->route('attendance.my_attendance')->with('flash_success', 'Excuse submitted successfully and pending review.');
    }

    public function myAttendance(Request $request)
    {
        $user = auth()->user();
        $data = [];

        $studentUserIds = collect();

        // 1. Get all children records (needed for the dropdown list)
        if (Qs::userIsParent()) {
            $data['my_children'] = StudentRecord::with('user')
                ->where('my_parent_id', $user->id)
                ->get();
            $studentUserIds = $data['my_children']->pluck('user_id');
        }

        // 2. Start building the Attendance Query
        $query = Attendance::with('user');

        if (Qs::userIsStudent()) {
            $query->where('student_id', $user->id);
        } elseif (Qs::userIsParent()) {
            // IMPORTANT: Use user_id for filtering
            if ($request->filled('student_id')) {
                // Filter by the specific child's user_id
                $query->where('student_id', $request->student_id);
            } else {
                // Fallback: Show all children for this parent
                $query->whereIn('student_id', $studentUserIds);
            }
        }

        // 3. Apply Status and Date Filters
        $query->when($request->status, function ($q) use ($request) {
            return $q->where('status', $request->status);
        });

        $query->when($request->from_date, function ($q) use ($request) {
            return $q->whereDate('attendance_date', '>=', $request->from_date);
        });

        $query->when($request->to_date, function ($q) use ($request) {
            return $q->whereDate('attendance_date', '<=', $request->to_date);
        });

        // 4. Execute Query
        $data['attendances'] = $query->orderBy('attendance_date', 'desc')->get();

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
