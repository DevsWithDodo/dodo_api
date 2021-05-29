<?php

namespace App\Notifications\Groups;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use App\Notifications\NotificationMaker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Group;
use App\User;


class ChangedGroupNameNotification extends Notification // implements ShouldQueue
{
    //use Queueable;

    public Group $group;
    public User $user;
    public $new_name;
    public $old_name;

    public function __construct(Group $group, User $user, $old_name, $new_name)
    {
        $this->user = $user;
        $this->group = $group;
        $this->new_name = $new_name;
        $this->old_name = $old_name;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.group.name.updated'),
            message_parts: [
                'user' => Group::nicknameOf($this->group->id, $this->user->id),
                'group' => $this->old_name,
                'changed' => $this->new_name
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $this->group->id,
                'group_name' => $this->group->name,
                'currency' => $this->group->currency,
                'details' => null,
                'channel_id' => 'group_update'
            ],
            channel_id: 'group_update'
        );
    }
}
