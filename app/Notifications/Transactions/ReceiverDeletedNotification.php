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

class ReceiverDeletedNotification extends Notification //implements ShouldQueue
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
        $group = $this->receiver->group;
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.purchase.deleted'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $this->receiver->purchase->buyer_id),
                'purchase' => $this->receiver->purchase->name,
                'deleted',
                'group' => $group->name
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'currency' => $group->currency,
                'details' => 'purchase'
            ],
            channel_id: 'purchase_deleted'
        );
    }
}
