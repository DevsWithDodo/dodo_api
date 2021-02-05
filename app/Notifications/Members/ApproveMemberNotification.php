<?php

namespace App\Notifications\Members;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Group;
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
        $message = __('notifications.approve_member_descr', [
            'user' => $this->user->username,
            'group' => $this->group->name
        ]);
        $title = __('notifications.approve_member_title', [
            'group' => $this->group->name
        ]);
        return FcmMessage::create()
            ->setData([
                'id' => '5' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'group_settings',
                    'group_id' => $this->group->id,
                    'group_name' => $this->group->name,
                    'details' => null
                ]),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
