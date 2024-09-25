<?php
session_start();
if (!isset($_SESSION['c_id'])) {
    header('Location: login.php');
    exit();
}

include('../database/connection.php'); // Database connection

// Fetch user's orders
$stmt = $mysqli->prepare("
    SELECT orders.order_id, orders.total_price, orders.order_date 
    FROM orders 
    WHERE orders.cid = ?
");
$stmt->bind_param('i', $_SESSION['cid']);
$stmt->execute();
$orders = $stmt->get_result();
?>

<h1>Your Orders</h1>
<table>
    <tr>
        <th>Order ID</th>
        <th>Total Price</th>
        <th>Order Date</th>
    </tr>
    <?php while ($order = $orders->fetch_assoc()): ?>
        <tr>
            <td><?= $order['order_id']; ?></td>
            <td>$<?= $order['total_price']; ?></td>
            <td><?= $order['order_date']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<a href="dashboard.php">Back to Dashboard</a>
