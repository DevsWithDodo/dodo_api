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
use App\User;

class FulfilledRequestNotification extends Notification
{
    public Request $request;
    public User $fulfiller;

    public function __construct(Request $request, User $fulfiller)
    {
        $this->request = $request;
        $this->fulfiller = $fulfiller;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $message = __('notifications.fulfilled_request_descr', [
            'user' => $this->request->group->members->find($this->fulfiller)->member_data->nickname,
            'request' => $this->request->name
        ]);
        $title = __('notifications.fulfilled_request_title');
        return FcmMessage::create()
            ->setData([
                'id' => '2' . rand(0, 100000),
                "screen" => $this->request->group->id.";shopping",
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
