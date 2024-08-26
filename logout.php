<?php
session_start();

if (isset($_SESSION['customer'])) {
    // Unset the 'customer' session variable
    unset($_SESSION['customer']);

    session_destroy();

    header("Location: content.php");
    exit;
}
?>