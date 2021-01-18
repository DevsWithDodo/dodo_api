<?php

namespace App\Notifications\Requests;

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
    public Group $group;
    public User $user;
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
        $message = __('notifications.shopping_descr', [
            'user' => $this->group->members->find($this->user)->member_data->nickname,
            'store' => $this->store,
            'group' => $this->group->name
        ]);
        $title = __('notifications.shopping_title', [
            'user' => $this->group->members->find($this->user)->member_data->nickname
        ]);
        return FcmMessage::create()
            ->setData([
                'id' => '8' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'shopping',
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
