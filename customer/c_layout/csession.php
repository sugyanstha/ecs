<?php
session_start();
if (!isset($_SESSION["customer"])) {
    header("Location: ../customer/dashboard.php");
    exit();
} else {
    $cid = $_SESSION["customer"]; 
   

    
}
?>