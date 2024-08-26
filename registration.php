<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
include('database/connection.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Submission Process
if (isset($_POST["register"])) {
    // Get Data
    $name = $_POST["name"];
    $district = $_POST["district"];
    $city = $_POST["city"];
    $mobile = $_POST["mobile"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Validations
    $errors = [];

    // Validate form inputs
    if (empty($name) || empty($district) || empty($city) || empty($mobile) || empty($email) || empty($password)) {
        $errors[] = "All fields are required";
    }
    
    // Password validation
    if (strlen($password) < 8 || strlen($password) > 16) {
        $errors[] = "Password must be between 8 and 16 characters.";
    }

    // Mobile number validation
    if (strlen($mobile) !== 10 || !is_numeric($mobile)) {
        $errors[] = "Mobile number should be numeric and 10 digits only.";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Unique Key Validation for Email
    $sql_check_mail = "SELECT * FROM customer WHERE email = '$email'";
    $result_check_mail = $conn->query($sql_check_mail);
    if ($result_check_mail->num_rows > 0) {
        $errors[] = "Email Already Registered";
    }

    // If there are no errors, proceed with inserting into the database
    if (empty($errors)) {
        // Prepare and execute the SQL query
        $sql = "INSERT INTO customer (name, district, city, mobile, email, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $errors[] = "Error in database connection.";
        } else {
            $name = mysqli_real_escape_string($conn, $name);
            $district = mysqli_real_escape_string($conn, $district);
            $city = mysqli_real_escape_string($conn, $city);
            $mobile = mysqli_real_escape_string($conn, $mobile);
            $email = mysqli_real_escape_string($conn, $email);
            $password = mysqli_real_escape_string($conn, $password);

            // Hashing password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Bind parameters and execute
            $stmt->bind_param("ssssss", $name, $district, $city, $mobile, $email, $hashedPassword);
            if ($stmt->execute()) {
                $successMessage = "Thank you for registering!!! <br> Your registration process has been successfully completed.";
                echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        icon: "success",
                        title: "Registration Successful",
                        html: "' . addslashes($successMessage) . '",
                        showCloseButton: true,
                    });
                });
                </script>';
            }
            
        }
    }

    // Display errors using SweetAlert
    if (!empty($errors)) {
        $errorMessages = join('<br>', $errors);
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: "error",
                title: "Sign Up Errors",
                html: "' . addslashes($errorMessages) . '",
                showCloseButton: true,
            });
        });
        </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="css/loginandreg.css">
    <title>Registration Page</title>
</head>
<body>
    <div class="container-fluid">
        <form class="mx-auto" action="registration.php" method="post" autocomplete="off">
            <h4 class="text-center">Register</h4>
            <h6 class="text-center">Please fill the fields below:</h6>
            <div class="mb-3 mt-4">
                <label for="name" class="form-label">Name:</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
            </div>

            <div class="mb-3 mt-2">
                <label for="district" class="form-label">District:</label>
                <input type="text" class="form-control" id="district" name="district" placeholder="Enter your district" required>
            </div>

            <div class="mb-3 mt-2">
                <label for="city" class="form-label">City:</label>
                <input type="text" class="form-control" id="city" name="city" placeholder="Enter your city" required>
            </div>

            <div class="mb-3 mt-2">
                <label for="mobile" class="form-label">Mobile no:</label>
                <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Enter your mobile number" required>
            </div>

            <div class="mb-3 mt-2">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="mb-3 mt-2">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                <div id="emailHelp" class="form-text mt-3">Already have an account?
                    <a href="login.php" id="emailHelp" class="form-text">Login</a>
                </div>
            </div>
            <button type="submit" name="register" class="btn btn-primary mt-4">CREATE ACCOUNT</button>
        </form>
    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
