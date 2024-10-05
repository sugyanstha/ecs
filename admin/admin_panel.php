<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();  // Start the session

if (!isset($_SESSION['adminemail'])) {
    // If admin is not logged in, redirect to the login page
    header("Location: admin_login.php");
    exit();
}

// Database Connection
include('../database/connection.php');

// Fetch orders
$order_query = "SELECT o.order_id, o.cid AS customer_id, o.total_price, o.status, o.created_at, 
                oi.order_item_id, oi.product_id, p.name AS product_name, oi.quantity, oi.price
                FROM orders o 
                JOIN orderitems oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                ORDER BY o.order_id";
$orders = mysqli_query($conn, $order_query);

if (!$orders) {
    // Handle query error
    die("Query Failed: " . mysqli_error($conn));
}

include('../admin/layout/adminheader.php');
?>


<link rel="stylesheet" href="../css/adminpanel.css">
<div class="main-content">
    <h2 align="center">Orders List</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Order Item ID</th>
                <th>Created At</th>
                <th>Customer ID</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($order = mysqli_fetch_assoc($orders)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                <td><?php echo htmlspecialchars($order['order_item_id']); ?></td>
                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_id']); ?></td>
                <td><?php echo htmlspecialchars($order['product_id']); ?></td>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                <td><?php echo htmlspecialchars($order['total_price']); ?></td>
                <td><?php echo htmlspecialchars($order['status']); ?></td>
                <td>
                    <form action="update_order_status.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                        <select name="status" onchange="this.form.submit()" class="form-select">
                            <option value="pending" <?php if ($order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                            <option value="completed" <?php if ($order['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                        </select>
                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
