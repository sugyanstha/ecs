<?php
session_start();
//include('layout/adminheader.php'); // Database Connection
include('../database/connection.php'); // Database Connection


// Check if the user is logged in; redirect to login page if not
if (!isset($_SESSION['adminemail'])) {
    header("Location: admin_login.php");
    exit; // Ensure you exit after redirect
}

// Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the 'name' field exists and is not empty
    if (isset($_POST['name']) && !empty(trim($_POST['name']))) {
        $name = trim($_POST['name']);

        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name); // 's' indicates the type is string

        // Execute the prepared statement
        if ($stmt->execute()) {
            header("Location: admin_panel.php");
            exit; // Redirect after successful insertion
        } else {
            echo "Error: " . $stmt->error; // Handle error
        }

        // Close the prepared statement
        $stmt->close();
    } 
}
?>

<!------------- FORM STYLE ------------>
<style>
    /* Add some global styling for body */
body {
    font-family: 'Times New Roman', Times, serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Styling the form container */
.container {
    background-color: white;
    padding: 30px;
    width: 350px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    text-align: center;
}

/* Styling the form heading */
.container h2 {
    margin-bottom: 20px;
    font-size: 24px;
    color: #333;
}

/* Styling labels and inputs */
.container label {
    display: block;
    margin-bottom: 10px;
    font-size: 16px;
    color: #555;
    text-align: left;
}

.container input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

/* Styling buttons */
.container button {
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    margin-top: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.container button:hover {
    background-color: #0056b3;
}

/* Styling for back button link */
.container button a {
    text-decoration: none;
    color: white;
}

.container button a:hover {
    text-decoration: underline;
}

/* Adjusting hover effect for button containing a link */
.container button a:hover {
    text-decoration: none;
}

</style>
<form method="post" action="create_category.php" autocomplete="off">
    <div class="container">
        <h2>Add Category Form</h2>
        <label for="category">Category Name:</label>
        <input type="text" name="name" placeholder="Enter Category Name" required>
        <button type="submit">Add Category</button>
        <button><a href="category_list.php">Back</a></button>
    </div>
</form>
