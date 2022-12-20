<?php

namespace App\Observers;

use App\User;
use App\Group;
use App\Transactions\Payment;
use App\Notifications\Transactions\PaymentNotification;
use App\Notifications\Transactions\PaymentUpdatedNotification;
use App\Notifications\Transactions\PaymentDeletedNotification;
use Illuminate\Support\Facades\Log;


class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     *
     * @param  \App\Transactions\Payment  $payment
     * @return void
     */
    public function created(Payment $payment)
    {
        if (config('app.debug'))
            Log::info('payment created', ["payment" => $payment]);
        Group::addToMemberBalance($payment->group_id, $payment->payer_id, $payment->amount);
        Group::addToMemberBalance($payment->group_id, $payment->taker_id, (-1) * $payment->amount);

        $user = $payment->taker;
        if (auth('api')->user() && $user->id != auth('api')->user()->id) {
            try {
                $user->notify((new PaymentNotification($payment))->locale($user->language));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }


    }

    /**
     * Handle the Payment "updated" event.
     *
     * @param  \App\Transactions\Payment  $payment
     * @return void
     */
    public function updated(Payment $payment)
    {
        if (config('app.debug'))
            Log::info('payment updated', ["payment" => $payment]);
        $old_payment = $payment->getOriginal();
        $group = $payment->group;
        $diff = bcsub($payment->amount, $old_payment['amount']);

        if ($diff != 0) {
            //amount is different
            Group::addToMemberBalance($group->id, $payment->payer_id, $diff);
            if ($old_payment['taker_id'] == $payment->taker_id) {
                //taker is the same
                Group::addToMemberBalance($group->id, $payment->taker_id, (-1) * $diff);
                $user = $payment->taker;
                if (auth('api')->user() && $user->id != auth('api')->user()->id) {
                    try {
                        $user->notify((new PaymentUpdatedNotification($payment))->locale($user->language));
                    } catch (\Exception $e) {
                        Log::error('FCM error', ['error' => $e]);
                    }
                }
            }
        }

        if ($old_payment['taker_id'] != $payment->taker_id) {
            //taker is different
            Group::addToMemberBalance($group->id, $old_payment['taker_id'], $old_payment['amount']);
            Group::addToMemberBalance($group->id, $payment->taker_id, (-1) * $payment->amount);

            //notify old taker
            $user = User::find($old_payment['taker_id']);
            if (auth('api')->user() && $user->id != auth('api')->user()->id) {
                try {
                    $user->notify((new PaymentDeletedNotification($payment))->locale($user->language));
                } catch (\Exception $e) {
                    Log::error('FCM error', ['error' => $e]);
                }
            }

            //notify new taker
            $user = $payment->taker;
            if (auth('api')->user() && $user->id != auth('api')->user()->id) {
                try {
                    $user->notify((new PaymentNotification($payment))->locale($user->language));
                } catch (\Exception $e) {
                    Log::error('FCM error', ['error' => $e]);
                }
            }
        }
    }

    /**
     * Handle the Payment "deleted" event.
     *
     * @param  \App\Transactions\Payment  $payment
     * @return void
     */
    public function deleted(Payment $payment)
    {
        if (config('app.debug'))
            Log::info('payment deleted', ["payment" => $payment]);
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

    /**
     * Handle the Payment "restored" event.
     *
     * @param  \App\Transactions\Payment  $payment
     * @return void
     */
    public function restored(Payment $payment)
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     *
     * @param  \App\Transactions\Payment  $payment
     * @return void
     */
    public function forceDeleted(Payment $payment)
    {
        //
    }
}
