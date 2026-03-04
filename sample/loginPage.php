<?php
session_start();
$error = "";
if (isset($_SESSION["error"])){
    $error = $_SESSION["error"];
    unset($_SESSION["error"]);
}
?>
<!DOCTYPE html>
<html>
   <link rel = "stylesheet" href= "loginPage.css">
<head> 
   <title> Log In </title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6F" crossorigin="anonymous">

</head>
<body>

    <div class ="container mt-5">
    <form id ="validateForm" action ="/app/loginRequest.php" method = "POST">
        <h1 class ="text-center mb-4"> Log In</h1>

        <div class = "mb-3">
        <label for = "email" class = "form-label"> Email </label>
        <input name = "email" id ="email" placeholder = " Enter Email" class="form-control"/>
        </div>

        <div class = "mb-3">
        <label for = "password" class = "form-label"> Password </label>
        <input name = "password" id="password" type = "password" placeholder = "Password" class="form-control"/>
        </div>
        <button type = "submit" class="btn btn-primary"> Log In </button>
    </form>  
    
    <?php if (!empty($error)): ?>
        <p style = "color: black; margin-top:15px;"> 
            <?php echo $error; ?> </p>
    <?php endif; ?>
    </div>
    <div class = "signUp">
    <p> Don't Have an Account? Sign Up Here! <a href = "registration.html"> Sign Up </a><p>
    </div>
</body>

</html>
