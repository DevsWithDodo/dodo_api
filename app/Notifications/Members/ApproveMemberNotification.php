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


class ApproveMemberNotification extends Notification //implements ShouldQueue
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
        $message = __('notifications.user.approve.descr', [
            'user' => $this->user->username,
            'group' => $this->group->name
        ]);
        $title = __('notifications.user.approve.title', [
            'group' => $this->group->name
        ]);
        return NotificationMaker::makeFcmMessage(
            title: $title,
            message_parts: [$message],
            payload: [
                'screen' => 'group_settings',
                'group_id' => $this->group->id,
                'group_name' => $this->group->name,
                'currency' => $this->group->currency,
                'details' => null
            ],
        );
    }
}
