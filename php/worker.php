<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('54.93.197.174', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

echo ' [*] Waiting for messages. To exit pressss CTRL+C', "\n";

$callback = function($msg){
  echo " [x] Received \n";
  //sleep(substr_count($msg->body, '.'));
  echo $msg->body."\n";

  echo " [x] Done", "\n";

   // postimportlog('file', __LINE__, $msg->body);

  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();


function postimportlog($type, $line, $message){
    $path = __DIR__.'/checklog.log';

    if (file_exists($path)) {
        chmod($path, 0777);
    }

    $fp = fopen($path, 'a+');
    fwrite($fp, '['. $type . '-' . 'post' . '::' . date('Y-m-d H:i:s') .'] @Line# '. $line. ' -- ' . $message . PHP_EOL);
    fclose($fp);
}
?>