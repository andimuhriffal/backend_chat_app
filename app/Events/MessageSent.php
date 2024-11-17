<?php
// app/Events/MessageSent.php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Import ShouldBroadcast
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    // Menentukan channel tempat event ini akan disiarkan
    public function broadcastOn()
    {
        return new Channel('chat.' . $this->message->receiver_id);
    }

    // Menentukan nama event untuk Pusher atau broadcasting lainnya
    public function broadcastAs()
    {
        return 'message.sent';
    }
}
