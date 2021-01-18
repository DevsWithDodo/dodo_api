<?php

namespace App\Notifications\Groups;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

use App\Group;
use App\User;

class GroupBoostedNotification extends Notification
{
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
            'notifications.group_boosted_descr',
            [
                'user' => Group::nicknameOf($this->group->id, $this->user->id),
                'group' => $this->group->name
            ]
        );
        $title = __(
            'notifications.group_boosted_title',
            [
                'user' => Group::nicknameOf($this->group->id, $this->user->id),
                'group' => $this->group->name
            ]
        );
        return FcmMessage::create()
            ->setData([
                'id' => '' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'home',
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
