<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseDeletedEvent;
use App\Group;
use Illuminate\Support\Facades\Log;

class PurchaseDeletedListener
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
     * @param  PurchaseDeletedEvent  $event
     * @return void
     */
    public function handle(PurchaseDeletedEvent $event)
    {
        $purchase = $event->purchase;
        if (config('app.debug'))
            Log::info('purchase deleted', ["purchase" => $purchase]);
        Group::addToMemberBalance($purchase->group_id, $purchase->buyer_id, (-1) * $purchase->amount);
    }
}
