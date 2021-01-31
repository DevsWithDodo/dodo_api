<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseCreatedEvent;
use App\Group;
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
        $purchase = $event->purchase;
        if (config('app.debug'))
            Log::info('purchase created', ["purchase" => $purchase]);
        Group::addToMemberBalance($purchase->group_id, $purchase->buyer_id, $purchase->amount);
    }
}
