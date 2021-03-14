<?php

namespace App\Notifications\Transactions;

use App\Group;
use App\Notifications\NotificationMaker;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Transactions\Payment;


class PaymentUpdatedNotification extends Notification //implements ShouldQueue
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
            title: __('notifications.payment.updated'),
            message_parts: [
                'user' => Group::nicknameOf($group->id, $this->payment->payer_id),
                'amount' => round(floatval($this->payment->getOriginal('amount')), 2) . " " . $group->currency,
                'changed' => round(floatval($this->payment->amount), 2) . " " . $group->currency,
                'group' => $group->name,
            ],
            payload: [
                'screen' => 'home',
                'group_id' => $group->id,
                'group_name' => $group->name,
                'currency' => $group->currency,
                'details' => 'payment'
            ],
            channel_id: 'payment_modified'
        );
    }
}
