<?php

require("class.AuthenticationManager.php");

//log the user out
$authenticationManager->logout();

//go to the login page
Header("Location: login.php");

?>