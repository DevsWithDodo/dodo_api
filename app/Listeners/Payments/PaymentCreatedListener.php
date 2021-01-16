<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentCreatedEvent;
use Illuminate\Support\Facades\Log;

class PaymentCreatedListener
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
     * @param  PaymentCreatedEvent  $event
     * @return void
     */
    public function handle(PaymentCreatedEvent $event)
    {
        if (config('app.debug'))
            Log::info('payment created', ["payment" => $event->payment]);
        $event->payment->group->addToMemberBalance($event->payment->payer_id, $event->payment->amount);
        $event->payment->group->addToMemberBalance($event->payment->taker_id, (-1) * $event->payment->amount);
    }
}
