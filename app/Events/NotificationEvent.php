<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public function __construct($user, public string $type, public string $content)
    {
        //
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new Channel('channel-name');
    }

    public function broadcastAs()
    {
        return "notification-" . $this->user;
    }
}
