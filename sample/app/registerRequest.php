<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

$request = array();
$request ['type'] = "Registration";
$request ['email'] = $_POST['email'];
$request ['password'] = $_POST['password'];
$response = $client->send_request($request);

$payload = json_encode($response);
echo $payload;

