<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseReceiverDeletedEvent;
use App\Group;
use App\Notifications\Transactions\ReceiverDeletedNotification;
use Illuminate\Support\Facades\Log;

class PurchaseReceiverDeletedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PurchaseReceiverDeletedEvent  $event
     * @return void
     */
    public function handle(PurchaseReceiverDeletedEvent $event)
    {
        $receiver = $event->receiver;
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
}
