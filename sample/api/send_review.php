<?php
session_start();
require_once('../app/path.inc');
require_once('../app/get_host_info.inc');
require_once('../app/rabbitMQLib.inc');
require_once('../app/phpValidation.php');
require_once('../app/validateSession.php');

if (!isset($_SESSION['token']) || empty($_SESSION['token']))
{
        header("Location: /loginPage.php");
        exit();
}

if(!isset($_POST['name'])){
	header("Location: /api/listGames.php");
	exit();
}

$game_name = trim($_POST['name']);
$genre = trim($_POST['genre']);
$release = trim($_POST['released']);
$user_id = trim($_SESSION['user_id']);
$rating = trim($_POST['reviewScore']);
$reviewText = trim($_POST['reviewText']);

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$request = array();
$request ['type'] = "new_review";
$request ['user_id'] = $user_id;
$request ['game'] = $game_name;
$request ['rating'] = $rating;
$request ['reviewText'] = $reviewText;
$request ['genre'] = $genre;
$request ['release_date'] = $release;



$response = $client->send_request($request);

if (isset($response['returnCode']) && (int)$response['returnCode'] === 1){
        //for now this is success 
        header ("Location: /api/listGames.php");
        exit();
}
else {
        header("Location: /index.php");
        exit();
}

