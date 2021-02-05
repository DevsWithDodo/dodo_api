<?php

namespace App\Policies;

use App\Transactions\Payment;
use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Payment $payment)
    {
        if(!$payment->group->members->contains($payment->taker_id)){
            return Response::deny(__('errors.update_deleted'));
        }
        return $payment->payer->id == $user->id;
    }

    public function delete(User $user, Payment $payment)
    {
        if(!$payment->group->members->contains($payment->taker_id)){
            return Response::deny(__('errors.update_deleted'));
        }
        return $payment->payer->id == $user->id;
    }
}
