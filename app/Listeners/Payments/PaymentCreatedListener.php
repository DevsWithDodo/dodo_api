<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentCreatedEvent;
use App\Group;
use App\Notifications\Transactions\PaymentNotification;
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
        $payment = $event->payment;
        if (config('app.debug'))
            Log::info('payment created', ["payment" => $event->payment]);
        Group::addToMemberBalance($payment->group_id, $payment->payer_id, $payment->amount);
        Group::addToMemberBalance($payment->group_id, $payment->taker_id, (-1) * $payment->amount);

        $user = $payment->taker;
        if (auth('api')->user() && $user->id != auth('api')->user()->id) {
            try {
                $user->notify(new PaymentNotification($payment))->locale($user->language);
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }
    }
}
