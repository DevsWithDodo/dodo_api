<?php

namespace App\Notifications\Requests;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Group;
use App\Notifications\NotificationMaker;
use App\User;


class ShoppingNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

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
        $message = __('notifications.shopping.descr', [
            'user' => Group::nicknameOf($this->group->id, $this->user->id),
            'store' => $this->store,
            'group' => $this->group->name
        ]);
        $title = __('notifications.shopping.title', [
            'user' => Group::nicknameOf($this->group->id, $this->user->id),
        ]);
        return NotificationMaker::makeFcmMessage(
            title: $title,
            message_parts: [$message],
            payload: [
                'screen' => 'shopping',
                'group_id' => $this->group->id,
                'group_name' => $this->group->name,
                'details' => null
            ],
        );
    }
}
