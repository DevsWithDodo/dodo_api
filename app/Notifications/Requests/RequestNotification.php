<?php

namespace App\Notifications\Requests;

use App\Group;
use App\Notifications\NotificationMaker;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Request;


class RequestNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

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
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.request.created'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $this->request->requester_id),
                'request_new' => $this->request->name,
                'group' => $group->name,
            ],
            payload: [
                'screen' => 'shopping',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'currency' => $group->currency,
                'details' => null
            ],
            channel_id: 'shopping_created'
        );
    }
}
