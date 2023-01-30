<?php

namespace App\Notifications\Transactions;

use App\Group;
use App\Notifications\NotificationMaker;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;

use App\Transactions\Purchase;

class PurchaseUpdatedNotification extends Notification //implements ShouldQueue
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
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.purchase.updated'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $this->purchase->buyer_id),
                'purchase' => $this->purchase->name,
                'amount' => round(floatval($this->purchase->getOriginal('amount')), 2) . " " . $group->currency,
                'changed' => round(floatval($this->purchase->amount), 2) . " " . $group->currency,
                'group' => $group->name,
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'currency' => $group->currency,
                'details' => 'purchase',
                'channel_id' => 'purchase_modified'
            ],
            channel_id: 'purchase_modified'
        );
    }
}
