<?php

namespace App\Observers;

use App\Transactions\Purchase;
use App\Group;
use App\Notifications\Transactions\PurchaseCreatedNotification;
use App\Notifications\Transactions\PurchaseDeletedNotification;
use App\Notifications\Transactions\PurchaseUpdatedNotification;
use App\User;
use Illuminate\Support\Facades\Log;

class PurchaseObserver
{
    /**
     * Handle the Purchase "created" event.
     *
     * @param  \App\Transactions\Purchase  $purchase
     * @return void
     */
    public function created(Purchase $purchase)
    {
        if (config('app.debug'))
            Log::info('purchase created', ["purchase" => $purchase]);
        Group::addToMemberBalance($purchase->group_id, $purchase->buyer_id, $purchase->amount);
        $purchase->buyer->sendNotification(new PurchaseCreatedNotification($purchase));
    }

    /**
     * Handle the Purchase "updated" event.
     *
     * @param  \App\Transactions\Purchase  $purchase
     * @return void
     */
    public function updated(Purchase $purchase)
    {
        $old_purchase = $purchase->getOriginal();
        $group = $purchase->group;
        if (config('app.debug'))
            Log::info('purchase updated', ["purchase" => $purchase, "old purchase" => $old_purchase]);

        if($old_purchase['buyer_id'] == $purchase->buyer_id) {
            $diff = bcsub($purchase->amount, $old_purchase['amount']);
            if($diff != 0) {
                Group::addToMemberBalance($group->id, $purchase->buyer_id, $diff);
                $purchase->buyer->sendNotification(new PurchaseUpdatedNotification($purchase));
            }
        } else {
            Group::addToMemberBalance($purchase->group_id, $old_purchase['buyer_id'], (-1) * $old_purchase['amount']);
            Group::addToMemberBalance($purchase->group_id, $purchase->buyer_id, $purchase->amount);
             //notify old buyer
             $buyer = User::find($old_purchase['buyer_id']);
             if($buyer) $buyer->sendNotification(new PurchaseDeletedNotification($purchase));

             //notify new buyer
             $purchase->buyer->sendNotification(new PurchaseCreatedNotification($purchase));
        }
    }

    /**
     * Handle the Purchase "deleted" event.
     *
     * @param  \App\Transactions\Purchase  $purchase
     * @return void
     */
    public function deleted(Purchase $purchase)
    {
        if (config('app.debug'))
            Log::info('purchase deleted', ["purchase" => $purchase]);
        Group::addToMemberBalance($purchase->group_id, $purchase->buyer_id, (-1) * $purchase->amount);
    }

    /**
     * Handle the Purchase "restored" event.
     *
     * @param  \App\Transactions\Purchase  $purchase
     * @return void
     */
    public function restored(Purchase $purchase)
    {
        //
    }

    /**
     * Handle the Purchase "force deleted" event.
     *
     * @param  \App\Transactions\Purchase  $purchase
     * @return void
     */
    public function forceDeleted(Purchase $purchase)
    {
        //
    }
}
