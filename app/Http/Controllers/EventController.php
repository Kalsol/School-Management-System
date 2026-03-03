<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Repositories\EventRepo;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $event;

    public function __construct(EventRepo $event)
    {
        $this->event = $event;
    }

    public function index()
    {
        $data['events'] = $this->event->all();
        $data['selected'] = false;
        
        return view('pages.schedule.index', $data);
    }

    public function create_event(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = auth()->id();
        $data['status'] = 'new';
        
        $event = $this->event->create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Event "' . $data['name'] . '" added successfully.',
            'event' => $event
        ]);
    }

    public function edit_event($event_id)
    {
        $d['event'] = $this->event->getRecord(['id' => $event_id])->first();
        $d['events'] = $this->event->all();
        $d['selected'] = true;

        return view('pages.schedule.index', $d);
    }

    public function update_event(Request $request, $event_id)
    {
        $this->event->update($event_id, $request->except("_token"));

        return redirect()->route('schedule.index')
            ->with('flash_success', 'Event updated successfully!');
    }

    public function delete_event(Request $req)
    {
        // Prevent deleting the first event if it's required
        if ($req->id === 1) {
            return back()->with('pop_warning', 'Cannot delete required event.');
        }

        $this->event->delete($req->only("id"));

        return back()->with('flash_success', __('msg.del_ok'));
    }
}