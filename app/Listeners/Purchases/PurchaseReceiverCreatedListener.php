<?php

namespace App\Listeners\Purchases;

use App\Events\Purchases\PurchaseReceiverCreatedEvent;
use App\Group;
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
        Group::addToMemberBalance($receiver->group_id, $receiver->receiver_id, (-1) * $receiver->amount);
        if (auth('api')->user() && $receiver->receiver_id != auth('api')->user()->id) {
            try {
                $receiver->user->notify(new ReceiverNotification($receiver))->locale($receiver->user->language);
            } catch (\Exception $e) {
                Log::error('FCM error', ['error' => $e]);
            }
        }
    }
}
