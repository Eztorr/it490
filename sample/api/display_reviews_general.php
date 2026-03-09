<!DOCTYPE>
<html>
	<h1>REVIEWS</h1>
	<form method="GET">
		<label></label>
		<input type='text' name='search' value='<?php echo htmlspecialchars($search)?>'>
		<input type='submit'>
	</form>
</html>
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$search ="";
if(isset($_GET['search']) && $_GET['search'] != ""){
	$search = $_GET['search'];
}else{
	$search = "";
}
$request = array();
$request['type'] = 'get_all_reviews';
$request['search_string'] = $search;
$response = $client->send_request($request);

if($response['returnCode'] == 1 && !empty($response['array'])){
	foreach($response['array'] as $review){
		echo "<p>" . $review['review_id'] ."</p>";
		echo "<p>" . $review['reviewer_id'] ."</p>";
		echo "<p>" . $review['reviewer_email'] ."</p>";
		echo "<p>" . $review['game_id'] ."</p>";
		echo "<p>" . $review['rating'] ."</p>";
		echo "<p>" . htmlspecialchars($review['text']) ."</p>";
		echo "<p>" . $review['is_private'] ."</p>";
		echo "<p>" . htmlspecialchars($review['game_name']) ."</p>";
	}
}else{
	echo "<p> no reviews found</p>"
}

?>
