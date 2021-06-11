<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentDeletedEvent;
use App\Group;
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
        Group::addToMemberBalance($payment->group_id, $payment->payer_id, (-1) * $payment->amount);
        Group::addToMemberBalance($payment->group_id, $payment->taker_id, $payment->amount);
        $user = $payment->taker;
        if (auth('api')->user() && $user->id != auth('api')->user()->id) {
            try {
                $user->notify((new PaymentDeletedNotification($payment))->locale($user->language));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }
    }
}
