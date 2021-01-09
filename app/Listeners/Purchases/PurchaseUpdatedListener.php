<?php

namespace App\Listeners\Purchases;

use Illuminate\Support\Facades\Log;
use App\Events\Purchases\PurchaseUpdatedEvent;

class PurchaseUpdatedListener
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
     * @param  PurchaseUpdatedEvent  $event
     * @return void
     */
    public function handle(PurchaseUpdatedEvent $event)
    {
        Log::info('purchase updated', ["purchase" => $event->purchase]);
        $old_purchase = $event->purchase->getOriginal();
        $new_purchase = $event->purchase;
        $group = $event->purchase->group;
        $diff = bcsub($new_purchase->amount, $old_purchase['amount']);
        $group->addToMemberBalance($new_purchase->buyer_id, $diff);
    }
}
