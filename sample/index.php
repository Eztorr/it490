<?php 
session_start();
require_once(__DIR__ . '/app/validateSession.php');
if (!isset($_SESSION['token']) || empty($_SESSION['token']))
{
	header("Location: /loginPage.php");
	exit();
}

if (!toValidateSessionTokens($_SESSION['token'])) 
{
	session_unset();
	session_destroy(); 
	header("Location: /loginPage.php");
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6F" crossorigin="anonymous">
    
    <style>
        body {
            margin: 0;
            font-family: Ariel, sans-serif;
            background-color: #f0f0f0;
        }
        h1 {
            color: #333;
        }
        </style>
        </head>
        <body>
            <nav class = "navbar navbar-expand-sm bg-light">
                <div class = "container-fluid">
                    <ul class = "navbar-nav me-auto mb-2 mb-sm-0">
                        <li class = "nav-item">
                            <a class = "nav-link" href = "index.php"> Home </a>
                        </li>
                        <li class = "nav-item">
                            <a class = "nav-link" href = "#"> Link </a>
                        </li>
                        <li class = "nav-item">
                            <a class = "nav-link" href = "#"> Link </a>
                        </li>
                    </ul>
                </div>
            </nav>


            <h1> Welcome </h1>
	    <form action="/app/logout.php" method = "POST">
		<button type ="submit" class="btn btn-danger"> Log Out</button>
	</form>
</body>
</html>
