<?php 
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
function validateLogin()
{
    $emailError="";
    $passwordError="";

    if(empty($_POST["email"])){
        $emailError = "Please enter your email.";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailError = "Email or password is invalid";
            //https://www.w3schools.com/php/php_form_url_email.asp
        }
    }

    if(empty($_POST["password"])){
        $passwordError = "Please enter your password.";
    } else {
        $password = test_input($_POST["password"]);
        if(!preg_match("/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})/", $password))
        //if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $password)) 
        {
            $passwordError = "Email or password is invalid.";
        }
    }
    if ($emailError != "")
        return $emailError;
    if ($passwordError != "")        
       return $passwordError;
    return "";
}

   
