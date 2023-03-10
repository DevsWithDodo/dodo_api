<?php

namespace App\Notifications\Groups;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Group;
use App\Notifications\NotificationMaker;
use App\User;

class JoinedGroupNotification extends Notification //implements ShouldQueue
{
    //use Queueable;
    public Group $group;
    public User $user;

    public function __construct(Group $group, User $user)
    {
        $this->group = $group;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $message = __(
            'notifications.user.joined.descr',
            [
                'user' => Group::nicknameOf($this->group->id, $this->user->id),
                'group' => $this->group->name
            ]
        );
        $title = __(
            'notifications.user.joined.title',
            [
                'group' => $this->group->name
            ]
        );
        return NotificationMaker::makeFcmMessage(
            title: $title,
            message_parts: [$message],
            payload: [
                'screen' => 'home',
                'group_id' => $this->group->id,
                'group_name' => $this->group->name,
                'currency' => $this->group->currency,
                'channel_id' => 'group_update',
            ],
            channel_id: 'group_update'
        );
    }
}
