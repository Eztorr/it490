#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$mydb = new mysqli('127.0.0.1','testUser','12345','data');




if ($mydb->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}

echo "successfully connected to database".PHP_EOL;


function doLogin($email,$password)
{
    global $mydb;
    $query = "select id, email, password from Users where Users.email='$email';";
    $response = $mydb->query($query);

    if ($mydb->errno != 0)
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	return array("status" => "error");
}

     if ($response && $response->num_rows > 0) 
    {
        $row = $response->fetch_assoc();
	
        
        if ($password === $row['password']) 
	{
	    $token = bin2hex(random_bytes(32));
	    $expiration_date = date("Y-m-d H:i:s", strtotime("+1 hour"));
	    $user_id = $response['id'];

	    $query = "INSERT INTO Sessions (user_id, session_token, expires) VALUES ('$user_id', '$token', '$expiration_date'";
            return array("status" => "ACCEPT", "message" => "Login successful", "token" => "$token");
        }
    }

    return array("status" => "DENY", "message" => "Login denied");
}

function doRegister($email, $password)
{

    global $mydb;
   
    $query = "SELECT email FROM Users WHERE email = '$email'";
    $response = $mydb->query($query);
   
       if ($response && $response->num_rows > 0) {
        return array("status" => "error", "message" => "user already exists");
    }
   
   
    $query = "insert into Users (email, password) values ('$email', '$password');";
    $mydb->query($query);
    if ($mydb->errno != 0)
{
        echo "failed to execute query:".PHP_EOL;
        echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
        return array("status" => "error", "message" => "error");
    }

    return array("status" => "ACCEPT", "message" => "successful registration");
}

    function doValidate($sessionID)   
  {
        global $mydb;
	$query = "SELECT * FROM Sessions WHERE session_token = $sessionID";
	$response = $mydb->query($query);

	 if ($mydb->errno != 0)
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("status" => "error", "message" => "session not valid");
	 }

	       if ($response && $response->num_rows > 0) {
        return array("status" => "ACCEPT", "message" => "valid session");
    }

	return array("status" => "DENY", "message" => "invalid session");
    
 }
   


function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "Login":
      return doLogin($request['email'],$request['password']);
    case "Registration":
	    return doRegister($request['email'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

