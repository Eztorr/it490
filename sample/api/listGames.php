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

	$rawgAPIurl = "https://api.rawg.io/api/games?key=$apiKey&search=$searchInput&page_size=30";

	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $rawgAPIurl);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPGET, true);

	$response = curl_exec($curl);

	if (curl_errno($curl)) {
    		echo "cURL Error: " . curl_error($curl);
    		curl_close($curl);
    		exit;
	}

	curl_close($curl);

	$apiGameData = json_decode($response, true);

	if (!$apiGameData) {
		
    		die("Error decoding JSON response.");
	}

	echo "<h2>Video Game List:</h2>";
	echo "<ul>";

	foreach ($apiGameData['results'] as $game) {

    	echo "<li>";

	//$dataName = $game['name'];
	$name = htmlspecialchars($game['name']); 
	$game_id = $game['id'];
	echo "<a href='view_game.php?game_id=" . urlencode($game_id) . "'>$name</a> | ";
	
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

    	echo "</li>";
}	

}
?>
