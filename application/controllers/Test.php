<?php
include "Util.php";
include "StringDB.php";
require_once 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Test extends CI_Controller {
	public static $QUEUE_NAME = "my-queue";
	public static $EXCHANGE_NAME = "my-exchange";
	public static $ROUTING_KEY = "my-routing-key";
	
	public function hmac() {
		$API_KEY = "X2ZPdrQEGVdTF5SRGLtF7jV7";
		$API_SECRET = "xYBS8Tx9qtJSFNuQTkvRJku3qpn7BvxKjLvNHeeByy7QGtKNcg25Z47tHE8CyMNFbUBu8pAM24rcvTmkGKp3K9";
		$ACTION = "verify_email";
		$USER_ID = 1;
		$data = "api_key=".$API_KEY."&api_secret=".$API_SECRET."&action=".$ACTION."&user_id=".$USER_ID;
		$key = utf8_encode($API_SECRET);
		$bytes = utf8_encode($data);
		$hmacSha256 = hash_hmac('sha256', $bytes, $key);
		echo base64_encode(utf8_encode($hmacSha256));
	}
	
	public function fcm() {
		$url = "https://fcm.googleapis.com/fcm/send";
		$serverKey = "AAAAw873lJk:APA91bHl4IYArG-tmXyH7bhl03U3yYtv46Ulo6O_AIKsmmu1NEMNsWASjj7MXugKBrFEPkAmS44-Lpboymu-Vb9Tv6znqT5zOFRR7VB0kut66OC31aI4h0VmD1-g1ASzd0nZkXaeOU-Y";
	    $notification = array('title' => "Tes judul" , 'body' => "Tes isi", 'android_channel_id' => 'com.qtracking.test.b2b1228b-1657-40a4-b28c-caea74a6d5c6', 'sound' => 'default', 'vibration' => true, 'badge' => '1');
	    $arrayToSend = array('to' => "eeRawu-TQci1iG-wRN8VzV:APA91bH6nxhti33lNHK7EqsVupb3T5B3kLNgLZkLLT5Snrg5wliNl9w88JLBFhim4WsgAK4-d5mrzxzAz0FuSfqhMMP5xFuuQvxRP1niDIXSkyD0jzeWUaojqkWlYsuniYvILZMHy4ze", 'notification' => $notification, 'android' => array('notification' => array('sound' => 'default', 'vibration' => true)), 'priority' => 'high', 'data' => array(
	    	"screen_path" => "/test_screen",
	    	"screen_name" => "Test Screen"
	    ));
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
	}
	
	public function fcm_test() {
		$url = "https://fcm.googleapis.com/fcm/send";
		$serverKey = "AAAAmJPNk8A:APA91bGIl_Lga0kO6YZ8664L5cm6XdjOZwWofNNf4FXcz6TeF9EsSO6Q1gZfxm7XmnkF1bMnJm34FSFz0jACinln-SDxNZLHQWlZyONrzE2LujXJE8Zs_WrEHTDbI9BwsjRXQU7ZaF5q";
	    $notification = array('title' => "Tes judul" , 'body' => "Tes isi", 'android_channel_id' => 'com.dn.fcmtest.bf9275c1-eaf3-4aae-a729-182d96c86b21', 'sound' => 'default', 'vibration' => true, 'badge' => '1');
	    $arrayToSend = array('to' => "/topics/test", 'notification' => $notification, 'android' => array('notification' => array('sound' => 'default', 'vibration' => true)), 'priority' => 'high', 'data' => array(
	    	"screen_path" => "/test_screen",
	    	"screen_name" => "Test Screen"
	    ));
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
	}
	
	public function fcm_native() {
		$url = "https://fcm.googleapis.com/fcm/send";
		$serverKey = "AAAAmJPNk8A:APA91bGIl_Lga0kO6YZ8664L5cm6XdjOZwWofNNf4FXcz6TeF9EsSO6Q1gZfxm7XmnkF1bMnJm34FSFz0jACinln-SDxNZLHQWlZyONrzE2LujXJE8Zs_WrEHTDbI9BwsjRXQU7ZaF5q";
	    $notification = array('title' => "Tes judul" , 'body' => "Tes isi", 'android_channel_id' => 'qtracking_notifications', 'sound' => 'notification_sound', 'badge' => '1');
	    $arrayToSend = array('to' => "fT6k_auRS8CluCzp02HGvw:APA91bHsiI8EQnOSa-pYo2XoeRwSjybI7JyoV89TqezaBCzkBeXw3NhnNKI0GzuSei__w4tpSI4dFkO05tOOa7uC5PesMWQDtei8kWM9ZGzGXmCE0C_GIwjtoJypCv8rkiPqHIEQzWYL", 'notification' => $notification, 'android' => array('notification' => array('sound' => 'notification_sound')), 'priority' => 'high', 'data' => array(
	    	"screen_path" => "/test_screen",
	    	"screen_name" => "Test Screen"
	    ));
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
	}
	
	public function doa_harian() {
		$data = file_get_contents("userdata/doa_harian.json");
		$doas = json_decode($data, true);
		for ($i=0; $i<sizeof($doas); $i++) {
			$this->db->insert("doa_harian", array(
				"title" => $doas[$i]['judul'],
				"arab" => $doas[$i]['arab'],
				"latin" => $doas[$i]['latin'],
				"meaning" => $doas[$i]['arti'],
				"source" => $doas[$i]['footnote']
			));
		}
	}
	
	public function messages() {
		$time = 0;
		for ($i=0; $i<100; $i++) {
			$this->db->query("INSERT INTO `messages` (`id`, `chat_id`, `sender_id`, `receiver_id`, `message`, `image`, `latitude`, `longitude`, `address`, `message_type`, `date`) VALUES (NULL, '1', '1', '2', 'This is test message " . ($i+1) . ".', NULL, '0', '0', NULL, 'text', '" . date("Y-m-d", time()+$time) . "');");
			$time += 86400;
		}
	}
	
	public function topic_fcm_test() {
		Util::send_fcm_message_to_topic($this, "topic_test", "Ada peserta sedang membutuhkan bantuan", "Klik untuk melihat lokasi peserta sekarang",
			json_encode(array()));
	}
	
	public function distance() {
		$distance = Util::get_distance(-6.206873, 106.8262983, -6.324814, 106.8878763);
		$radius = 100;
		echo "Distance is ".$distance." meters<br/>";
				$inRadius = $this->db->query("SELECT * FROM `in_radius_users` WHERE `place_id`=1 AND `user_id`=1")->result_array();
				$isInRadius = false;
				if (sizeof($inRadius) > 0) {
					$isInRadius = intval($inRadius[0]['is_in_radius'])==1?true:false;
				}
				echo "Is in radius? ".($isInRadius?1:0)."<br/>";
				$this->db->query("DELETE FROM `in_radius_users` WHERE `place_id`=1 AND `user_id`=1");
				if ($distance <= $radius) {
					if (!$isInRadius) {
						$this->db->insert("in_radius_users", array(
							"place_id" => 1,
							"user_id" => 1,
							"is_in_radius" => 1
						));
					}
					Util::send_fcm_message_to_topic_no_notification($this, "group_1",
						json_encode(array(
							"type" => "user_entered_radius",
							"group_id" => "1",
							"user_id" => "1"
						)));
				} else {
					Util::send_fcm_message_to_topic_no_notification($this, "group_1",
						json_encode(array(
							"type" => "user_exited_radius",
							"group_id" => "1",
							"user_id" => "1"
						)));
				}
	}
	
	public function string_test() {
		echo StringDB::get("text1");
	}
	
	public function notification_test() {
		Util::send_fcm_message_to_topic($this, "group_53", "Grup 1", "Tempat telah ditambahkan",
			json_encode(array(
				"type" => "test"
			)));
	}
	
	// appumroh://localhost?data=eyJhY3Rpb24iOiJ2ZXJpZnlfZW1haWwiLCJ0b2tlbiI6IlpHVTVPR1ptWTJObVptUTBNVEUxTWpJNU1XSm1NREJqWldVNVpUaGxORGcwT0RrNE1UQmhZekZpWW1Gak1UVmpOalJpWVRWbE9XVXdOMlppTW1JeFpnPT0iLCJ1c2VyX2lkIjoiMSIsImRhdGEiOnsiZW1haWwiOiJkYW5hb3MuYXBwc0BnbWFpbC5jb20iLCJ2ZXJpZmljYXRpb25fdG9rZW4iOiJNbVEzTmpFMFlUa3RZVGt4TkMwME9UTXpMVGswTkRrdE1HVXdPR1UzWlRWbU5XTTEifX0=
		
	// {"action":"verify_email","token":"ZGU5OGZmY2NmZmQ0MTE1MjI5MWJmMDBjZWU5ZThlNDg0ODk4MTBhYzFiYmFjMTVjNjRiYTVlOWUwN2ZiMmIxZg==","user_id":"1","data":{"email":"danaos.apps@gmail.com","verification_token":"MmQ3NjE0YTktYTkxNC00OTMzLTk0NDktMGUwOGU3ZTVmNWM1"}}
	
	public function rabbitmq_send() {
		$instance = $this;
		$messagingMethod = "rabbitmq";
		$gmt = 7;
		$date = date_create_from_format("Y-m-d H:i:s", '2022-02-26 09:43:00')->format("Uv")+3600000;
		$target = 'user_1';
		$title = "Judul Notifikasi";
		$body = "Isi Notifikasi";
		$payload = array();
		$notification = array('title' => $title , 'body' => $body, 'badge' => 1);
	    $data = $payload;
	    $data['notification'] = $notification;
	    $data['click_action'] = "FLUTTER_NOTIFICATION_CLICK";
	    $data['screen'] = "group_message";
	    $arrayToSend = array('to' => $target, 'notification' => $notification, 'data' => $data);
	    $json = json_encode($arrayToSend);
	    $connection = new AMQPStreamConnection(Util::$RABBITMQ_IP, Util::$RABBITMQ_PORT, Util::$RABBITMQ_USER, Util::$RABBITMQ_PASS);
		$channel = $connection->channel();
		$data = new AMQPMessage("Hello World", array(
            'delivery_mode' => 2,
            'application_headers' => new AMQPTable([
                'x-delay' => 5000
            ])
        ));
        $channel->basic_publish($data, $exchange = Test::$EXCHANGE_NAME, $routing_key = Test::$ROUTING_KEY);
	}
	
	public function rabbitmq_receive() {
		$instance = $this;
		$messagingMethod = "rabbitmq";
		$gmt = 7;
		$date = date_create_from_format("Y-m-d H:i:s", '2022-02-26 09:43:00')->format("Uv")+3600000;
		$target = 'user_1';
		$title = "Judul Notifikasi";
		$body = "Isi Notifikasi";
		$payload = array();
		$notification = array('title' => $title , 'body' => $body, 'badge' => 1);
	    $data = $payload;
	    $data['notification'] = $notification;
	    $data['click_action'] = "FLUTTER_NOTIFICATION_CLICK";
	    $data['screen'] = "group_message";
	    $arrayToSend = array('to' => $target, 'notification' => $notification, 'data' => $data);
	    $json = json_encode($arrayToSend);
	    $connection = new AMQPStreamConnection(Util::$RABBITMQ_IP, Util::$RABBITMQ_PORT, Util::$RABBITMQ_USER, Util::$RABBITMQ_PASS);
		$channel = $connection->channel();
		$channel->exchange_declare(Test::$EXCHANGE_NAME, "direct", $durable = true, $auto_delete = false, $arguments = array("x-delayed-type" => "direct"));
		$channel->queue_declare(Test::$QUEUE_NAME, $durable = false, $exclusive = false, $auto_delete = false, $arguments = null);
		$channel->queue_bind(Test::$QUEUE_NAME, Test::$EXCHANGE_NAME, Test::$ROUTING_KEY);
		$channel->basic_consume(Test::$QUEUE_NAME, $no_ack = true, $callback = function ($consumerTag, $delivery) {
			echo "Received message with tag: ".$consumerTag." and message: ".$delivery."<br/>";
		}, $consumer_tag = "");
	}
	
	public function rabbitmq_direct() {
		$target = "user_1";
		$connection = new AMQPStreamConnection("49.50.10.47", 5672, "test", "test");
		$channel = $connection->channel();
		list($queueName, $messageCount, $consumerCount) = $channel->queue_declare($target, false, false, false, false);
		echo "Consumer count: ".$consumerCount."\n";
		$msg = new AMQPMessage("tes");
		$channel->basic_publish($msg, '', $target);
		$channel->close();
		$connection->close();
	}
	
	public function fcm_notification_click() {
		$url = "https://fcm.googleapis.com/fcm/send";
		$serverKey = Util::get_setting($this, "fcm_server_key");
	    $notification = array('title' => "Tes judul" , 'body' => "Tes isi", 'sound' => 'default', 'badge' => '1');
	    $arrayToSend = array('to' => "dWFnjUFdQ6m2-XKrA9AKo6:APA91bF6Yfhgo5j9-i1TL9LSfzqLabt8YgfUJzrjz8VM7vsJMBEkAZCSJaHSlOIieQ5HEQA4AZwDLLZlHH5H3VHYMPHQjVWbJ0Xrpu7gGL2o80ChaNB-ok12kjO0o84oHTo2pX5fLdRn", 'notification' => $notification, 'priority' => 'high', 'data' => array(
	    	"screen_path" => "/test_screen",
	    	"screen_name" => "Test Screen"
	    ));
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
	}
	
	public function pushy() {
		$serverKey = Util::get_setting($this, "pushy_api_key");
		$url = "https://api.pushy.me/push?api_key=".$serverKey;
	    $notification = array('title' => "Tes judul" , 'body' => "Tes isi", 'sound' => 'default', 'badge' => 1);
	    $arrayToSend = array('to' => "/topics/test_topic", 'notification' => $notification, 'priority' => 'high', 'data' => array(
	    	"screen_path" => "/test_screen",
	    	"screen_name" => "Test Screen",
	    	'notification' => $notification
	    ));
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
	}
	
	public function schedule() {
		Util::send_message($this, 2, "Tes judul", "Tes isi", json_encode(array(
			"user_id" => "2",
			"title" => "Tes judul",
			"body" => "Tes isi",
			"group_id" => "54",
			"type" => "schedule",
			"payload" => array(
				"type" => "schedule"
			)
		)));
	}
	
	public function redis() {
		$redis = new Redis([
			'host' => '192.168.22.187',
		    'port' => 6380
		]);
		$redis->publish('group_2', 'Tes pesan');
	}
	
	public function email() {
		$email = "admin@dev.jtindonesia.com";
		$subject = $this->input->post('subject');
		$message = $this->input->post('message');
    	$headers = 'From: '.$email."\r\n" .
                 'Reply-To: '.$email. "\r\n" .
                 'X-Mailer: PHP/' . phpversion();
        mail("danaoscompany@gmail.com", $subject, $message, $headers);
	}
	
	public function time() {
		$now = DateTime::createFromFormat('U.u', microtime(true));
		echo "Time: ".($now*1000)."<br/>";
		echo "Time (GMT+7): ".(($now+(7*60*60))*1000)."<br/>";
	}
	
	public function date() {
		echo "Date: " . strtotime('2022-02-25 15:43:00');
	}
}

