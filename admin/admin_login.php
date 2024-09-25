<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database Connection
session_start();  // Start the session
include('../database/connection.php');

if (isset($_POST['login'])) {
    $adminname = $_POST['adminemail'];
    $adminpassword = $_POST['adminpassword'];

    // Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $adminname, $adminpassword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch admin data
        $admin = $result->fetch_assoc();

        // Store email in session
        $_SESSION['adminemail'] = $admin['email'];

        // Set admin session to true
        $_SESSION['admin'] = true;

        // Redirect to the admin panel page
        header("Location: admin_panel.php");
        exit();
    } else {
        // Redirect to the login page with an error
        header("Location: admin_login.php?error=1");
        exit();
    }
}
?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/loginandreg.css">
    <title>Admin Login Page</title>
  </head>
  <body>
    <div class="container-fulid">
      <form class="mx-auto" action="admin_login.php" method="POST" autocomplete = "off">
        <h4 class="text-center">Admin Login</h4>
        <h6 class="text-center">Please enter username and password:</h6>

        <div class="mb-3 mt-5">
          <label for="exampleInputEmail1" class="form-label">Username:</label>
          <input type="text" class="form-control" name="adminemail" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter your email or username" required>
        </div>

        <div class="mb-3">
          <label for="exampleInputPassword1" class="form-label">Password:</label>
          <input type="password" class="form-control" name="adminpassword" id="exampleInputPassword1" placeholder="Enter your password" required>
        </div>
        <button type="submit" name="login" value="login" class="btn btn-primary mt-4">LOGIN</button>
      </form>

    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

  </body>
</html>