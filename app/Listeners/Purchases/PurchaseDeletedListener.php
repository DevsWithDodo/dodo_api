<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseDeletedEvent;
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
        if (config('app.debug'))
            Log::info('purchase deleted', ["purchase" => $event->purchase]);
        $event->purchase->group->addToMemberBalance($event->purchase->buyer_id, (-1) * $event->purchase->amount);
    }
}
