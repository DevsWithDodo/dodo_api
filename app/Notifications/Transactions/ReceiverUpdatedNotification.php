<?php

namespace App\Notifications\Transactions;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Transactions\PurchaseReceiver;

use App\Group;
use App\Notifications\NotificationMaker;

class ReceiverUpdatedNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

    public PurchaseReceiver $receiver;

    public function __construct(PurchaseReceiver $receiver)
    {
        $this->receiver = $receiver;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $purchase = $this->receiver->purchase;
        $group = $purchase->group;
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.purchase.updated'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $purchase->buyer_id),
                'purchase' => $purchase->name,
                'amount' => round(floatval($this->receiver->getOriginal('amount')), 2) . " " . $group->currency,
                'changed' => round(floatval($this->receiver->amount), 2) . " " . $group->currency,
                'group' => $group->name
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'details' => 'purchase'
            ]
        );
    }
}
