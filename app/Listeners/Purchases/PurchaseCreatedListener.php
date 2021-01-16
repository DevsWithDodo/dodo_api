<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseCreatedEvent;
use Illuminate\Support\Facades\Log;
use App\Transactions\Purchase;

class PurchaseCreatedListener
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
     * @param  PurchaseCreatedEvent  $event
     * @return void
     */
    public function handle(PurchaseCreatedEvent $event)
    {
        if (config('app.debug'))
            Log::info('purchase created', ["purchase" => $event->purchase]);
        $event->purchase->group->addToMemberBalance($event->purchase->buyer_id, $event->purchase->amount);
    }
}
