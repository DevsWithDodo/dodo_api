<?php

namespace App\Notifications\Transactions;

use App\Group;
use App\Notifications\NotificationMaker;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;

use App\Transactions\Purchase;

class PurchaseCreatedNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

    public Purchase $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $group = $this->purchase->group;
        $receivers = $this->purchase->receivers->map(function($receiver) use ($group) {
            return Group::nicknameOf($group->id, $receiver->receiver_id);
        });
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.purchase.created'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $this->purchase->buyer_id),
                'to_user' => $receivers->implode(', '),
                'purchase' => $this->purchase->name,
                'amount' => round(floatval($this->purchase->amount), 2) . " " . $group->currency,
                'group' => $group->name,
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
