<?php
session_start();


    $_SESSION = array(); // Clear all session variables
    session_destroy(); // Destroy the session

    header("Location: admin_login.php");
    exit();

?>

