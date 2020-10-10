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

class RequestNotification extends Notification
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
        $message = $this->request->group->members->find($this->request->requester_id)->member_data->nickname . 
            ' added ' . $this->request->name . ' to the shopping list.'; 
        return FcmMessage::create()
            ->setData(['id' => '7' . rand (0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('New shopping request in ' . $this->request->group->name)
                ->setBody($message));
    }
}