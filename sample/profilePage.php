<?php
//https://stackoverflow.com/questions/6249707/check-if-php-session-has-already-started
//start if not started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/app/path.inc');
require_once(__DIR__ . '/app/get_host_info.inc');
require_once(__DIR__ . '/app/rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$userId = $_SESSION['user_id']; //get the userId from the person thats logged in 
//database request!! for the reviews
$request = ['type' => 'get_user_reviews',
    'user_id' => $userId
];
$response = $client->send_request($request);

//if reviews is in the array then store to reviews
if (isset($response ['array']) && is_array($response['array'])) 
    {
        $reviews = $response['array'];
        } else {
    $reviews = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Profile</title>
       </head>
       <body>
           <?php include_once(__DIR__ . '/app/navBar.php'); ?>
           <h1> Profile </h1>
           <p><strong>Email:</strong> <?php echo ($_SESSION['email']); ?></p>
           <h2> Reviews </h2>
           <?php if (empty($reviews)): ?>
               <p>Your Reviews Will Appear Here When You Submit a Review!</p>
           <?php else: ?>
                   <?php foreach ($reviews as $review): ?>
                       <div class="reviewCard">
                           <strong>Game:</strong> <?php echo $review['game']; ?><br>
                           <strong>Rating:</strong> <?php echo $review['rating']; ?>/100<br>
                           <strong>Comment:</strong> <?php echo $review['text']; ?>
                           <strong>Date:</strong> <?php echo $review['created']; ?> </div>
                   <?php endforeach; ?>
           <?php endif; ?>
       </body>
</html>

