<?php
define('API_ACCESS_KEY','AAAA-NiL4iU:APA91bG80Zp0kg_dWh06TgS42CWoZrYoD6crH8pA3-uOzX59LDtNW68RUDmme4dGL5_pd78L_pcAqt5wv4OYbSSHc6juHTwcIhtWMhO0suMIZgBLfB1oMsR852pzwqa6nF6Ewx1_DPxd');
$fcmUrl = 'https://fcm.googleapis.com/fcm/send';
$token='1068784935461';
$notification = [
  'title' =>'title',
  'body' => 'body of message.',
  'icon' =>'myIcon', 
  'sound' => 'mySound'
];
$extraNotificationData = ["message" => $notification,"moredata" =>'dd'];

$fcmNotification = [
  //'registration_ids' => $tokenList, //multple token array
  'to'        => $token, //single token
  'notification' => $notification,
  'data' => $extraNotificationData
];

$headers = [
  'Authorization: key=' . API_ACCESS_KEY,
  'Content-Type: application/json'
];


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$fcmUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
$result = curl_exec($ch);
curl_close($ch);

echo $result;

?>