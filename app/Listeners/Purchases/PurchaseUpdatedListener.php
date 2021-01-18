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
        $old_purchase = $event->purchase->getOriginal();
        $new_purchase = $event->purchase;
        $group = $event->purchase->group;
        $diff = bcsub($new_purchase->amount, $old_purchase['amount']);
        if ($diff != 0) {
            if (config('app.debug'))
                Log::info('purchase updated', ["purchase" => $event->purchase]);
            $group->addToMemberBalance($new_purchase->buyer_id, $diff);
        }
    }
}
