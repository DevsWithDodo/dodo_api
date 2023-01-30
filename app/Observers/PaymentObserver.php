<?php

namespace App\Observers;

use App\User;
use App\Group;
use App\Transactions\Payment;
use App\Notifications\Transactions\PaymentCreatedNotification;
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

        $payment->taker->sendNotification(new PaymentCreatedNotification($payment));
        $payment->payer->sendNotification(new PaymentCreatedNotification($payment));
    }

    /**
     * Handle the Payment "updated" event.
     *
     * @param  \App\Transactions\Payment  $payment
     * @return void
     */
    public function updated(Payment $payment)
    {
        $old_payment = $payment->getOriginal();
        $diff = bcsub($payment->amount, $old_payment['amount']);

        if (config('app.debug'))
            Log::info('payment updated', ["payment" => $payment, "old payment" => $payment->getOriginal()]);

        if ($old_payment['payer_id'] != $payment->payer_id) {
            //modify payer balances
            Group::addToMemberBalance($payment->group_id, $old_payment['payer_id'], (-1) * $old_payment['amount']);
            Group::addToMemberBalance($payment->group_id, $payment->payer_id, $payment->amount);

            //notify old payer
            $payer = User::find($old_payment['payer_id']);
            if($payer) $payer->sendNotification(new PaymentDeletedNotification($payment));

            //notify new payer
            $payment->payer->sendNotification(new PaymentCreatedNotification($payment));
        } else if ($diff != 0) {
            Group::addToMemberBalance($payment->group_id, $payment->payer_id, $diff);
            $payment->payer->sendNotification(new PaymentUpdatedNotification($payment));
        }

        if($old_payment['taker_id'] != $payment->taker_id) {
            //modify taker balances
            Group::addToMemberBalance($payment->group_id, $old_payment['taker_id'], $old_payment['amount']);
            Group::addToMemberBalance($payment->group_id, $payment->taker_id, (-1) * $payment->amount);

            //notify old taker
            $taker = User::find($old_payment['taker_id']);
            if($taker) $taker->sendNotification(new PaymentDeletedNotification($payment));

            //notify new taker
            $payment->taker->sendNotification(new PaymentCreatedNotification($payment));
        } else if ($diff != 0) {
            Group::addToMemberBalance($payment->group_id, $payment->taker_id, (-1) * $diff);
            $payment->taker->sendNotification(new PaymentUpdatedNotification($payment));
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
        $payment->taker->sendNotification(new PaymentDeletedNotification($payment));
        $payment->payer->sendNotification(new PaymentDeletedNotification($payment));
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
