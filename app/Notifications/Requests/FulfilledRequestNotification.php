<?php

namespace App\Notifications\Requests;

use App\Group;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

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
        $group = $this->request->group;
        $message = __('notifications.fulfilled_request_descr', [
            'user' => Group::nicknameOf($group, $this->fulfiller->id),
            'request' => $this->request->name
        ]);
        $title = __('notifications.fulfilled_request_title');
        return FcmMessage::create()
            ->setData([
                'id' => '2' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'shopping',
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'details' => null
                ]),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
