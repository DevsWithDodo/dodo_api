<?php

namespace App\Notifications\Transactions;

use App\Group;
use App\Notifications\NotificationMaker;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Transactions\PurchaseReceiver;


class ReceiverCreatedNotification extends Notification //implements ShouldQueue
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
        $group = $this->receiver->purchase->group;
        $purchase = $this->receiver->purchase;
        $receivers = $purchase->receivers->map(function($receiver) use ($group) {
            return Group::nicknameOf($group->id, $receiver->receiver_id);
        });
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.purchase.created'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $purchase->buyer_id),
                'to_user' => $receivers->implode(', '),
                'purchase' => $purchase->name,
                'amount' => round(floatval($this->receiver->amount), 2) . " " . $group->currency,
                'group' => $group->name
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'currency' => $group->currency,
                'details' => 'purchase',
                'channel_id' => 'purchase_created'
            ],
            channel_id: 'purchase_created'
        );
    }
}
