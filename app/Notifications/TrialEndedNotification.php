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
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.trial_ended.title'),
            message_parts: [__('notifications.trial_ended.descr')],
            payload: ['screen' => 'store'],
        );
    }
}
