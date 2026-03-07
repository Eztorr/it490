<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once(__DIR__ . '/../app/navBar.php'); 
require_once('../app/validateSession.php');
if (!isset($_SESSION['token']) || empty($_SESSION['token']))
{
	header("Location: /loginPage.php");
	exit();
}

if (isset($_SESSION['message']))
{
        $message = $_SESSION['message'];
	echo "<p>$message</p>";
        unset($_SESSION['message']);
	unset($message);	
}

?>

<!DOCTYPE html>

<html>

</html>

<?php
$searchInput ="";
if($_SERVER["REQUEST_METHOD"] == "GET" && $_GET["game_id"] !="" )
{
	if(isset($_GET["game_id"])){
		
		$game_id = urlencode($_GET["game_id"]);
	}
	
	$env = parse_ini_file(__DIR__ . '/.env');

	if (!$env || !isset($env['RAWG_API_KEY'])) {
    		die("API key not found in .env file.");
	}

	$apiKey = $env['RAWG_API_KEY'];

	$url = "https://api.rawg.io/api/games/$game_id?key=$apiKey";

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPGET, true);

	$response = curl_exec($ch);

	if (curl_errno($ch)) {
    		echo "cURL Error: " . curl_error($ch);
    		curl_close($ch);
    		exit;
	}

	curl_close($ch);

	$game = json_decode($response, true);

	if (!$game) {
		
    		die("Error decoding JSON response.");
	}


	echo "<ul>";

	

    	echo "<li>";
	
	$name = htmlspecialchars($game['name']); 
	echo "$name | ";
	
	echo "Released: " . htmlspecialchars($game['released']) . " | ";
	$released =  htmlspecialchars($game['released']); 
	
	if($released == ""){
		$released = "N/A";
	}

	echo "Genres: ";
	$mainGenre = "N/A";
    	if (!empty($game['genres'])) {
        	foreach ($game['genres'] as $genre) {
            	echo htmlspecialchars($genre['name']) . " ";
		}
		$mainGenre = htmlspecialchars($game['genres'][0]['name']);
    	}

    	echo "| Platforms: ";

    	if (!empty($game['platforms'])) {
        	foreach ($game['platforms'] as $platform) {
            	echo htmlspecialchars($platform['platform']['name']) . " ";
        	}
	}
	 echo "<form action='review_game.php' method='POST' style='display:inline;'>";

    	echo "<input type='hidden' name='name' value='$name'>";
    	echo "<input type='hidden' name='released' value='$released'>";
    	echo "<input type='hidden' name='genre' value='$mainGenre'>";

    	echo "<input type='submit' value='Review Game'>";

    	echo "</form>";	
	echo "<br>"; 


	if (!empty($game['background_image'])) {
    	$image = htmlspecialchars($game['background_image']);
    	echo "<img src='$image' alt='$name' style='max-width:400px; display:block; margin-top:10px; margin-bottom:10px;'>";
	}


	if (!empty($game['description_raw'])) {
    	$description = htmlspecialchars($game['description_raw']);
    	echo "<p>$description</p>";
	}


	if (!empty($game['metacritic'])) {
    	$metacritic = htmlspecialchars($game['metacritic']);
    	echo "<p><strong>Metacritic:</strong> $metacritic</p>";
	}


	if (!empty($game['tags'])) {
    	echo "<p><strong>Tags:</strong> ";
    	$tagNames = [];
    	foreach ($game['tags'] as $tag) {
        	$tagNames[] = htmlspecialchars($tag['name']);
    	}
    	echo implode(", ", $tagNames);
    	echo "</p>";
	}		
    	echo "</li>";
	

}
?>
