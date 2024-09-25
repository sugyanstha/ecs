<?php
session_start();
include('customer/layout/cheader.php'); // Including header layout
// include('customer/layout/sidebar.php'); // Including sidebar layout
include('database/connection.php'); // Including database connection

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email']; // Fetch logged-in user email

// Enable error reporting for debugging  
error_reporting(E_ALL);
ini_set('display_errors', 1);

// You can uncomment this and fetch the products when ready
// $stmt = $conn->prepare("SELECT * FROM products");
// $stmt->execute();
// $products = $stmt->get_result();
?>



<!------------------ FRONTEND ------------------->
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Dashboard</title>
</head>
<body>

    <!-- Display Welcome Message -->
    <h2 class="text-center mt-4">Welcome, <?= htmlspecialchars($email); ?></h2>
    
    

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
</html>
