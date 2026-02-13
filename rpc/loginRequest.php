<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

/**
$request = array();
$request['type'] = "Login";
$request['username'] = "steve";
$request['password'] = "password";
$request['message'] = $msg;
$response = $client->send_request($request);
//$response = $client->publish($request);
*/
$request = array();
$request['type'] = "Login";
$request ['username'] = $_POST['username'];
$request ['password'] = $_POST['password'];
$response = $client->send_request($request);

$payload = json_encode($response);
echo $payload;

