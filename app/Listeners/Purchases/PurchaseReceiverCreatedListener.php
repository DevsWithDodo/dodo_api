<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseReceiverCreatedEvent;
use Illuminate\Support\Facades\Log;
use App\Transactions\PurchaseReceiver;

class PurchaseReceiverCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PurchaseReceiverCreatedEvent  $event
     * @return void
     */
    public function handle(PurchaseReceiverCreatedEvent $event)
    {
        if (config('app.debug'))
            Log::info('purchase receiver created', ["purchase receiver" => $event->receiver]);
        $event->receiver->purchase->group->addToMemberBalance($event->receiver->receiver_id, (-1) * $event->receiver->amount);
    }
}
