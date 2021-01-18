<?php

namespace App\Notifications\Transactions;

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
    public Payment $payment;

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
        $message = __('notifications.new_payment_descr', [
            'user' =>  $this->payment->group->members->find($this->payment->payer_id)->member_data->nickname,
            'amount' => round(floatval($this->payment->amount), 2) . " " . $this->payment->group->currency,
            'group' => $this->payment->group->name
        ]);
        if ($this->payment->note)
            $message .= ' ' . __('notifications.new_payment_message', [
                'message' => $this->payment->note
            ]);

        $title = __('notifications.new_payment_title', [
            'group' => $this->payment->group->name
        ]);
        return FcmMessage::create()
            ->setData([
                'id' => '4' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'home',
                    'group_id' => $this->payment->group->id,
                    'group_name' => $this->payment->group->name,
                    'details' => 'payment'
                ]),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
