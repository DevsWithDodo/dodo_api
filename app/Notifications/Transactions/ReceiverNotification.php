<?php

namespace App\Notifications\Transactions;

use App\Group;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

use App\Transactions\PurchaseReceiver;

class ReceiverNotification extends Notification
{
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
        $message = __('notifications.receiver_notification_descr', [
            'user' => Group::nicknameOf($group->id, $purchase->buyer_id),
            'purchase' => $purchase->name,
            'amount' => round(floatval($this->receiver->amount), 2) . " " . $group->currency,
            'group' => $group->name
        ]);
        $title = __('notifications.receiver_notification_title', [
            'group' => $group->name
        ]);
        return FcmMessage::create()
            ->setData([
                'id' => '6' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'home',
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'details' => 'purchase'
                ]),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
