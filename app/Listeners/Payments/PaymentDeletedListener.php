<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentDeletedEvent;
use App\Notifications\Transactions\PaymentDeletedNotification;
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
        $payment = $event->payment;
        if (config('app.debug'))
            Log::info('payment deleted', ["payment" => $event->payment]);
        $payment->group->addToMemberBalance($payment->payer_id, (-1) * $payment->amount);
        $payment->group->addToMemberBalance($payment->taker_id, $payment->amount);

        $user = $payment->taker;
        if (auth('api')->user() && $user->id != auth('api')->user()->id) {
            try {
                $user->notify(new PaymentDeletedNotification($payment));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }
    }
}
