<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Database Connection
include('database/connection.php');


//Check is the use is already logged in
if(isset($_SESSION['email'])){
    header("location: ../customer/dashboard.php");
    exit;
}

//Submission Process
if (isset($_POST["login"])){
    //Get Data(i.e. Email and Password from the form)
    $email = strtolower($_POST["email"]);
    $password = $_POST["password"];

    //Use prepared statement to prevent SQL injection
    $sql = "SELECT * FROM customer WHERE email = ?";

    //Preapres the SQL statement, binds the email parameter, executes statement and retrice the result
    $stmt = $conn->prepare($sql);
    $stmt->blind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    //If there is at least one row in the result, fetchs the associative array representing the user data and
    //retrieves the hashed password from the database
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $hashedpassword = $row['password'];

        if(password_verify($password, $hashedpassword)){
            //Password matches, store user details in session
            $_SESSION['cid'] = $row['cid'];
            $_SESSION['email'] = $email;
            header("location: ../customer/dashboard.php");
            exit;
        }else {
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
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="css/loginandreg.css">
    <title>Login Page</title>
  </head>
  <body>
    <div class="container-fulid">
      <form class="mx-auto">
        <h4 class="text-center">Login</h4>
        <h6 class="text-center">Please enter your e-mail and password:</h6>

        <div class="mb-3 mt-5">
          <label for="exampleInputEmail1" class="form-label">Email:</label>
          <input type="email" class="form-control" name="email" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter your email" required>
        </div>

        <div class="mb-3">
          <label for="exampleInputPassword1" class="form-label">Password:</label>
          <input type="password" class="form-control" name="password" id="exampleInputPassword1" placeholder="Enter your password" required>
          <div id="emailHelp" class="form-text mt-3" >Forget Password?
          <a href="#" id="emailHelp" class="form-text">Click Here!!!</a>
        </div>

        </div>
        <!-- <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="exampleCheck1">
          <label class="form-check-label" for="exampleCheck1">Check me out</label>
        </div> -->
        <button type="submit" name="login" value="login" class="btn btn-primary mt-4">LOGIN</button>
        <div id="emailHelp" class="form-text mt-3" align ="center" >New Customer?
          <a href="registration.php" id="emailHelp" class="form-text">Create an accout</a>
        </div>
      </form>

    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

  </body>
</html>