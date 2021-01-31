<?php

namespace App\Notifications\Transactions;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Transactions\PurchaseReceiver;

use App\Group;

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
        $message = __('notifications.receiver_updated_descr', [
            'user' => Group::nicknameOf($group->id, $purchase->buyer->id),
            'purchase' => $purchase->name,
            'amount' => round(floatval($this->receiver->amount), 2) . " " . $group->currency,
        ]);
        $title = __('notifications.receiver_updated_title');
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
