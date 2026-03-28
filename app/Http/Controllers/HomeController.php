<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Attendance;
use App\Models\ExamRecord;
use App\Models\Mark;
use App\Repositories\UserRepo;
use App\Repositories\NoticeRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    protected $user;
    protected $student;
    protected $notice;
    public function __construct(UserRepo $user, NoticeRepo $notice, StudentRepo $student)
    {
        $this->user = $user;
        $this->notice = $notice;
        $this->student =$student;
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

        // 1. Handle Administrative Data
        if (Qs::userIsTeamSAT()) {
            $d['users'] = $this->user->getAll();
        }

        // 2. Handle Notices (Refactored into private method)
        $noticeData = $this->getNoticeData($request);
        $d = array_merge($d, $noticeData);

        // If Notice pagination is AJAX, return only that view
        if ($request->ajax() && ($request->has('unviewed-notices-page') || $request->has('viewed-notices-page'))) {
            return view('pages.support_team.notices.show', $d);
        }

        // 3. Handle Parent/Student Data
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

        // 1. Identify Student Context
        if (Qs::userIsParent()) {
            $children = $this->student->getRecord(['my_parent_id' => $user->id])->with(['my_class', 'section', 'user'])->get();
            $data['childrenList'] = $children;
            $selected_id = $request->query('student_id');
            $data['firstLoadChild'] = $selected_id
                ? $children->where('id', Qs::decodeHash($selected_id))->first() ?? $children->first()
                : $children->first();
        } else {
            $data['firstLoadChild'] = $this->student->getRecord(['user_id' => $user->id])->with(['my_class', 'section', 'user'])->first();
        }

        if ($data['firstLoadChild']) {
            $st = $data['firstLoadChild'];
            $target_user_id = $st->user_id;

            // --- ACADEMIC AVG ---
            $exr = ExamRecord::where(['student_id' => $target_user_id, 'year' => $current_session])
                ->orderBy('updated_at', 'desc')->first();
            $data['avg_mark'] = $exr ? $exr->ave . '%' : 'N/A';
            $data['total_score'] = $exr ? $exr->total : 'N/A';

            // --- ATTENDANCE ---
            $today_att = Attendance::where('student_id', $target_user_id)->whereDate('attendance_date', date('Y-m-d'))->first();
            $data['today_status'] = $today_att ? $today_att->status : 'Not Marked';

            $all_att = Attendance::where(['student_id' => $target_user_id, 'session_id' => $current_session])->get();
            $total_days = $all_att->count();
            $present_days = $all_att->whereIn('status', [1, 'P', 'Present'])->count();
            $data['attendance_val'] = $total_days > 0 ? round(($present_days / $total_days) * 100) : 0;

            // --- LIVE TIMETABLE ---
            $timetable = $this->getLivePeriod($st->my_class_id, $st->section_id);
            $data['current_subject'] = $timetable['subject'];
            $data['next_class_info'] = $timetable['next_info'];
        }

        return $data;
    }

    private function getLivePeriod($class_id, $section_id)
    {
        $today = date('l'); // Get current day (e.g., Monday)
        $now = date('H:i:s'); // Get current time in 24h format

        // Query your timetable table
        // Adjust column names (timestamp_from, timestamp_to) to match your schema
        $current = DB::table('time_tables')
            ->join('subjects', 'time_tables.subject_id', '=', 'subjects.id')
            ->where([
                'my_class_id' => $class_id,
                'section_id' => $section_id,
                'day' => $today
            ])
            ->whereTime('timestamp_from', '<=', $now)
            ->whereTime('timestamp_to', '>=', $now)
            ->select('subjects.name as subject_name', 'timestamp_to')
            ->first();

        if ($current) {
            return [
                'subject' => $current->subject_name,
                'next_info' => 'Ends at ' . date('h:i A', strtotime($current->timestamp_to))
            ];
        }

        return [
            'subject' => 'No Active Class',
            'next_info' => 'Check Full Schedule'
        ];
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
