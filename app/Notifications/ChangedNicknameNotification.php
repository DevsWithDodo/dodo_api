<?php

namespace App\Notifications;

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

class ChangedNicknameNotification extends Notification
{
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
        $message = __('notifications.changed_nickname_descr', [
            'user' => $this->group->members->find($this->user)->member_data->nickname,
            'new_name' => $this->new_nickname,
            'group' => $this->group->name
        ]);
        $title = __('notifications.changed_nickname_title', [
            'name' => $this->new_nickname
        ]);
        return FcmMessage::create()
            ->setData([
                'id' => '1' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'home',
                    'group_id' => $this->group->id,
                    'group_name' => $this->group->name,
                    'details' => null
                ]),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
