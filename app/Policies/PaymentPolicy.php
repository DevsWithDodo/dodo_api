<?php

namespace App\Policies;

use App\Transactions\Payment;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Payment $payment)
    {
        return $payment->payer->id == $user->id;
    }

    public function delete(User $user, Payment $payment)
    {
        return $payment->payer->id == $user->id;
    }
}
