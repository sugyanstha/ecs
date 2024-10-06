<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$errors = []; // Array to store validation errors

// Checking if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php"); // Redirecting to Login Page
    exit;
}
$email = $_SESSION['email']; // Fetch logged-in user email

//Database Connection
include('../database/connection.php');
include ('../customer/layout/cheader.php');


$sql = "SELECT * FROM customer WHERE cid = '" . $_SESSION['cid'] . "'";

$result = $conn->query($sql);

$row = null;
if ($result && $result->num_rows > 0) {
    // Fetch values in row
    $row = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $newPassword = $_POST['password'];

    if (empty($name) || empty($email) || empty($mobile) || empty($district) || empty($city)) {
        $errors[] = "All fields are required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (!empty($newPassword) && (strlen($newPassword) < 8 || strlen($newPassword) > 16)) {
        $errors[] = "Password must be between 8 and 16 characters";
    }

    if (strlen($mobile) !== 10 || !is_numeric($mobile)) {
        $errors[] = "Mobile number should be 10 digits only.";
    }

    if (empty($errors)) {
        // Add fields to the update query
        $updateSql .= " name = '$name', district = '$district', city = '$city', mobile = '$mobile', email = '$email'";
        
        if (!empty($newPassword)) {
            // Update the password field if a new password is provided
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql .= ", password = '$hashedPassword'";
        }
        
        // Complete the update query
        $updateSql .= " WHERE cid = " . $_SESSION['cid'];

        if ($conn->query($updateSql) === TRUE) {
            // Update the session variables with the new values
            $_SESSION['name'] = $name;
            $_SESSION['district'] = $district;
            $_SESSION['city'] = $city;
            $_SESSION['mobile'] = $mobile;
            $_SESSION['email'] = $email;
            // Redirect to the profile page with a success message
            header("Location: profile.php?success=1");
            exit;
        } else {
            // Redirect to the profile page with an error message
            // header("Location: profile.php?error=1");
            // Redirect to the profile page with a specific error message
            header("Location: profile.php?error=db_update_failed");
            exit;
        }
    } // Display errors using SweetAlert
    else if (!empty($errors)) {
        $errorMessages = join("\n", $errors);
        echo '<script>
        swal({title:"Error!",text:"' .$errorMessages.'",icon : "warning"});
        Swal.fire({
            icon: "error",
            title: "Sign Up Errors",
            html: "' . $errorMessages . '",
            showCloseButton: true,
        });
        </script>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" type="text/css" href="../css/profile.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <link rel="stylesheet" href="css/sweetAlert.css"> -->
    <script>
        document.querySelector('form').addEventListener('submit', function (event) {
            if (!validateForm()) {
                event.preventDefault();
            }
        });
        // Client-side validation function with SweetAlert integration
        function validateForm() {
            var errors = [];
            var name = document.getElementById("name").value;
            var email = document.getElementById("email").value;
            var password = document.getElementById("password").value;
            var mobile = document.getElementById("mobile").value; // Corrected ID
            
            if (name === "") {
                errors.push("Name is required.");
            }
            
            if (email === "") {
                errors.push("Email is required.");
            } else if (!validateEmail(email)) {
                errors.push("Invalid email format.");
            }
            
            if (password !== "") { // Check if password is provided
                if (password.length < 8 || password.length > 16) {
                    errors.push("Password must be between 8 and 16 characters.");
                }
            }
            
            if (mobile === "") {
                errors.push("Mobile number is required.");
            } else if (mobile.length !== 10) {
                errors.push("Mobile number must be 10 digits.");
            }
            
            // Display errors using SweetAlert with bullet points
            if (errors.length > 0) {
                var errorMessage = `<div class="error-list">${errors.map(error => `• ${error}`).join("<br>")}</div>`;
                Swal.fire({
                    icon: 'error',
                    title: 'Update Error',
                    html: errorMessage,
                    showCloseButton: true,
                });
                
                return false; // Prevent form submission
            }
            
            return true; // Allow form submission
        }
        
        function validateEmail(email) {
            var re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return re.test(email);
        }
        
        function enableEdit(field) {
            document.getElementById(field).readOnly = false;
        }
        
        function openFileInput() {
            document.getElementById('profile_pic').click();
        }    
    </script>
</head>
<body>
<div class="container">
    <h1>My Profile</h1>
        <!-- <a href="home.php" class="back-button">Back</a> -->
        <?php if (isset($_GET['success'])) { ?>
            <div class="success-message">
                Update successful!
            </div>
        <?php } elseif (isset($_GET['error'])) { ?>
            <div class="error-message">
                Update failed!
            </div>
        <?php } ?>
        <form action="" method="POST" onsubmit="return validateForm();" enctype="multipart/form-data">
        <!-- <div class="profile-picture">
            <img src="<?php //echo $row['img_srcs']; ?>" alt="Profile Picture" id="profilePicPreview">
            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" style="display: none;" onchange="previewImage(event)">
            <button type="button" class="edit-button" onclick="openFileInput()">Edit</button>
        </div>        -->
        
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" readonly>
            <button type="button" class="edit-button" onclick="enableEdit('name')">Edit</button>
        </div>
        <div class="form-group">
            <label for="district">District:</label>
            <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($row['district']); ?>" readonly>
            <button type="button" class="edit-button" onclick="enableEdit('district')">Edit</button>

        </div>
        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($row['city']); ?>" readonly>
            <button type="button" class="edit-button" onclick="enableEdit('city')">Edit</button>

        </div>
        <div class="form-group">
            <label for="contact">Mobile:</label>
            <input type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($row['mobile']); ?>" readonly>
            <button type="button" class="edit-button" onclick="enableEdit('mobile')">Edit</button>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" readonly>
            <button type="button" class="edit-button" onclick="enableEdit('email')">Edit</button>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter new password">
            <button type="button" class="edit-button" onclick="enableEdit('password')">Edit</button>
        </div>
        
        <div class="form-group">
            <input type="submit" value="Save Changes">
        </div>
    </form>
    <a href="../dashboard.php">Back</a>
</div>
</body>
</html>