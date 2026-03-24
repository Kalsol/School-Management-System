<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Repositories\UserRepo;
use App\Repositories\NoticeRepo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\User;

class HomeController extends Controller
{
    protected $user;
    protected $notice;
    public function __construct(UserRepo $user, NoticeRepo $notice)
    {
        $this->user = $user;
        $this->notice = $notice;
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
        $unviewed_notices = $viewed_notices = $d = [];
        $unviewed_count = 0; // Unviewed notices count
        
        $d=[];
        if(Qs::userIsTeamSAT()){
            $d['users'] = $this->user->getAll();
        }
        
        $notices = $this->notice->allExceptAuth()->sortBy("id", SORT_REGULAR, true);
        // Count all users who did not view notice(s) - whose id not in viewers_ids.
        foreach ($notices as $ntc) {
            $v_ids = $ntc->viewers_ids;
            if ($v_ids == NULL || !in_array(auth()->id(), json_decode($v_ids))) {
                $unviewed_count++;
                $unviewed_notices[] = $ntc;
            } else {
                $viewed_notices[] = $ntc;
            }
        }

        $un_paginated = $this->get_paginator($request, $unviewed_notices, "unviewed-notices-page");
        $vn_paginated = $this->get_paginator($request, $viewed_notices, "viewed-notices-page");

        $d["unviewed_count"] = $unviewed_count;

        if ($request->ajax()) {
            if (str_contains($request->fullUrl(), 'unviewed'))
                $d["unviewed_notices"] = $un_paginated;
            else
                $d["viewed_notices"] = $vn_paginated;

            $d['current_url'] = $request->url();

            return view('pages/support_team/notices/show', $d);
        }

        $d["unviewed_notices"] = $un_paginated;
        $d["viewed_notices"] = $vn_paginated;
        
        $d['conversations'] = $this->getRecentConversations();
        // dd($d['conversations']);

        return view('pages.support_team.dashboard', $d);
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
