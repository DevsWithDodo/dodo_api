<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class TrialEndedNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $message = __('notifications.trial_ended_descr');
        $title = __('notifications.trial_ended_title');
        return FcmMessage::create()
            ->setData([
                'id' => '' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'store'
                ]),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
