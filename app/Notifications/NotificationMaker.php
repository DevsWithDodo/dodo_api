<?php

namespace App\Notifications;

use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidNotification;

class NotificationMaker
{
    const signs = [
        'group' => "👥",
        'user' => "👤",
        'to_user' => "➡",
        'purchase' => "🛒",
        'amount' => "💰",
        'request_new' => "🙄",
        'request_fulfilled' => "✅",
        'changed' => "➡",
        'message' => "💬",
        'deleted' => "🗑️"
    ];

    public static function makeFcmMessage($title, $message_parts, $payload, $channel_id = null)
    {
        $message = "";
        $i = 0;
        foreach (array_filter($message_parts) as $key => $value) {
            if ($value == 'deleted') {
                $message .= " " . self::signs['deleted'];
                continue;
            }
            if($i++) {
                $message .= (in_array($key, ['to_user', 'changed'])) ? " " : "\n";
            }
            if ($sign = self::signs[$key] ?? false) {
                $message .= $sign . " " . $value;
            } else {
                $message .= $value;
            }
        }
        return FcmMessage::create()
            ->setData([
                'id' => "" . rand(0, 100000),
                'payload' => json_encode($payload),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ])
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($title)
                ->setBody($message))
            ->setAndroid(
                AndroidConfig::create()
                    ->setNotification(AndroidNotification::create()->setChannelId($channel_id))
            );
    }
}
