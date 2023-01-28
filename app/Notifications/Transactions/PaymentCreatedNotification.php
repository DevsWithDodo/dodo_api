<?php

namespace App\Notifications\Transactions;

use App\Group;
use App\Notifications\NotificationMaker;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;

use App\Transactions\Payment;

class PaymentCreatedNotification extends Notification //implements ShouldQueue
{
    //use Queueable;

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
        return NotificationMaker::makeFcmMessage(
            title: __('notifications.payment.created'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $this->payment->payer_id),
                'to_user' => Group::nicknameOf($group->id, $this->payment->taker_id),
                'amount' => round(floatval($this->payment->amount), 2) . " " . $group->currency,
                'message' => $this->payment->note,
                'group' => $group->name,
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'currency' => $group->currency,
                'details' => 'payment',
                'channel_id' => 'payment_created'
            ],
            channel_id: 'payment_created'
        );
    }
}
