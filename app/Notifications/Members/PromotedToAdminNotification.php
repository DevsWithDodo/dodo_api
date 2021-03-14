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


class PromotedToAdminNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

    public Group $group;
    public User $admin;

    public function __construct(Group $group, User $admin)
    {
        $this->group = $group;
        $this->admin = $admin;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $message = __('notifications.user.promoted_to_admin.descr', [
            'user' => Group::nicknameOf($this->group->id, $this->admin->id),
            'group' => $this->group->name
        ]);
        $title = __('notifications.user.promoted_to_admin.title', [
            'group' => $this->group->name
        ]);
        return NotificationMaker::makeFcmMessage(
            title: $title,
            message_parts: [$message],
            payload: [
                'screen' => 'home',
                'group_id' => $this->group->id,
                'group_name' => $this->group->name,
                'currency' => $this->group->currency,
                'details' => null
            ],
            channel_id: 'group_update'
        );
    }
}
