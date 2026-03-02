<?php 
function validateLogin ($email, $password) {
    if ($email === "" && $password === "") {
        return "Please enter both email and password.";
    } 

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
            //https://www.w3schools.com/php/php_form_url_email.asp
        }
         return "";
    }
   
