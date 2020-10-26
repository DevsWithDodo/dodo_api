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
    public $receiver;

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
        $message = $this->receiver->purchase->group->members->find($this->receiver->purchase->buyer)->member_data->nickname .
         ' bought you ' . $this->receiver->purchase->name . ' for ' . $this->receiver->amount . ' HUF.'; 
        return FcmMessage::create()
            ->setData(['id' => '6' . rand (0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('New purchase in ' . $this->receiver->purchase->group->name)
                ->setBody($message));
    }
}