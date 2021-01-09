<?php

namespace App\Listeners\Payments;

use Illuminate\Support\Facades\Log;
use App\Events\Payments\PaymentUpdatedEvent;

class PaymentUpdatedListener
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
     * @param  PaymentUpdatedEvent  $event
     * @return void
     */
    public function handle(PaymentUpdatedEvent $event)
    {
        Log::info('payment updated', ["payment" => $event->payment]);
        $old_payment = $event->payment->getOriginal();
        $new_payment = $event->payment;
        $group = $event->payment->group;
        $diff = bcsub($new_payment->amount, $old_payment['amount']);
        $group->addToMemberBalance($new_payment->payer_id, $diff);
        $group->addToMemberBalance($old_payment['taker_id'], $old_payment['amount']);
        $group->addToMemberBalance($new_payment->taker_id, (-1) * $new_payment->amount);
    }
}
