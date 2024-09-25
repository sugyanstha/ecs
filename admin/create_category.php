<?php
session_start();
include('../database/connection.php'); //Database Connection
// include('../admin/layout/adminheader.php');

if (!isset($_SESSION['adminemail'])) {
    header("Location: admin_login.php");
}

//Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $query = "INSERT INTO categories (name) VALUES ('$name')";
    mysqli_query($conn, $query);
    header("Location: admin_panel.php");
}
?>

<form method="post" action="create_category.php">
    <label for="category">Category Name:</label>
    <input type="text" name="name" placeholder="Enter Category Name" required>
    <button type="submit">Add Category</button>
    <button><a href="admin_panel.php">Back</a></button>
</form>
