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

use App\Request;

class FulfilledRequestNotification extends Notification
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $message = "Thanks to " . $this->request->group->members->find($this->request->fulfiller)->member_data->nickname .
             ', ' . $this->request->name . ' is within reach!';
        return FcmMessage::create()
            ->setData(['id' => '2' . rand (0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Request fulfilled in ' . $this->request->group->name)
                ->setBody($message));
    }
}