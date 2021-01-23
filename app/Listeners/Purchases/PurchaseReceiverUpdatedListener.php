<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseReceiverUpdatedEvent;
use App\Notifications\Transactions\ReceiverUpdatedNotification;
use Illuminate\Support\Facades\Log;

class PurchaseReceiverUpdatedListener
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
     * @param  PurchaseReceiverUpdatedEvent  $event
     * @return void
     */
    public function handle(PurchaseReceiverUpdatedEvent $event)
    {
        $old_receiver = $event->receiver->getOriginal();
        $new_receiver = $event->receiver;
        $diff = bcsub($old_receiver['amount'], $new_receiver->amount);
        if ($diff != 0) {
            if (config('app.debug'))
                Log::info('purchase receiver updated', ["diff" => $diff, "receiver" => $new_receiver, 'old receiver' => $old_receiver]);
            $new_receiver->group->addToMemberBalance($new_receiver->receiver_id, $diff);

            $user = $new_receiver->user;
            if (auth('api')->user() && $user->id != auth('api')->user()->id)
                try {
                    $user->notify(new ReceiverUpdatedNotification($new_receiver));
                } catch (\Exception $e) {
                    Log::error('FCM error', ['error' => $e]);
                }
        }
    }
}
