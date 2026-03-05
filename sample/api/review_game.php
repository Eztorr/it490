<?php

if(!isset($_POST['name'])){
	header("Location: /api/listGames.php");
	exit();
}


?>


<!DOCTYPE html>
<html>


</html>

<?php

echo "Reviewing Game: " .  htmlspecialchars($_POST['name']) . " | Genre: " . htmlspecialchars($_POST['genre']) . " | Released on: " . htmlspecialchars($_POST['released']);

echo "<form action='send_review.php' method='POST'>";

echo "<label>Enter review score (1-100):</label>";
echo "<br>";
echo "<input type='number' name='reviewScore'>";

echo "<br>";

echo "<label>Enter Full Review Here:</label>";
echo "<br>";
//echo "<input type='text' name='reviewText' style='width: 700px; height: 250px;'>";
echo "<textarea name='reviewText' rows='30' cols='100'></textarea>";

echo "<br>";

echo "<input type='submit' value='Submit Review'>";

$name = $_POST['name'];
$genre = $_POST['genre'];
$released = $_POST['released'];

echo "<input type='hidden' name='name' value='$name'>";
echo "<input type='hidden' name='released' value='$released'>";
echo "<input type='hidden' name='genre' value='$genre'>";

echo "</form>";
?>
