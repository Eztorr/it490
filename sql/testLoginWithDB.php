#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$mydb = new mysqli('127.0.0.1','userInfo','theBestPassword','data');




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

    if (!$stmt->execute())
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	return array("returnCode" => "2", "message" => "db error");
    }
     $response = $stmt->get_result();


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
            return array("returnCode" => "1", "message" => "Login successful", "token" => "$token", "email" => $row['email'], "user_id" => $row['id']);
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
    if (!$stmt->execute())
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
	 $query = "SELECT * FROM Sessions WHERE session_token = ? AND expires > NOW()";
	 $stmt = $mydb->prepare($query);
         $stmt->bind_param('s', $sessionID);

	 if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	    return array("returnCode" => 2, "message" => "db error /session not valid");
	 }
	 $response = $stmt->get_result();

	 if ($response && $response->num_rows > 0) {	
	 $stmt->close();
	 $expiration_date = date("Y-m-d H:i:s", strtotime("+1 hour"));
	 $query = "UPDATE Sessions SET expires = ? WHERE session_token = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('ss', $expiration_date, $sessionID);
	 if (!$stmt->execute()) {
   		 return ["returnCode" => 2, "message" => "failed to update expiration"];
}
        return array("returnCode" => 1, "message" => "valid session");
	 }
	$query = "DELETE FROM Sessions WHERE session_token = ?";
        $stmt = $mydb->prepare($query);
        $stmt->bind_param('s', $sessionID);
        $stmt->execute();

	return array("returnCode" => 0, "message" => "invalid session");
    
 }
function deleteSession($sessionID)
{
	global $mydb;
	$query = "DELETE FROM Sessions WHERE session_token = ?";
	$stmt = $mydb->prepare($query);
	$stmt->bind_param('s', $sessionID);
        $stmt->execute();

	if (!$stmt->execute())
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
		return array ("returnCode" => 0, "message" => "db error");
	}
	return array ("returnCode" => 1, "message" => "session deleted/ logged out!!!");
}

function newReview($user_id, $game, $rating, $reviewText, $genre, $release){
	global $mydb;
	$query = "INSERT IGNORE INTO Games (game, genre, release_date) VALUES (?, ?, ?)";
	$stmt = $mydb->prepare($query);
        $stmt->bind_param('sss', $game, $genre, $release);
	if (!$stmt->execute())
        {
                echo "failed to execute query:".PHP_EOL;
                echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
                return array ("returnCode" => 0, "message" => "db error");
        }


	$stmt->close();

	$query = "SELECT game_id FROM Games WHERE game = ?";
        $stmt = $mydb->prepare($query);
        $stmt->bind_param('s', $game);

	if (!$stmt->execute())
        {
                echo "failed to execute query:".PHP_EOL;
                echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
                return array ("returnCode" => 0, "message" => "db error");
        }


	$response = $stmt->get_result();
	$row = $response->fetch_assoc();

	$stmt->close();

	$query = "INSERT INTO User_Reviews (user_id, game_id, rating, text) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = ?, text = ?;";
        $stmt = $mydb->prepare($query);
        $stmt->bind_param('siisis', $game, $row['game_id'], $rating, $reviewText, $rating, $reviewText);
        if (!$stmt->execute())
        {
                echo "failed to execute query:".PHP_EOL;
                echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
                return array ("returnCode" => 0, "message" => "db error");
        }

	
	return array ("returnCode" => 1, "message" => "review uploaded");



}

function handlePrivate($user_id, $game){
	//gets game_id based on game name
        $query = "SELECT game_id FROM Games WHERE game = ?";
        $stmt = $mydb->prepare($query);
        $stmt->bind_param('s', $game);

        if (!$stmt->execute())
        {
                echo "failed to execute query:".PHP_EOL;
                echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
                return array ("returnCode" => 0, "message" => "db error");
        }


        $response = $stmt->get_result();
        $row = $response->fetch_assoc();
	
	$stmt->close();

        $query = "SELECT is_private FROM User_Reviews WHERE user_id = ? AND game_id = ?;";
        $stmt = $mydb->prepare($query);
	$stmt->bind_param('ii', $user_id, $row['game_id']);
	$game_id = $row['game_id'];

        if (!$stmt->execute()) {
            echo "Failed to execute query: " . PHP_EOL;
            echo __FILE__ . ':' . __LINE__ . ": error: " . $mydb->error . PHP_EOL;
            return array("returnCode" => 0, "message" => "Database error");
    }

        $response = $stmt->get_result();
    
        if ($response->num_rows > 0) {
            $row = $response->fetch_assoc();
            $currentPrivacy = $row['is_private'];
	
             $newPrivacy = ($currentPrivacy == 0) ? 1 : 0;
	    
	    $stmt->close();

            $query = "UPDATE User_Reviews SET is_private = ? WHERE user_id = ? AND game_id = ?;";
            $stmt = $mydb->prepare($query);
            $stmt->bind_param('iii', $newPrivacy, $user_id, $game_id);

        if (!$stmt->execute()) {
            echo "Failed to execute query: " . PHP_EOL;
            echo __FILE__ . ':' . __LINE__ . ": error: " . $mydb->error . PHP_EOL;
            return array("returnCode" => 0, "message" => "Database error during update");
        }

        return array("returnCode" => 1, "message" => "Review privacy updated successfully");
    } else {
        return array("returnCode" => 0, "message" => "Review not found");
    }
}


function getReviews($user_id){

	global $mydb;
         $query = "SELECT * FROM User_Reviews WHERE user_id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
	 $response = $stmt->get_result();

	 $all_rows = $response->fetch_all(MYSQLI_ASSOC);

	 return array("returnCode" => 1, "array" => $all_rows);



}


function getFollowedReviews($user_id){

        global $mydb;
        $query = "
        SELECT 
            User_Reviews.review_id,
            User_Reviews.user_id AS reviewer_id,
            Users.email AS reviewer_email,
            User_Reviews.game_id,
            User_Reviews.rating,
            User_Reviews.text,
            User_Reviews.is_private
        FROM 
            User_Following
        JOIN 
            User_Reviews ON User_Following.following_id = User_Reviews.user_id
        JOIN 
            Users ON User_Reviews.user_id = Users.id
        WHERE 
            User_Following.user_id = ?;
    ";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
         $response = $stmt->get_result();

         $all_rows = $response->fetch_all(MYSQLI_ASSOC);

         return array("returnCode" => 1, "array" => $all_rows);



}

function handleFollow($user_id, $follow_id){

	 global $mydb;
         $query = "SELECT * FROM User_Following WHERE user_id = ? AND following_id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('ii', $user_id, $follow_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
         $response = $stmt->get_result();
	 $stmt->close();




	 if ($response->num_rows > 0) {
	 	
		$query = "DELETE FROM User_Following WHERE user_id = ? AND following_id = ?";
                $stmt = $mydb->prepare($query);
		$stmt->bind_param('ii', $user_id, $follow_id);

		if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }

		$stmt->close();
		return array("returnCode" => 1, "message" => "unfollowed");

	 
	 
	 }else{
	        $query = "INSERT INTO User_Following (user_id, following_id) VALUES (?, ?)";
                $stmt = $mydb->prepare($query);
                $stmt->bind_param('ii', $user_id, $follow_id);

                if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
		$stmt->close();
                return array("returnCode" => 1, "message" => "followed");

	 
	 
	 }

}

function get_all_reviews($search){
	
	global $mydb;
         $query = "
        SELECT 
            User_Reviews.review_id,
            User_Reviews.user_id AS reviewer_id,
            Users.email AS reviewer_email,
            User_Reviews.game_id,
            User_Reviews.rating,
            User_Reviews.text,
            User_Reviews.is_private
        FROM 
            User_Reviews
        JOIN 
            Users ON Users.id = User_Reviews.user_id
        WHERE 
	    User_Reviews.game LIKE ?";
	
	 $searchTerm = "%" . $search . "%";
    
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('s', $searchTerm);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error");
         }
         $response = $stmt->get_result();

         $all_rows = $response->fetch_all(MYSQLI_ASSOC);

         return array("returnCode" => 1, "array" => $all_rows);


}

function getProfileInfo($user_id){

	global $mydb;
         $query = "SELECT * FROM Users WHERE id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
	 $response = $stmt->get_result();
	 if ($response && $response->num_rows > 0) {

		$row = $response->fetch_assoc();
        	return array("returnCode" => "1", "user_id" => $row['id'], "email" => $row['email'], "joined" => $row['created'] );
	 }

	 return array("returnCode" => "0", "message" => "User does not exist");



}

function getFollowStatus($user_id, $follow_id){

	global $mydb;
         $query = "SELECT * FROM User_Following WHERE user_id = ? AND following_id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('ii', $user_id, $follow_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
         $response = $stmt->get_result();
         if ($response && $response->num_rows > 0) {

                $row = $response->fetch_assoc();
                return array("returnCode" => "1", "message" => "following");
         }

         return array("returnCode" => "0", "message" => "Not following");

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
     case "new_review":
	     return addReview($request['user_id'], $request['game'], $request['rating'], $request['reviewText'], $request['genre'], $request['release_date']);
     case "private":
	     return handlePrivate($request['user_id'], $request['game']);
     case "get_user_reviews":
	     return getReviews($request['user_id']);
     case "get_followed_reviews":
	     return getFollowedReviews($request['user_id']);
     case "follow":
             return handleFollow($request['user_id'], $request['follow_id']);
     case "get_all_reviews":
             return getAll($request['search_string']);
     case "get_profile_info":
	     return getProfileInfo($request['user_id']);
     case "get_follow_status":
             return getFollowStatus($request['user_id'], $request['follow_id']);






  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

