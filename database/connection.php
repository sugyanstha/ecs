<?php

// Database Credentials
$host = "localhost";
$username = "root";
$password = ""; // Corrected the variable name from $passsword to $password
$dbname = "jnthread";

// Creating Connection with Database
$conn = new mysqli($host, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

?>
