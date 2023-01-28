<?php

namespace App\Notifications\Transactions;

use App\Group;
use App\Notifications\NotificationMaker;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;

use App\Transactions\Purchase;

class PurchaseDeletedNotification extends Notification //implements ShouldQueue
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
            title: __('notifications.purchase.deleted'),
            message_parts: [
                'purchase' => $this->purchase->name,
                'amount' => round(floatval($this->purchase->amount), 2) . " " . $group->currency,
                'deleted',
                'group' => $group->name,
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'currency' => $group->currency,
                'details' => 'purchase',
                'channel_id' => 'purchase_deleted'
            ],
            channel_id: 'purchase_deleted'
        );
    }
}
