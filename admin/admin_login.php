<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Database Connection
include('../database/connection.php');
session_start();
if(isset($_POST['login']))
{
    $adminname = $_POST['adminname'];
    $adminpassword = $_POST['adminpassword'];
    
    $sql = "SELECT * FROM admin WHERE email = '$adminname' and password = '$adminpassword'";

    $result = $conn->query($sql);
    if($result->num_rows > 0){
        $_SESSION['adminname'] = $adminname;

        // For logout Purpose
        $_SESSION['admin'] =  $_POST['adminname'];
        // Redirect to the admin panel page
        header ("Location: categoryandproductCRUD.php");
        exit();
    }else{
        // Redirect to the same login page with an error message
        header("Location: admin_login.php?error=1");
        exit;
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
    <link rel="stylesheet" href="css/loginandreg.css">
    <title>Admin Login Page</title>
  </head>
  <body>
    <div class="container-fulid">
      <form class="mx-auto">
        <h4 class="text-center">Admin Login</h4>
        <h6 class="text-center">Please enter e-mail and password:</h6>

        <div class="mb-3 mt-5">
          <label for="exampleInputEmail1" class="form-label">Email:</label>
          <input type="email" class="form-control" name="adminemail" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter your email or username" required>
        </div>

        <div class="mb-3">
          <label for="exampleInputPassword1" class="form-label">Password:</label>
          <input type="password" class="form-control" name="adminpassword" id="exampleInputPassword1" placeholder="Enter your password" required>
          <!-- <div id="emailHelp" class="form-text mt-3" >Forget Password?
          <a href="#" id="emailHelp" class="form-text">Click Here!!!</a>
        </div> -->

        </div>
        <!-- <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="exampleCheck1">
          <label class="form-check-label" for="exampleCheck1">Check me out</label>
        </div> -->
        <button type="submit" name="login" value="login" class="btn btn-primary mt-4">LOGIN</button>
        <!-- <div id="emailHelp" class="form-text mt-3" align ="center" >New Customer?
          <a href="registration.php" id="emailHelp" class="form-text">Create an accout</a>
        </div> -->
      </form>

    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

  </body>
</html>