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

class ShoppingNotification extends Notification
{
    public $group;
    public $user;
    public $store;

    public function __construct(Group $group, User $user, $store)
    {
        $this->user = $user;
        $this->group = $group;
        $this->store = $store;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $message = 'You have been notified that ' .
            $this->group->members->find($this->user)->member_data->nickname .
            ' is shopping in '. $this->store .' right now. Write something to '. $this->group->name . '\'s shopping list if you need something!';
        return FcmMessage::create()
            ->setData(['id' => '8' . rand(0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('Ask something from '. $this->group->members->find($this->user)->member_data->nickname .'!')
                ->setBody($message));
    }
}
