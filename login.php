<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
include('database/connection.php');

// Check if the user is already logged in
if (isset($_SESSION['email'])) {
    header("location: dashboard.php");
    exit;
}

// Submission Process
if (isset($_POST["login"])) {
    // Get Data (Email and Password from the form)
    $email = strtolower($_POST["email"]);
    $password = $_POST["password"];

    // Use prepared statement to prevent SQL injection
    $sql = "SELECT cid, email, name, password FROM customer WHERE email = ?";

    // Prepares the SQL statement, binds the email parameter, executes statement and retrieves the result
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email); // Corrected to bind_param
    $stmt->execute();
    $result = $stmt->get_result();

    // If there is at least one row in the result
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedpassword = $row['password'];

        if (password_verify($password, $hashedpassword)) {
            // Password matches, store user details in session
            $_SESSION['cid'] = $row['cid'];
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $row['name'];
            header("location: dashboard.php");
            exit;
        } else {
            echo "<script>Swal.fire('Error!', 'Invalid credentials!', 'error');</script>";
        }
    } else {
        echo "<script>Swal.fire('Error!', 'No user found with that email!', 'error');</script>";
    }
}
?>

<!------------ HTML Code -------------> 
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="css/loginandreg.css">
    <title>Login Page</title>
</head>
<body>
    <div class="container-fluid">
        <form class="mx-auto" action="login.php" method="POST">
            <h4 class="text-center">Login</h4>
            <h6 class="text-center">Please enter your e-mail and password:</h6>
            <div class="mb-3 mt-5">
                <label for="exampleInputEmail1" class="form-label">Email:</label>
                <input type="email" class="form-control" name="email" id="exampleInputEmail1" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password:</label>
                <input type="password" class="form-control" name="password" id="exampleInputPassword1" placeholder="Enter your password" required>
                <div class="form-text mt-3">Forget Password?
                    <a href="#">Click Here!!!</a>
                </div>
            </div>
            <button type="submit" name="login" value="login" class="btn btn-primary mt-4">LOGIN</button>
            <div class="form-text mt-3 text-center">New Customer?
                <a href="registration.php">Create an account</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
