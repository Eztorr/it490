<!DOCTYPE html>
<html>
	<h1>REVIEWS</h1>
	<form method="GET">
		<label>Search:</label>
		<input type='text' name='search' value="<?php echo htmlspecialchars($search); ?>"></input>
		<input type='submit'>
	</form>
</html>

<?php
require_once('../app/path.inc');
require_once('../app/get_host_info.inc');
require_once('../app/rabbitMQLib.inc');

$client = new rabbitMQClient("../app/testRabbitMQ.ini","testServer");

function getReviews($search){
	global $client;
	$request = array();
	$request['type'] = 'get_all_reviews';
	$request['search_string'] = $search;
	$result = $client->send_request($request);
	return $result;
}
$search = isset($_GET['search']) ? $_GET['search'] : '';
$response = getReviews($search);

if($response['returnCode'] == 1 && !empty($response['array'])){
	foreach($response['array'] as $review){
		echo "<p>" . $review['review_id'] . "</p>";
		echo "<p>" . $review['reviewer_id'] ."</p>";
		echo "<p>" . $review['reviewer_email'] . "</p>";
		echo "<p>" . $review['game_id'] . "</p>";
		echo "<p>" . $review['rating'] . "</p>";
		echo "<p>" . htmlspecialchars($review['text']) . "</p>";
		echo "<p>" . $review['is_private'] . "</p>";
		echo "<p>" . htmlspecialchars($review['game_name']) . "</p>";
	}
}else{
	echo "<p> no reviews found</p>";
}

?>
