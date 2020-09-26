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
    public $group;
    public $user;
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
        $message = $this->group->members->find($this->user)->member_data->nickname .
             ' set your nickname to ' . $this->new_nickname . ' in ' . $this->group->name;
        return FcmMessage::create()
            ->setData(['id' => '6' . rand (0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Call me ' . $this->new_nickname . '!')
                ->setBody($message));
    }
}