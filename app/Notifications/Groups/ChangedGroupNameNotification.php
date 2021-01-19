<?php

namespace App\Notifications\Groups;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

use App\Group;
use App\User;

class ChangedGroupNameNotification extends Notification
{
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
        $message = __('notifications.changed_group_name_descr', [
            'user' => Group::nicknameOf($this->group->id, $this->user->id),
            'old_name' => $this->old_name,
            'new_name' => $this->new_name
        ]);
        $title = __('notifications.changed_group_name_title');
        return FcmMessage::create()
            ->setData([
                'id' => '0' . rand(0, 100000),
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
