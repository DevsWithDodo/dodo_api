<?php

namespace App\Notifications\Transactions;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Transactions\PurchaseReceiver;
use App\Group;

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
        $message = __('notifications.receiver_deleted_descr', [
            'user' => Group::nicknameOf($group->id, $this->receiver->purchase->buyer_id),
            'purchase' => $this->receiver->purchase->name
        ]);
        $title = __('notifications.receiver_deleted_title');
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
