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
    public $customer;
    public $store;
    public function __construct($customer, $store, public string $sender, public string $content)
    {
        //
        $this->customer = $customer;
        $this->store = $store;
    }

    public function broadcastOn()
    {
        // return new Channel($this->customer . "" . $this->store);
        return new Channel("message");
    }

    public function broadcastAs()
    {
        return 'message';
    }
}
