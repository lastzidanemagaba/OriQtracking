<?php
include "Constants.php";
require_once 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Util {
	public static $RABBITMQ_IP = "192.168.22.187";
	//public static $RABBITMQ_IP = "49.50.10.47";
	//public static $RABBITMQ_IP = "localhost";
	public static $RABBITMQ_PORT = 5672;
	public static $RABBITMQ_USER = "test";
	public static $RABBITMQ_PASS = "test";
	//public static $REDIS_IP = "192.168.22.187";
	public static $REDIS_IP = "49.50.10.47";
	public static $REDIS_PORT = 6380;
	
	public static function generateUUID() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	        mt_rand( 0, 0xffff ),
	        mt_rand( 0, 0x0fff ) | 0x4000,
	        mt_rand( 0, 0x3fff ) | 0x8000,
		    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    );
	}
	
	public static function generateHMAC($userID, $action, $value) {
		$key = utf8_encode(Constants::$API_SECRET);
		$bytes = utf8_encode($value);
		$hmacSha256 = hash_hmac('sha256', $bytes, $key);
		return base64_encode(utf8_encode($hmacSha256));
	}
	
	public static function get_setting($instance, $settingName) {
		$setting = $instance->db->query("SELECT * FROM `settings` WHERE `name`='".$settingName."'")->row_array();
		return $setting['value'];
	}
	
	public static function get_user_setting($instance, $userID, $settingName) {
		$setting = $instance->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$userID." AND `name`='".$settingName."'")->row_array();
		if ($setting == NULL) return "";
		return $setting['value'];
	}
	
	public static function send_message($instance, $userID, $title, $body, $payload) {
		$url = "https://fcm.googleapis.com/fcm/send";
		$user = $instance->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		$messagingMethod = Util::get_setting($instance, "messaging_method");
		if ($messagingMethod == "fcm") {
			if ($user != NULL) {
				Util::send_fcm_message($instance, $user['notification_channel_id'], $user['fcm_key'], $title, $body, $payload);
			}
		} else if ($messagingMethod == "pushy") {
			if ($user != NULL) {
				Util::send_pushy_message($instance, $user['pushy_token'], $title, $body, $payload);
			}
		} else if ($messagingMethod == "rabbitmq" || $messagingMethod == "mix") {
			Util::send_amqp_message_remote($instance, $messagingMethod, null, "user_".$userID, $title, $body, $payload);
		} else if ($messagingMethod == "redis") {
			Util::send_redis_message($instance, "user_".$user['id'], $title, $body, $payload);
		}
	}
	
	public static function send_message_to_topic($instance, $topic, $title, $body, $payload) {
		$url = "https://fcm.googleapis.com/fcm/send";
		$messagingMethod = Util::get_setting($instance, "messaging_method");
		//echo "Messaging method: ".$messagingMethod."\n";
		if ($messagingMethod == "fcm") {
			Util::send_fcm_message($instance, $topic, "/topics/".$topic, $title, $body, $payload);
		} else if ($messagingMethod == "pushy") {
			Util::send_pushy_message($instance, "/topics/".$topic, $title, $body, $payload);
		} else if ($messagingMethod == "rabbitmq" || $messagingMethod == "mix") {
			Util::send_amqp_message_to_topic_remote($instance, $messagingMethod, $topic, $title, $body, $payload);
		} else if ($messagingMethod == "redis") {
			Util::send_redis_message($instance, $topic, $title, $body, $payload);
		}
	}
	
	public static function send_message_no_notification($instance, $userID, $payload) {
		$url = "https://fcm.googleapis.com/fcm/send";
		$messagingMethod = Util::get_setting($instance, "messaging_method");
		$user = $instance->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		if ($messagingMethod == "fcm") {
			if ($user != NULL) {
				Util::send_fcm_message_no_notification($instance, $user['fcm_key'], $payload);
			}
		} else if ($messagingMethod == "pushy") {
			if ($user != NULL) {
				Util::send_pushy_message_no_notification($instance, $user['pushy_token'], $payload);
			}
		} else if ($messagingMethod == "rabbitmq" || $messagingMethod == "mix") {
			Util::send_amqp_message_no_notification_remote($instance, $messagingMethod, "user_".$userID, $payload);
		} else if ($messagingMethod == "redis") {
			Util::send_redis_message_no_notification($instance, "user_".$userID, $payload);
		}
	}
	
	public static function send_message_to_topic_no_notification($instance, $topic, $payload) {
		$url = "https://fcm.googleapis.com/fcm/send";
		$messagingMethod = Util::get_setting($instance, "messaging_method");
		if ($messagingMethod == "fcm") {
			Util::send_fcm_message_no_notification($instance, "/topics/".$topic, $payload);
		} else if ($messagingMethod == "pushy") {
			Util::send_pushy_message_no_notification($instance, "/topics/".$topic, $payload);
		} else if ($messagingMethod == "rabbitmq" || $messagingMethod == "mix") {
			Util::send_amqp_message_to_topic_no_notification_remote($instance, $messagingMethod, $topic, $payload);
		} else if ($messagingMethod == "redis") {
			Util::send_redis_message_no_notification($instance, $topic, $payload);
		}
	}
	
	public static function send_fcm_message($instance, $androidChannelID, $target, $title, $body, $payload) {
		$url = "https://fcm.googleapis.com/fcm/send";
		$serverKey = Util::get_setting($instance, "fcm_server_key");
	    $notification = array('title' => $title , 'body' => $body, 'sound' => 'notification_sound', 'badge' => '1', "icon" => "notification_icon");
	    if ($androidChannelID != NULL) {
	    	$notification['android_channel_id'] = $androidChannelID;
	    }
	    $arrayToSend = array('to' => $target, 'notification' => $notification, 'android' => array('notification' => array('sound' => 'notification_sound')), 'priority' => 'high', 'data' => json_decode($payload, true));
	    $headers = array();
	    $headers[] = 'Authorization: key='. $serverKey;
	    $headers[] = 'Content-Type: application/json';
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    //echo $response;
	    curl_close($ch);
	}
	
	public static function send_pushy_message($instance, $pushyToken, $title, $body, $payload) {
		$settings = $instance->db->get("settings")->row_array();
		$pushyApiKey = Util::get_setting($instance, "pushy_api_key");
		$url = "https://api.pushy.me/push?api_key=".$pushyApiKey;
	    $notification = array('title' => $title , 'body' => $body, 'badge' => 1);
	    $data = json_decode($payload, true);
	    $data['notification'] = $notification;
	    $arrayToSend = array('to' => $pushyToken, 'notification' => $notification, 'data' => $data);
	    $headers = array();
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    curl_close($ch);
	}
	
	public static function send_amqp_message($instance, $messagingMethod, $target, $title, $body, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$notification = array('title' => $title , 'body' => $body, 'badge' => 1);
	    $data = json_decode($payload, true);
	    $data['notification'] = $notification;
	    $data['click_action'] = "FLUTTER_NOTIFICATION_CLICK";
	    $data['screen'] = "group_message";
	    $arrayToSend = array('to' => $target, 'notification' => $notification, 'data' => $data);
	    $json = json_encode($arrayToSend);
	    $headers = array();
	    $connection = new AMQPStreamConnection(Util::$RABBITMQ_IP, Util::$RABBITMQ_PORT, Util::$RABBITMQ_USER, Util::$RABBITMQ_PASS);
		$channel = $connection->channel();
        $message = new AMQPMessage($json);
        $channel->basic_publish($message, $exchange = Test::$EXCHANGE_NAME, $routing_key = Test::$ROUTING_KEY);
		list($queueName, $messageCount, $consumerCount) = $channel->queue_declare($target, false, false, false, false);
		if ($consumerCount > 0) {
			for ($j=0; $j<$consumerCount; $j++) {
				$channel->basic_publish($message, $exchange = Test::$EXCHANGE_NAME, $routing_key = Test::$ROUTING_KEY);
			}
			$channel->close();
			$connection->close();
		} else {
			if ($messagingMethod == "mix") {
				Util::send_fcm_message($instance, "com.qtracking.test.".$target, $target, $title, $body, $payload);
			}
		}
		return true;
	}
	
	public static function send_amqp_message_to_user_ids($instance, $messagingMethod, $topicName, $userIDs, $title, $body, $payload) {
		// $userIDs = 1, 2, 3, 4, 5, ...
		$notification = array('title' => $title , 'body' => $body, 'badge' => 1);
	    $data = json_decode($payload, true);
	    $data['notification'] = $notification;
	    $data['click_action'] = "FLUTTER_NOTIFICATION_CLICK";
	    $data['screen'] = "group_message";
	    $arrayToSend = array('to' => $topicName, 'notification' => $notification, 'data' => $data);
	    $json = json_encode($arrayToSend);
	    $headers = array();
	    $connection = new AMQPStreamConnection(Util::$RABBITMQ_IP, Util::$RABBITMQ_PORT, Util::$RABBITMQ_USER, Util::$RABBITMQ_PASS);
		$channel = $connection->channel();
        $message = new AMQPMessage($json);
		for ($i=0; $i<sizeof($userIDs); $i++) {
			list($queueName, $messageCount, $consumerCount) = $channel->queue_declare("user_".$userIDs[$i], false, false, false, false);
			if ($consumerCount > 0) {
				for ($j=0; $j<$consumerCount; $j++) {
					$channel->basic_publish($message, $exchange = Test::$EXCHANGE_NAME, $routing_key = Test::$ROUTING_KEY);
				}
			} else {
				if ($messagingMethod == "mix") {
					$userInfo = $instance->db->where("id", $userIDs[$i])->get("users")->row_array();
					Util::send_fcm_message($instance, $userInfo['notification_channel_id'], "user_".$userIDs[$i], $title, $body, $payload);
				}
			}
		}
		$channel->close();
		$connection->close();
		return true;
	}
	
	public static function send_amqp_message_remote($instance, $messagingMethod, $target, $title, $body, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$url = "http://49.50.10.47/admin/user/send_amqp_message";
		$arrayToSend = array(
			'messaging_method' => $messagingMethod,
	    	'target' => $target,
	    	'title' => $title,
	    	'body' => $body,
	    	'payload' => $payload
	    );
	    $headers = array();
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    //echo $response;
	    curl_close($ch);
	}
	
	public static function send_amqp_message_to_topic_remote($instance, $messagingMethod, $target, $title, $body, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$url = "http://49.50.10.47/admin/user/send_amqp_message_to_topic";
		$subscribers = $instance->db->query("SELECT * FROM `rabbitmq_topics` WHERE `topic`='".$target."'")->result_array();
		$userIDs = [];
		for ($i=0; $i<sizeof($subscribers); $i++) {
			array_push($userIDs, intval($subscribers[$i]['user_id']));
		}
		$arrayToSend = array(
			'messaging_method' => $messagingMethod,
	    	'target' => $target,
	    	'title' => $title,
	    	'body' => $body,
	    	'payload' => $payload,
	    	'user_ids' => json_encode($userIDs)
	    );
	    $headers = array();
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    //echo $response;
	    curl_close($ch);
	}
	
	public static function send_amqp_message_no_notification($instance, $messagingMethod, $target, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$data = json_decode($payload, true);
	    $arrayToSend = array('to' => $target, 'data' => $data);
	    $json = json_encode($arrayToSend);
	    $headers = array();
	    $connection = new AMQPStreamConnection(Util::$RABBITMQ_IP, Util::$RABBITMQ_PORT, Util::$RABBITMQ_USER, Util::$RABBITMQ_PASS);
		$channel = $connection->channel();
		list($queueName, $messageCount, $consumerCount) = $channel->queue_declare($target, false, false, false, false);
		if ($consumerCount > 0) {
			for ($j=0; $j<$consumerCount; $j++) {
				$msg = new AMQPMessage($json);
				$channel->basic_publish($msg, '', $target);
			}
		} else {
			if ($messagingMethod == "mix") {
				Util::send_fcm_message($instance, "com.qtracking.test.".$target, $target, $title, $body, $payload);
			}
		}
		$channel->close();
		$connection->close();
		return true;
	}
	
	public static function send_amqp_message_in_date_no_notification($instance, $messagingMethod, $target, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$data = json_decode($payload, true);
	    $arrayToSend = array('to' => $target, 'data' => $data);
	    $json = json_encode($arrayToSend);
	    $headers = array();
	    $connection = new AMQPStreamConnection(Util::$RABBITMQ_IP, Util::$RABBITMQ_PORT, Util::$RABBITMQ_USER, Util::$RABBITMQ_PASS);
		$channel = $connection->channel();
		list($queueName, $messageCount, $consumerCount) = $channel->queue_declare($target, false, false, false, false);
		if ($consumerCount > 0) {
			for ($j=0; $j<$consumerCount; $j++) {
				$headers = new AMQPTable(array('x-delay' => $delay));
				$msg = new AMQPMessage($json, array('delivery_mode' => 2));
				$msg->set('application_headers', $headers);
				$channel->basic_publish($msg, '', $target);
			}
		} else {
			if ($messagingMethod == "mix") {
				Util::send_fcm_message($instance, "com.qtracking.test.".$target, $target, $title, $body, $payload);
			}
		}
		$channel->close();
		$connection->close();
		return true;
	}
	
	public static function send_amqp_message_to_user_ids_no_notification($instance, $messagingMethod, $topicName, $userIDs, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$data = json_decode($payload, true);
	    $arrayToSend = array('to' => $topicName, 'data' => $data);
	    $json = json_encode($arrayToSend);
	    $headers = array();
	    $connection = new AMQPStreamConnection(Util::$RABBITMQ_IP, Util::$RABBITMQ_PORT, Util::$RABBITMQ_USER, Util::$RABBITMQ_PASS);
		$channel = $connection->channel();
		for ($i=0; $i<sizeof($userIDs); $i++) {
			list($queueName, $messageCount, $consumerCount) = $channel->queue_declare("user_".$userIDs[$i], false, false, false, false);
			if ($consumerCount > 0) {
				for ($j=0; $j<$consumerCount; $j++) {
					$msg = new AMQPMessage($json);
					$channel->basic_publish($msg, '', "user_".$userIDs[$i]);
				}
			} else {
				if ($messagingMethod == "mix") {
					$userInfo = $instance->db->where("id", $userIDs[$i])->get("users")->row_array();
					Util::send_fcm_message($instance, $userInfo['notification_channel_id'], "user_".$userIDs[$i], $title, $body, $payload);
				}
			}
		}
		$channel->close();
		$connection->close();
		return true;
	}
	
	public static function send_amqp_message_no_notification_remote($instance, $messagingMethod, $target, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$url = "http://49.50.10.47/admin/user/send_amqp_message_no_notification";
		$arrayToSend = array(
			'messaging_method' => $messagingMethod,
	    	'target' => $target,
	    	'payload' => $payload
	    );
	    $headers = array();
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    //echo $response;
	    curl_close($ch);
	}
	
	public static function send_delayed_amqp_message_no_notification_remote($instance, $messagingMethod, $target, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$url = "http://49.50.10.47/admin/user/send_amqp_message_in_date_no_notification";
		$arrayToSend = array(
			'messaging_method' => $messagingMethod,
	    	'target' => $target,
	    	'payload' => $payload
	    );
	    $headers = array();
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    //echo $response;
	    curl_close($ch);
	}
	
	public static function send_amqp_message_to_topic_no_notification_remote($instance, $messagingMethod, $target, $payload) {
		// $target = user_{USER_ID}, user_1, user_2, user_3
		$url = "http://49.50.10.47/admin/user/send_amqp_message_to_topic_no_notification";
		$subscribers = $instance->db->query("SELECT * FROM `rabbitmq_topics` WHERE `topic`='".$target."'")->result_array();
		$userIDs = [];
		for ($i=0; $i<sizeof($subscribers); $i++) {
			array_push($userIDs, intval($subscribers[$i]['user_id']));
		}
		$arrayToSend = array(
			'messaging_method' => $messagingMethod,
	    	'target' => $target,
	    	'payload' => $payload,
	    	'user_ids' => json_encode($userIDs)
	    );
	    $headers = array();
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    //echo $response;
	    curl_close($ch);
	}
	
	public static function send_redis_message($instance, $target, $title, $body, $payload) {
		/*$redis = new Redis([
			'host' => Util::$REDIS_IP,
		    'port' => Util::$REDIS_PORT
		]);
		$notification = array('title' => $title , 'body' => $body);
	    $data = json_decode($payload, true);
	    $data['notification'] = $notification;
	    $arrayToSend = array('notification' => $notification, 'data' => $data);
		$redis->publish($target, json_encode($arrayToSend));*/
	}
	
	public static function send_fcm_message_no_notification($instance, $target, $payload) {
		$url = "https://fcm.googleapis.com/fcm/send";
		$serverKey = Util::get_setting($instance, "fcm_server_key");
	    $arrayToSend = array('to' => $target, 'priority' => 'high', 'data' => json_decode($payload, true));
	    $headers = array();
	    $headers[] = 'Authorization: key='. $serverKey;
	    $headers[] = 'Content-Type: application/json';
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    curl_close($ch);
	}
	
	public static function send_pushy_message_no_notification($instance, $pushyToken, $payload) {
		$settings = $instance->db->get("settings")->row_array();
		$pushyApiKey = Util::get_setting($instance, "pushy_api_key");
		$url = "https://api.pushy.me/push?api_key=".$pushyApiKey;
	    $arrayToSend = array('to' => $pushyToken, 'data' => json_decode($payload, true));
	    $headers = array();
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arrayToSend));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($ch);
	    if ($response === FALSE) {
	    }
	    curl_close($ch);
	}
	
	public static function send_redis_message_no_notification($instance, $target, $payload) {
		/*$redis = new Redis([
			'host' => Util::$REDIS_IP,
		    'port' => Util::$REDIS_PORT
		]);
	    $data = json_decode($payload, true);
	    $arrayToSend = array('data' => $data);
		$redis->publish($target, json_encode($arrayToSend));*/
	}
	
	public static function get_distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) { // in meters
	    $rad = M_PI / 180;
	    $theta = $longitudeFrom - $longitudeTo;
    	$dist = sin($latitudeFrom * $rad) * sin($latitudeTo * $rad) +  cos($latitudeFrom * $rad) * cos($latitudeTo * $rad) * cos($theta * $rad);
    	return acos($dist) / $rad * 60 * 1.853 * 1000;
	}
	
	public static function insert_notification($instance, $userID, $isTopic, $topic, $type, $title, $body, $payload, $date) {
		$instance->db->insert("notifications", array(
			"user_id" => $userID,
			'is_topic' => $isTopic?"1":"0",
			'topic' => $topic,
			"type" => $type,
			"title" => $title,
			"body" => $body,
			"payload" => $payload,
			"date" => $date
		));
	}
	
	public static function startsWith($string, $firstString) {
    	return substr_compare($string, $firstString, 0, strlen($firstString)) === 0;
	}
	
	public static function endsWith($string, $lastString) {
    	return substr_compare($string, $lastString, -strlen($lastString)) === 0;
	}
	
	public static function get_local_date() {
		$now = DateTime::createFromFormat('U.u', microtime(true));
		return $now->format("Y-m-d H:i:s");
	}
}
