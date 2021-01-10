<?php

namespace App\Events\Purchases;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Transactions\PurchaseReceiver;

class PurchaseReceiverCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PurchaseReceiver $receiver;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PurchaseReceiver $receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}