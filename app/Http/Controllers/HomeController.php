<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Attendance;
use App\Models\ExamRecord;
use App\Repositories\UserRepo;
use App\Repositories\NoticeRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Message;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class HomeController extends Controller
{
    protected $user, $student, $notice;

    public function __construct(UserRepo $user, NoticeRepo $notice, StudentRepo $student)
    {
        $this->user = $user;
        $this->notice = $notice;
        $this->student = $student;
    }

    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function privacy_policy()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.privacy_policy', $data);
    }

    public function terms_of_use()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.terms_of_use', $data);
    }

    public function dashboard(Request $request)
    {
        $d = [];

        // Administrative Data
        if (Qs::userIsTeamSAT()) {
            $d['users'] = $this->user->getAll();
        }

        // Notices logic
        $noticeData = $this->getNoticeData($request);
        $d = array_merge($d, $noticeData);

        if ($request->ajax() && ($request->has('unviewed-notices-page') || $request->has('viewed-notices-page'))) {
            return view('pages.support_team.notices.show', $d);
        }

        // Parent/Student Dashboard Data
        if (Qs::userIsParent() || Qs::userIsStudent()) {
            $d = array_merge($d, $this->getChildDashboardData($request));
        }

        $d['conversations'] = $this->getRecentConversations();

        return view('pages.support_team.dashboard', $d);
    }

    private function getChildDashboardData(Request $request)
    {
        $data = [];
        $user = Auth::user();
        $current_session = Qs::getSetting('current_session');

        $data['avg_mark'] = 'N/A';
        $data['total_score'] = 'N/A';
        $data['today_status'] = 'Not Marked';
        $data['attendance_val'] = 0;
        $data['current_subject'] = 'No Active Class';
        $data['next_class_info'] = 'Check Full Schedule';
        $data['firstLoadChild'] = null;

        if (Qs::userIsParent()) {
            $children = $this->student->getRecord(['my_parent_id' => $user->id])->with(['my_class', 'section', 'user'])->get();
            $data['childrenList'] = $children;

            if ($children->count() > 0) {
                $selected_id = $request->query('student_id');
                $data['firstLoadChild'] = $selected_id
                    ? $children->where('id', Qs::decodeHash($selected_id))->first() ?? $children->first()
                    : $children->first();
            }
        } else {
            $data['firstLoadChild'] = $this->student->getRecord(['user_id' => $user->id])->with(['my_class', 'section', 'user'])->first();
        }

        if ($data['firstLoadChild']) {
            $st = $data['firstLoadChild'];
            $target_user_id = $st->user_id;

            // ACADEMIC AVG
            $exr = ExamRecord::where(['student_id' => $target_user_id, 'year' => $current_session])
                ->orderBy('updated_at', 'desc')->first();

            if ($exr) {
                $data['avg_mark'] = $exr->ave . '%';
                $data['total_score'] = $exr->total;
            }

            // ATTENDANCE
            $today_att = Attendance::where('student_id', $target_user_id)
                ->whereDate('attendance_date', date('Y-m-d'))->first();
            if ($today_att) {
                $data['today_status'] = $today_att->status;
            }

            $all_att = Attendance::where(['student_id' => $target_user_id, 'session_id' => $current_session])->get();
            $total_days = $all_att->count();
            $present_days = $all_att->whereIn('status', [1, 'P', 'Present'])->count();
            $data['attendance_val'] = $total_days > 0 ? round(($present_days / $total_days) * 100) : 0;

            // LIVE TIMETABLE
            $timetable = $this->getLivePeriod($st->my_class_id, $st->section_id);
            $data['current_subject'] = $timetable['subject'];
            $data['next_class_info'] = $timetable['next_info'];
        }

        return $data;
    }

    private function getLivePeriod($class_id, $section_id)
    {
        $today = date('l');
        $now = date('H:i');

        $current = DB::table('time_tables')
            ->join('time_table_records', 'time_tables.ttr_id', '=', 'time_table_records.id')
            ->join('subjects', 'time_tables.subject_id', '=', 'subjects.id')
            ->where([
                'time_table_records.my_class_id' => $class_id,
                'time_table_records.section_id' => $section_id,
                'time_tables.day' => $today
            ])
            ->where('time_tables.timestamp_from', '<=', $now)
            ->where('time_tables.timestamp_to', '>=', $now)
            ->select('subjects.name as subject_name', 'time_tables.timestamp_to')
            ->first();

        if ($current) {
            return [
                'subject' => $current->subject_name,
                'next_info' => 'Ends at ' . date('h:i A', strtotime($current->timestamp_to))
            ];
        }

        return ['subject' => 'No Active Class', 'next_info' => 'Check Full Schedule'];
    }

    private function getNoticeData(Request $request)
    {
        $unviewed_notices = $viewed_notices = [];
        $unviewed_count = 0;
        $notices = $this->notice->allExceptAuth()->sortBy("id", SORT_REGULAR, true);

        foreach ($notices as $ntc) {
            $v_ids = json_decode($ntc->viewers_ids) ?: [];
            if (!in_array(auth()->id(), $v_ids)) {
                $unviewed_count++;
                $unviewed_notices[] = $ntc;
            } else {
                $viewed_notices[] = $ntc;
            }
        }

        return [
            'unviewed_count' => $unviewed_count,
            'unviewed_notices' => $this->get_paginator($request, $unviewed_notices, "unviewed-notices-page"),
            'viewed_notices' => $this->get_paginator($request, $viewed_notices, "viewed-notices-page"),
            'is_ajax_req' => $request->ajax()
        ];
    }
    
    private function get_paginator(Request $request, array $items, string $page_name)
    {
        $total = count($items);
        $per_page = 4;
        $current_page = $request->input($page_name) ?? 1;
        $starting_point = $current_page * $per_page - $per_page;
        $viewed_notices = array_slice($items, $starting_point, $per_page, true);
        return new LengthAwarePaginator($viewed_notices, $total, $per_page, $current_page, [
            'pageName' => $page_name,
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }
    
    private function getRecentConversations()
    {
            $auth_id = auth()->id();
    
            return User::whereHas('messages', function($q) use ($auth_id) {
                    $q->where('receiver_id', $auth_id);
                })
                ->addSelect([
                    'latest_message_text' => Message::select('message')
                        ->where('receiver_id', $auth_id)
                        ->whereColumn('sender_id', 'users.id')
                        ->latest()->take(1),
                    'unread_count' => Message::selectRaw('count(*)')
                        ->where('receiver_id', $auth_id)
                        ->whereColumn('sender_id', 'users.id')
                        ->where('is_read', false),
                    'last_interaction' => Message::select('created_at')
                        ->where('receiver_id', $auth_id)
                        ->whereColumn('sender_id', 'users.id')
                        ->latest()->take(1)
                ])
                ->orderByDesc('last_interaction')
                ->take(5)
                ->get();
    }
}
