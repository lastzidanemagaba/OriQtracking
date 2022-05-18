<?php
$url = "https://fcm.googleapis.com/fcm/send";
$serverKey = 'AAAAw873lJk:APA91bHl4IYArG-tmXyH7bhl03U3yYtv46Ulo6O_AIKsmmu1NEMNsWASjj7MXugKBrFEPkAmS44-Lpboymu-Vb9Tv6znqT5zOFRR7VB0kut66OC31aI4h0VmD1-g1ASzd0nZkXaeOU-Y';
$arrayToSend = array('to' => '/topics/update_location', 'priority' => 'high', 'data' => array("type" => "update_location"));
$json = json_encode($arrayToSend);
$headers = array();
$headers[] = 'Content-Type: application/json';
$headers[] = 'Authorization: key='. $serverKey;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$response = curl_exec($ch);
echo $response;
if ($response === FALSE) {
}
curl_close($ch);
