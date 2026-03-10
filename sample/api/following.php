<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../app/validateSession.php');
if (!isset($_SESSION['token'])) || empty($_SESSION['token'])) {
    header("LOcation: /loginPage.php");
    exit();
}


require_once('../app/path.inc');
require_once('../app/get_host_info.inc');
require_once('../app/rabbitMQLib.inc');

$client = new rabbitMQClient("../app/testRabbitMQ.ini", "testServer");

function getFollowedReviews() {
    global $client;
    $request = array();
    
}