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


function doLogin($username,$password)
{
    global $mydb;
    $query = "select email, password from Users where Users.email='$username';";
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

            return array(
                "status" => "success",
		"message" => "ACCEPT"

            );
        }
    }

    return array(
        "status" => "fail",
        "message" => "DENY"
    );
}

function doRegister($username, $password)
{

    global $mydb;
   
    $query = "SELECT email FROM Users WHERE email = '$username'";
    $response = $mydb->query($query);
   
       if ($response && $response->num_rows > 0) {
        return array("status" => "error", "message" => "user already exists");
    }
   
   
    $query = "insert into Users (email, password) values ('$username', '$password');";
    $mydb->query($query);
    if ($mydb->errno != 0)
{
        echo "failed to execute query:".PHP_EOL;
        echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
        return array("status" => "error", "message" => "error");
    }

    return array("status" => "success", "message" => "successful registration");


    function doValidate($sessionID)   
  {
        global $mydb;
	$query = "SELECT * FROM Sessions WHERE session_token = $sessionID";
	 if ($mydb->errno != 0)
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("status" => "error", "message" => "session not valid");
	 }

	return array("status" => "success", "message" => "valid session");
    
 }
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
      return doLogin($request['username'],$request['password']);
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

