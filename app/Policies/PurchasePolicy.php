<?php

namespace App\Policies;

use App\Transactions\Purchase;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    public function update(User $user, Purchase $purchase)
    {
        return $purchase->buyer->id == $user->id;
    }

    public function delete(User $user, Purchase $purchase)
    {
        return $purchase->buyer->id == $user->id;
    }
}
