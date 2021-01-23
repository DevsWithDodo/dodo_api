<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseReceiverCreatedEvent;
use App\Notifications\Transactions\ReceiverNotification;
use Illuminate\Support\Facades\Log;

class PurchaseReceiverCreatedListener
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
     * @param  PurchaseReceiverCreatedEvent  $event
     * @return void
     */
    public function handle(PurchaseReceiverCreatedEvent $event)
    {
        $receiver = $event->receiver;
        if (config('app.debug'))
            Log::info('purchase receiver created', ["purchase receiver" => $receiver]);
        $receiver->group->addToMemberBalance($receiver->receiver_id, (-1) * $receiver->amount);
        $user = $receiver->user;
        if (auth('api')->user() && $user->id != auth('api')->user()->id) {
            try {
                $user->notify(new ReceiverNotification($receiver));
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }
    }
}
