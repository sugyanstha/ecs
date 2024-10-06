<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$errors = []; // Array to store validation errors

// Checking if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php"); // Redirecting to Login Page
    exit();
}
$email = $_SESSION['email']; // Fetch logged-in user email

// Database Connection
include('../database/connection.php');
include('../customer/layout/cheader.php');

$sql = "SELECT * FROM customer WHERE cid = '" . $_SESSION['cid'] . "'";
$result = $conn->query($sql);

$row = null;
if ($result && $result->num_rows > 0) {
    // Fetch values in row
    $row = $result->fetch_assoc();
}
?>

<link rel="stylesheet" href="../css/view_profile.css">
<div class="box-container">
    <div class="box">
        <h1>My Profile</h1>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="district">District:</label>
            <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($row['district']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($row['city']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="mobile">Mobile:</label>
            <input type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($row['mobile']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" readonly>
        </div>
        <a href="../dashboard.php">Back</a>
        <a href="edit_profile.php">Edit Profil</a>
    </div>
</div>
