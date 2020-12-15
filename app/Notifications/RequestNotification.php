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
    public Request $request;

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
        $message = __('notifications.new_request_descr', [
            'user' => $this->request->group->members->find($this->request->requester_id)->member_data->nickname,
            'purchase' => $this->request->name
        ]);
        $title = __('notifications.new_request_title', [
            'group' => $this->request->group
        ]);
        return FcmMessage::create()
            ->setData(['id' => '7' . rand(0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
