<?php
include('../admin/layout/adminheader.php');

// Check if the user is logged in; redirect to login page if not logged in
session_start();
if (!isset($_SESSION['adminemail'])) {
    header("location:admin_login.php");
}
$adminname = $_SESSION['adminemail'];

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include("../database/connection.php");

// Delete Category
if (isset($_POST['remove'])) {
    $customer_id = $_POST['cid'];
    $sql = "DELETE FROM customer WHERE cid='$customer_id'";
    $result = $conn->query($sql);
    if ($result) {
        header("Location: customer_list.php");
        exit; // Redirect after deletion
    } else {
        die("Error: " . $conn->error);
    }
}

// Query to get the Customer list
$customer_query = "SELECT `cid`, `name`, `district`, `city`, `mobile`, `email` FROM `customer`";

// Execute the query
$customer_result = mysqli_query($conn, $customer_query);

// Check if query succeeded
if (!$customer_result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<link rel="stylesheet" href="../css/adminpanel.css">
<div class="main-content">
    <?php //if (!isset($_POST['add'])) { ?>
        <h1 align="center">List of Customers</h1>
        <div class="table-wrapper">
            <!-- <form action="product.php" method="post">
                <input type="submit" value="Add Product" name="add">
            </form> -->
            <table class="fl-table">
                <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>District</th>
                    <th>City</th>
                    <th>Mobile Number</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                    <?php if ($customer_result && $customer_result->num_rows > 0) {
                        while ($row = $customer_result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['cid']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['district']); ?></td>
                                <td><?php echo htmlspecialchars($row['city']); ?></td>
                                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <div class="button-row">
                                        <!-- <form method="post" action="edit_product.php">
                                            <input type="hidden" value="<?php echo $row['product_id']; ?>" name="product_id" />
                                            <input type="submit" value="Edit" name="edit" />
                                        </form> -->
                                        <form method="post" action=" ">
                                            <input type="hidden" value="<?php echo $row['cid']; ?>" name="cid" />
                                            <input type="submit" value="Remove Customer" name="remove" 
                                            onclick="return confirm('Are you sure you want to remove this customer?');"
                                            style="background-color: red; color: white; border: none; cursor: pointer;" 
                                            onmouseover="this.style.backgroundColor='darkred';" 
                                            onmouseout="this.style.backgroundColor='red';" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="10">No Customer Have Registered Yet.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
    </div>
</div>
