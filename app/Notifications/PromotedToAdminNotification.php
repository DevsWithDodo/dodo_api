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
    public Group $group;
    public User $admin;

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
        $message = __('notifications.promoted_to_admin_descr', [
            'user' => $message = $this->group->members->find($this->admin)->member_data->nickname,
            'group' => $this->group->name
        ]);
        $title = __('notifications.promoted_to_admin_title', [
            'group' => $this->group->name
        ]);
        return FcmMessage::create()
            ->setData([
                'id' => '5' . rand(0, 100000),
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
