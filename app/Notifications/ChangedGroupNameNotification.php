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

class ChangedGroupNameNotification extends Notification
{
    public $group;
    public $user;
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
        $message = $this->group->members->find($this->user)->member_data->nickname .
            ' set ' . $this->old_name . '\'s name to ' . $this->new_name;
        return FcmMessage::create()
            ->setData(['id' => '0' . rand(0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Changed group name')
                ->setBody($message));
    }
}
