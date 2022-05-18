<?php

require_once 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Send extends CI_Controller {
	
	public function index() {
		echo "Sending...";
		$queue = 'test';
$retryQueue = 'test_retry';
$exchange = 'test_exchange';
$retryExchange = 'test_exchange_retry';
$retryTimeSec = 5;

$connection = new AMQPStreamConnection('192.168.22.187', 5672, 'test', 'test');
$channel = $connection->channel();

$channel->exchange_declare($exchange, 'direct');
$channel->exchange_declare($retryExchange, 'direct');

$channel->queue_declare($queue, false, false, false, false, false, ['x-dead-letter-exchange' => ['S', $retryExchange]]);
$channel->queue_declare($retryQueue, false, false, false, false, false, ['x-message-ttl' => ['I', $retryTimeSec * 1000], 'x-dead-letter-exchange' => ['S', $exchange]]);

$channel->queue_bind($queue, $exchange);
$channel->queue_bind($retryQueue, $retryExchange);

for ($i = 0; $i < 1; $i++) {
    echo " [x] Send $i \n";
    $msg = new AMQPMessage($i);
    $channel->basic_publish($msg, $exchange);
    sleep(1);
}

$channel->close();
$connection->close();
	}
}
