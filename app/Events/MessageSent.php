<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\ShouldBroadcastNow;
use Illuminate\Bus\Queueable;

use App\Models\Message;

// Add the ShouldBroadcast interface here
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, Queueable, InteractsWithSockets, SerializesModels, InteractsWithBroadcasting;

    public $message; // Make this public so Echo can see it

    public function __construct(Message $message) 
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Broadcast to the receiver's private channel
        return new PrivateChannel('chat.' . $this->message->receiver_id);
    }

    // public function broadcastOn()
    // {
    //     return ['my-channel'];
    // }

    // public function broadcastAs()
    // {
    //     return '';
    // }
}
