<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseReceiverDeletedEvent;
use Illuminate\Support\Facades\Log;

class PurchaseReceiverDeletedListener
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
     * @param  PurchaseReceiverDeletedEvent  $event
     * @return void
     */
    public function handle(PurchaseReceiverDeletedEvent $event)
    {
        if (config('app.debug'))
            Log::info('purchase receiver deleted', ["purchase receiver" => $event->receiver]);
        $event->receiver->purchase->group->addToMemberBalance($event->receiver->receiver_id, $event->receiver->amount);
    }
}
