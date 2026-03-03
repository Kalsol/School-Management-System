<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Message;
use App\Models\StudentRecord;
use App\Repositories\MyClassRepo;
use App\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    protected $my_class;

    public function __construct(MyClassRepo $my_class)
    {
        $this->my_class = $my_class;
    }

    public function index(Request $request)
    {
        $data = $this->getCommonData($request);
        $data['chat_users'] = $this->getParentUsers($request);

        return view('pages.support_team.messages.chat', $data);
    }

    public function show(Request $request, $id = null)
    {
        $raw_id = $id ?? $request->segment(3);
        $user_id = Qs::decodeHash($raw_id) ?: $raw_id;
    
        Message::where('sender_id', $user_id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        $data = $this->getCommonData($request);
        $data['chat_users'] = $this->getParentUsers($request);
        $data['active_user'] = User::find($user_id);
    
        if (!$data['active_user']) {
            return $request->ajax()
                ? response()->json(['error' => "User not found"], 404)
                : back()->with('flash_danger', 'User not found');
        }
    
        $data['messages'] = Message::where(function ($q) use ($user_id) {
                $q->where('sender_id', auth()->id())->where('receiver_id', $user_id);
            })->orWhere(function ($q) use ($user_id) {
                $q->where('sender_id', $user_id)->where('receiver_id', auth()->id());
            })->orderBy('created_at', 'asc')->get();
    
        return view('pages.support_team.messages.single_chat', $data);
    }

    private function getCommonData(Request $request)
    {
        return [
            'my_classes' => $this->my_class->all(),
            'sections' => $this->my_class->getAllSections(),
            'selected' => $request->has('my_class_id'),
            'my_class_id' => $request->my_class_id,
            'section_id' => $request->section_id,
        ];
    }

    private function getParentUsers(Request $request)
    {
        $auth_id = auth()->id();
    
        if (Qs::userIsTeamSA() || Qs::userIsAdmin()) {
            $class_id = $request->my_class_id;
            $section_id = $request->section_id;
            if($class_id == ''){
                $section_id = null;
            }
    
            // If either filter is present, we filter via StudentRecord
            if ($class_id || $section_id) {
                $userIds = StudentRecord::whereNotNull('my_parent_id')
                    ->when($class_id, function($q) use ($class_id) {
                        return $q->where('my_class_id', $class_id);
                    })
                    ->when($section_id, function($q) use ($section_id) {
                        return $q->where('section_id', $section_id);
                    })
                    ->pluck('my_parent_id')
                    ->unique();
    
                $query = User::whereIn('id', $userIds);
            } else {
                // No filter: Get every user registered as a parent
                $query = User::where('user_type', 'parent');
            }
        } else {
            // If the logged-in user is a Parent/Teacher, show Admin/Super Admin to contact
            $query = User::whereIn('user_type', ['admin', 'super_admin']);
        }
    
        // Apply the sorting and "Last Message" logic in one query
        return $query->addSelect([
            'last_interaction' => Message::select('created_at')
                ->where(function ($q) use ($auth_id) {
                    $q->where(function($inner) use ($auth_id) {
                        $inner->whereColumn('sender_id', 'users.id')
                              ->where('receiver_id', $auth_id);
                    })->orWhere(function($inner) use ($auth_id) {
                        $inner->whereColumn('receiver_id', 'users.id')
                              ->where('sender_id', $auth_id);
                    });
                })
                ->latest()
                ->take(1)
        ])
        ->orderByDesc('last_interaction')
        ->get();
    }

    public function sendMessage(Request $request)
    {
        $request->validate(['receiver_id' => 'required', 'message' => 'required']);

        $msg = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);

        broadcast(new MessageSent($msg))->toOthers();

        return response()->json(['status' => 'Message Sent!', 'message' => $msg]);
    }
}