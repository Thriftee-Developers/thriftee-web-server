<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bidding;
    public function __construct($bidding, public string $customer, public string $amount, public string $noOfBid)
    {
        //
        $this->bidding = $bidding;
    }

    public function broadcastOn()
    {
        return new Channel("bidding");
    }

    public function broadcastAs()
    {
        return "bidding-" . $this->bidding;
    }
}
