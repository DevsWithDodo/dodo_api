<?php

namespace App\Notifications\Transactions;

use App\Group;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

use App\Transactions\Payment;

class PaymentUpdatedNotification extends Notification
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
        $group = $this->payment->group;
        $message = __('notifications.updated_payment_descr', [
            'user' =>  Group::nicknameOf($group->id, $this->payment->payer_id),
            'old_amount' => round(floatval($this->payment->getOriginal('amount')), 2) . " " . $group->currency,
            'new_amount' => round(floatval($this->payment->amount), 2) . " " . $group->currency,
        ]);

        $title = __('notifications.updated_payment_title');
        return FcmMessage::create()
            ->setData([
                'id' => '4' . rand(0, 100000),
                'payload' => json_encode([
                    'screen' => 'home',
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'details' => 'payment'
                ]),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message));
    }
}
