<?php

namespace App\Listeners\Payments;

use Illuminate\Support\Facades\Log;
use App\Events\Payments\PaymentUpdatedEvent;
use App\Notifications\Transactions\PaymentDeletedNotification;
use App\Notifications\Transactions\PaymentNotification;
use App\Notifications\Transactions\PaymentUpdatedNotification;
use App\User;

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
        if (config('app.debug'))
            Log::info('payment updated', ["payment" => $event->payment]);
        $old_payment = $event->payment->getOriginal();
        $new_payment = $event->payment;
        $group = $event->payment->group;
        $diff = bcsub($new_payment->amount, $old_payment['amount']);

        if ($diff != 0) {
            //amount is different
            $group->addToMemberBalance($new_payment->payer_id, $diff);
            if ($old_payment['taker_id'] == $new_payment->taker_id) {
                //taker is the same
                $group->addToMemberBalance($new_payment->taker_id, (-1) * $diff);
                $user = $new_payment->taker;
                if (auth('api')->user() && $user->id != auth('api')->user()->id) {
                    try {
                        $user->notify(new PaymentUpdatedNotification($new_payment));
                    } catch (\Exception $e) {
                        Log::error('FCM error', ['error' => $e]);
                    }
                }
            }
        }

        if ($old_payment['taker_id'] != $new_payment->taker_id) {
            //taker is different
            $group->addToMemberBalance($old_payment['taker_id'], $old_payment['amount']);
            $group->addToMemberBalance($new_payment->taker_id, (-1) * $new_payment->amount);

            //notify old taker
            $user = User::find($old_payment['taker_id']);
            if (auth('api')->user() && $user->id != auth('api')->user()->id) {
                try {
                    $user->notify(new PaymentDeletedNotification($new_payment));
                } catch (\Exception $e) {
                    Log::error('FCM error', ['error' => $e]);
                }
            }

            //notify new taker
            $user = $new_payment->taker;
            if (auth('api')->user() && $user->id != auth('api')->user()->id) {
                try {
                    $user->notify(new PaymentNotification($new_payment));
                } catch (\Exception $e) {
                    Log::error('FCM error', ['error' => $e]);
                }
            }
        }
    }
}
