<?php
//Start the session
session_start();

//Clear all the session variable
$_SESSION=array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to the login page or any other page you want
    header("Location: index.php");
    exit();

?>