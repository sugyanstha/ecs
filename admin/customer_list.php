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

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete orders associated with the customer
        $delete_orders_sql = "DELETE FROM orders WHERE cid = '$customer_id'";
        if (!$conn->query($delete_orders_sql)) {
            throw new Exception("Error deleting orders: " . $conn->error);
        }

        // Delete customer
        $delete_customer_sql = "DELETE FROM customer WHERE cid = '$customer_id'";
        if (!$conn->query($delete_customer_sql)) {
            throw new Exception("Error deleting customer: " . $conn->error);
        }

        // Commit transaction
        $conn->commit();

        // Invalidate customer session if logged in
        if (isset($_SESSION['cid']) && $_SESSION['cid'] == $customer_id) {
            session_destroy(); // Log out the customer
            header("Location: login.php"); // Redirect to login page
            exit;
        }

        // Redirect after deletion
        header("Location: customer_list.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
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
    <h1 align="center">List of Customers</h1>
    <div class="table-wrapper">
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
