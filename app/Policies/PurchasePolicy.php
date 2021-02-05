<?php

namespace App\Policies;

use App\Transactions\Purchase;
use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    public function update(User $user, Purchase $purchase)
    {
        $members = $purchase->group->members;
        if($purchase->receivers->filter(function ($value, $key) use ($members) {
            return !$members->contains($value->receiver_id);
        })->count()){
            return Response::deny(__('errors.update_deleted'));
        }
        return $purchase->buyer->id == $user->id;
    }

    public function delete(User $user, Purchase $purchase)
    {
        $members = $purchase->group->members;
        if($purchase->receivers->filter(function ($value, $key) use ($members) {
            return !$members->contains($value->receiver_id);
        })->count()){
            return Response::deny(__('errors.update_deleted'));
        }
        return $purchase->buyer->id == $user->id;
    }
}
