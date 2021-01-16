<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentDeletedEvent;
use Illuminate\Support\Facades\Log;

class PaymentDeletedListener
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
     * @param  PaymentDeletedEvent  $event
     * @return void
     */
    public function handle(PaymentDeletedEvent $event)
    {
        if (config('app.debug'))
            Log::info('payment deleted', ["payment" => $event->payment]);
        $event->payment->group->addToMemberBalance($event->payment->payer_id, (-1) * $event->payment->amount);
        $event->payment->group->addToMemberBalance($event->payment->taker_id, $event->payment->amount);
    }
}
