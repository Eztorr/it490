<!DOCTYPE html>
<html>
<form action="<?php echo ($_SERVER["PHP_SELF"])?>" method="POST">
                <label>Search</label>
                <input type="search" name="search">
                <input type="submit">
        </form>
</html>

<?php
$searchInput ="";
print_r($_POST);
if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["search"] !="" )
{
	if(isset($_POST["search"])){
		
		$searchInput = urlencode($_POST["search"]);
	}
	
	$env = parse_ini_file(__DIR__ . '/.env');

	if (!$env || !isset($env['RAWG_API_KEY'])) {
    		die("API key not found in .env file.");
	}

	$apiKey = $env['RAWG_API_KEY'];

	$url = "https://api.rawg.io/api/games?key=$apiKey&search=$searchInput&page_size=30";

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

	$data = json_decode($response, true);

	if (!$data) {
		
    		die("Error decoding JSON response.");
	}

	echo "<h2>Video Game List:</h2>";
	echo "<ul>";

	foreach ($data['results'] as $game) {

    	echo "<li>";
	
	echo htmlspecialchars($game['name']) . " | ";
	$name = htmlspecialchars($game['name']); 
	
	echo "Released: " . htmlspecialchars($game['released']) . " | ";
	$released =  htmlspecialchars($game['released']); 

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

    	echo "</li>";
}	

}
?>
