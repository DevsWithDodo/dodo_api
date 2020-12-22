<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

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
        $message = __('notifications.receiver_notification_descr', [
            'user' => $this->receiver->purchase->group->members->find($this->receiver->purchase->buyer)->member_data->nickname,
            'purchase' => $this->receiver->purchase->name,
            'amount' => $this->receiver->amount . " " . $this->receiver->purchase->group->currency,
            'group' => $this->receiver->purchase->group->name
        ]);
        $title = __('notifications.receiver_notification_title', [
            'group' => $this->receiver->purchase->group->name
        ]);
        return FcmMessage::create()
            ->setData([
                'id' => '6' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'home',
                    'group_id' => $this->receiver->purchase->group->id,
                    'group_name' => $this->receiver->purchase->group->name,
                    'details' => 'purchase'
                ]),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
