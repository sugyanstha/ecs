<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Include customer header and database connection
include('customer/c_layout/cheader.php');
include('customer/c_layout/sidebar.php');
include('database/connection.php');

?>

<?php
session_start();
?>