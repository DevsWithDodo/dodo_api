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

use App\Transactions\Payment;

class PaymentNotification extends Notification
{
    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $message = $this->payment->group->members->find($this->payment->payer_id)->member_data->nickname . ' payed you ' . $this->payment->amount . ' HUF in ' . $this->payment->group->name . ($this->payment->note ? ".\nNote: ".$this->payment->note : '.'); 
        return FcmMessage::create()
            ->setData(['id' => '0' . rand (0, 100000)])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle('New payment')
                ->setBody($message));
    }
}