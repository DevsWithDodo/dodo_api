<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class CustomNotification extends Notification
{
    public $message;
    public $screen;

    public function __construct($message, $screen = null)
    {
        $this->message = $message;
        $this->screen = $screen;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $title = __('notifications.message_from_developers');
        $message = $this->message;
        return FcmMessage::create()
            ->setData([
                'id' => '9' . rand(0, 100000),
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
