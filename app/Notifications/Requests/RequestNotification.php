<?php

namespace App\Notifications\Requests;

use App\Group;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

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
        $group = $this->request->group;
        $message = __('notifications.new_request_descr', [
            'user' => Group::nicknameOf($group->id, $this->request->requester_id),
            'request' => $this->request->name,
            'group' => $group->name,
        ]);
        $title = __('notifications.new_request_title', [
            'group' => $group->name
        ]);
        return FcmMessage::create()
            ->setData([
                'id' => '7' . rand(0, 100000),
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
