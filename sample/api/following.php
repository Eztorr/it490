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
    // the type
    $request['type'] = 'get_followed_reviews';
    $request['user_id'] = $_SESSION['user_id'];
    $result = $client->send_request($request);
    return $result;
}



$response = getFollowedReviews();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Follower Feed</title>
</head>
<body>
    <h1>Reviews from People You FOllow</h1>

    <?php
    if($response['returnCode'} ==1 && !empty($response['array'])){
        foreach($response['array' as $review){
            echo "div style='border-bottom: 1px solid #ccc; padding: 10px;'>";
            echo "<label>Game Name: </label>";
            echo "<strong>" . htmlspecialchars($review['game_name']). "</strong><br>";

            $reviewerEmail = htmlspecialchars($review['reviewer_email']);
            $reviewerID = $review['reviewer_id'];

            echo "<label>Reviewer Email: </label>";
            echo "<a href='#' onclick='alert(\"user ID: $reviewerID\"); return false;'>$reviewerEmail</a><br>";

            echo "<label>Rating: </label>";
            echo $review['rating'] . " / 5<br>";

            echo "<label>Review: ";

            if $review['is_private'] == 0){
                echo htmlspecialchars($eview['text']);
            } else {
                echo "<em>This review is private.</em>";
            }

            echo "</div>";
            } else {
                echo "<p>No reviews found from users that you follow. Start following people to see their current activity!</p>";
            }
            ?>
        </body>
        </html>