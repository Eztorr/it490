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
    $query = "select id, email, password from Users where Users.email= ?;";
    $stmt = $mydb->prepare($query);
    if ($stmt === false) {
        echo "Failed to prepare statement: " . $mydb->error . PHP_EOL;
        return array("returnCode" => "2", "message" => "Failed to prepare statement");
    }

    $stmt->bind_param('s', $email);  
    $stmt->execute();
    $response = $stmt->get_result();

    if ($stmt->errno != 0)
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	return array("returnCode" => "2", "message" => "db error");
}

     if ($response && $response->num_rows > 0) 
    {
        $row = $response->fetch_assoc();
	
        
        if (password_verify($password, $row['password'])) 
	{
	    $token = bin2hex(random_bytes(32));
	    $expiration_date = date("Y-m-d H:i:s", strtotime("+1 hour"));
	    $user_id = $row['id'];

	    $query = "INSERT INTO Sessions (user_id, session_token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE session_token = ?, expires = ?;";
	    $stmt = $mydb->prepare($query);
	    $stmt->bind_param('issss', $user_id, $token, $expiration_date, $token, $expiration_date);  
    	    $stmt->execute();
            return array("returnCode" => "1", "message" => "Login successful", "token" => "$token");
        }
    }

    return array("returnCode" => "0", "message" => "Login denied");
}

function doRegister($email, $password)
{

    global $mydb;
   
    $query = "SELECT email FROM Users WHERE email = ?";
    $stmt = $mydb->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $response = $stmt->get_result();
   
       if ($response && $response->num_rows > 0) {
        return array("returnCode" => "0", "message" => "user already exists");
    }
   
    $passwordHash = password_hash($password, PASSWORD_BCRYPT); 
    $query = "insert into Users (email, password) values (?, ?);";
    $stmt = $mydb->prepare($query);
    $stmt->bind_param('ss', $email, $passwordHash);
    $stmt->execute();
    if ($stmt->errno != 0)
{
        echo "failed to execute query:".PHP_EOL;
        echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
        return array("returnCode" => "2", "message" => "db error");
    }

    return array("returnCode" => "1", "message" => "successful registration");
}

    function doValidate($sessionID)   
  {
	 global $mydb;
	 $query = "SELECT * FROM Sessions WHERE session_token = ?";
	 $stmt = $mydb->prepare($query);
         $stmt->bind_param('s', $sessionID);
         $stmt->execute();
         $response = $stmt->get_result();

	 if ($mydb->errno != 0)
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	    return array("returnCode" => 2, "message" => "db error /session not valid");
	 }

	       if ($response && $response->num_rows > 0) {
        return array("returnCode" => 1, "message" => "valid session");
    }

	return array("returnCode" => 0, "message" => "invalid session");
    
 }
function deleteSession($sessionID)
{
	global $mydb;
	$query = "DELETE FROM Sessions WHERE session_token = ?";
	$stmt = $mydb->prepare($query);
	$stmt->bind_param('s', $sessionID);
        $stmt->execute();

	if ($stmt->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
		return array ("returnCode" => 0, "message" => "db error");
	}
	return array ("returnCode" => 1, "message" => "session deleted/ logged out!!!");
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
    case "login":
      return doLogin($request['email'],$request['password']);
    case "Registration":
	    return doRegister($request['email'],$request['password']);
    case "validate_session":
	    return doValidate($request['sessionId']);
     case "delete_session":
	     return deleteSession($request['token']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

