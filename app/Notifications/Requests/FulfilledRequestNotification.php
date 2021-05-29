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
use App\User;


class FulfilledRequestNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

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
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.request.fulfilled'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $this->fulfiller->id),
                'request_fulfilled' => $this->request->name,
                'group' => $group->name,
            ],
            payload: [
                'screen' => 'shopping',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'currency' => $group->currency,
                'details' => null,
                'channel_id' => 'shopping_fulfilled'
            ],
            channel_id: 'shopping_fulfilled'
        );
    }
}
