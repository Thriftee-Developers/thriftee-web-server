<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $user;
    public function __construct(public string $customer, public string $store, public string $sender, public string $content)
    {
        //
        $this->user = $store;
        if ($sender === "store") {
            $this->user = $customer;
        }
    }

    public function broadcastOn()
    {
        return new Channel("message");
        // return new Channel("message");
    }

    public function broadcastAs()
    {
        return "user-" . $this->user;
    }
}
