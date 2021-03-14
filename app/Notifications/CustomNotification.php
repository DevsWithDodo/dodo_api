<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class CustomNotification extends Notification
{
    public function __construct($message, $title = null, $payload = null, $channel_id = null, $log = false)
    {
        $this->message = $message;
        $this->payload = $payload ?? [];
        $this->channel_id = $channel_id;
        $this->title = $title;
        $this->log = $log;

        //add channel id to payload also
        $this->payload[] = ['channel_id' => $channel_id];
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $notification =  NotificationMaker::makeFcmMessage(
            title: $this->title ?? __('notifications.message_from_developers'),
            message_parts: [$this->message],
            payload: $this->payload,
            channel_id: $this->channel_id ?? 'other'
        );
        if ($this->log) echo var_dump($notification);
        return $notification;
    }
}
