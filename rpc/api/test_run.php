<?php 

require_once 'fetch_api.php';

echo "<h1>API FETCH TEST</h1>";

$gameData = fetchLatestGames(5);

if ($gameData && isset($gameData['results'])) {
    echo "<h3> Raw API Response: </h3>";
    echo "<pre>";
    print_r($gameData['results']);
    echo "</pre>";
} else {
    echo "<p> Error: Data cannot be fetched for some reason. Please re-enter the API key and check the config.php file.";

}