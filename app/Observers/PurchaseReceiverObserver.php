<?php

namespace App\Observers;

use App\Group;
use App\Transactions\PurchaseReceiver;
use App\Notifications\Transactions\ReceiverCreatedNotification;
use App\Notifications\Transactions\ReceiverUpdatedNotification;
use App\Notifications\Transactions\ReceiverDeletedNotification;
use Illuminate\Support\Facades\Log;

class PurchaseReceiverObserver
{
    /**
     * Handle the PurchaseReceiver "created" event.
     *
     * @param  \App\Transactions\PurchaseReceiver  $receiver
     * @return void
     */
    public function created(PurchaseReceiver $receiver)
    {
        if (config('app.debug'))
            Log::info('purchase receiver created', ["purchase receiver" => $receiver]);
        Group::addToMemberBalance($receiver->group_id, $receiver->receiver_id, (-1) * $receiver->amount);
        $receiver->user->sendNotification(new ReceiverCreatedNotification($receiver));
    }

    /**
     * Handle the PurchaseReceiver "updated" event.
     * The receiver_id should not be changed. In that case, a new receiver should be created.
     *
     * @param  \App\Transactions\PurchaseReceiver  $receiver
     * @return void
     */
    public function updated(PurchaseReceiver $receiver)
    {
        $old_receiver = $receiver->getOriginal();
        if (config('app.debug'))
            Log::info('purchase receiver updated', ["receiver" => $receiver, 'old receiver' => $old_receiver]);
        $diff = bcsub($old_receiver['amount'], $receiver->amount);
        if ($diff != 0) {
            Group::addToMemberBalance($receiver->group_id, $receiver->receiver_id, $diff);
            $receiver->user->sendNotification(new ReceiverUpdatedNotification($receiver));
        }
    }

    /**
     * Handle the PurchaseReceiver "deleted" event.
     *
     * @param  \App\Transactions\PurchaseReceiver  $receiver
     * @return void
     */
    public function deleted(PurchaseReceiver $receiver)
    {
        if (config('app.debug'))
            Log::info('purchase receiver deleted', ["purchase receiver" => $receiver]);
        Group::addToMemberBalance($receiver->group_id, $receiver->receiver_id, $receiver->amount);

        $receiver->user->sendNotification(new ReceiverDeletedNotification($receiver));
    }

    /**
     * Handle the PurchaseReceiver "restored" event.
     *
     * @param  \App\Transactions\PurchaseReceiver  $receiver
     * @return void
     */
    public function restored(PurchaseReceiver $receiver)
    {
        //
    }

    /**
     * Handle the PurchaseReceiver "force deleted" event.
     *
     * @param  \App\Transactions\PurchaseReceiver  $receiver
     * @return void
     */
    public function forceDeleted(PurchaseReceiver $receiver)
    {
        //
    }
}
