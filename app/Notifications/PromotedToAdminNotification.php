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

class PromotedToAdminNotification extends Notification
{
    public $group;
    public $admin;

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
        $message = $this->group->members->find($this->admin)->member_data->nickname . ' promoted you to be an admin.';
        return FcmMessage::create()
            ->setData(['id' => '5' . rand (0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('You became an admin in ' . $this->group->name)
                ->setBody($message));
    }
}