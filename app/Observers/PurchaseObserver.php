<?php

namespace App\Observers;

use App\Transactions\Purchase;
use App\Group;
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
        $diff = bcsub($purchase->amount, $old_purchase['amount']);
        if ($diff != 0) {
            if (config('app.debug'))
                Log::info('purchase updated', ["purchase" => $purchase]);
            Group::addToMemberBalance($group->id, $purchase->buyer_id, $diff);
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
