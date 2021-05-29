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


class ApprovedJoinGroupNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

    public Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $message = __('notifications.user.approved.descr');
        $title = __('notifications.user.approved.title', [
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
                'details' => "added_to_group",
                'channel_id' => 'group_update'
            ],
            channel_id: 'group_update'
        );
    }
}
