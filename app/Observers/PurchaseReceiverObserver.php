<?php

namespace App\Observers;

use App\Group;
use App\Transactions\PurchaseReceiver;
use App\Notifications\Transactions\ReceiverNotification;
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
        if (auth('api')->user() && $receiver->receiver_id != auth('api')->user()->id) {
            try {
                $receiver->user->notify((new ReceiverNotification($receiver))->locale($receiver->user->language));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }
    }

    /**
     * Handle the PurchaseReceiver "updated" event.
     *
     * @param  \App\Transactions\PurchaseReceiver  $receiver
     * @return void
     */
    public function updated(PurchaseReceiver $receiver)
    {
        $old_receiver = $receiver->getOriginal();
        $diff = bcsub($old_receiver['amount'], $receiver->amount);
        if ($diff != 0) {
            if (config('app.debug'))
                Log::info('purchase receiver updated', ["diff" => $diff, "receiver" => $receiver, 'old receiver' => $old_receiver]);
            Group::addToMemberBalance($receiver->group_id, $receiver->receiver_id, $diff);

            $user = $receiver->user;
            if (auth('api')->user() && $user->id != auth('api')->user()->id)
                try {
                    $user->notify((new ReceiverUpdatedNotification($receiver))->locale($user->language));
                } catch (\Exception $e) {
                    Log::error('FCM error', ['error' => $e]);
                }
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

        $user = $receiver->user;
        if (auth('api')->user() && $user->id != auth('api')->user()->id)
            try {
                $user->notify((new ReceiverDeletedNotification($receiver))->locale($user->language));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
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
