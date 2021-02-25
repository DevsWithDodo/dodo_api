<?php

namespace App\Notifications\Members;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Group;
use App\Notifications\NotificationMaker;
use App\User;

class ChangedNicknameNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

    public Group $group;
    public User $user;
    public $new_nickname;

    public function __construct(Group $group, User $user, $new_nickname)
    {
        $this->group = $group;
        $this->user = $user;
        $this->new_nickname = $new_nickname;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.user.nickname_changed.title', [
                'name' => $this->new_nickname
            ]),
            message_parts: [
                'user' => Group::nicknameOf($this->group->id, $this->user->id),
                __('notifications.nickname_changed.descr'),
                'group' => $this->group->name,
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $this->group->id,
                'group_name' => $this->group->name,
                'details' => null
            ]
        );
    }
}
