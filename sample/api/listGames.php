<!DOCTYPE html>
<html>
<form action="<?php echo ($_SELF["PHP_SELF"])?>" method="POST">
                <label>Search</label>
                <input type="search" name="search" ></input>
                <input type="submit">Search</input>
        </form>
</html>

<?php
$searchInput ="";
if($_SERVER["REQUEST_METHOD"] == "POST" ){
	if(isset($_POST["search"])){
		$searchInput = $_POST["search"];

	}
	// Load .env file
	$env = parse_ini_file(__DIR__ . '/.env');

	if (!$env || !isset($env['RAWG_API_KEY'])) {
    		die("API key not found in .env file.");
	}

	$apiKey = $env['RAWG_API_KEY'];

	$url = "https://api.rawg.io/api/games?key=$apiKey&search=$searchInput&page_size=10";

	// Initialize cURL
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
    		echo "<li>" . htmlspecialchars($game['name']) . 
         	" (Released: " . htmlspecialchars($game['released']) . ")</li>";
	}

	echo "</ul>";
}
?>
