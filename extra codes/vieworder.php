<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['cid'])) {
    header('Location: login.php');
    exit();
}

include('../database/connection.php'); // Database connection
include('../customer/layout/cheader.php');

// Assuming customer is logged in and we have their customer ID in the session
$customer_id = $_SESSION['cid'];

// SQL query to fetch the order details for the logged-in customer
$order_query = "SELECT o.order_id, o.created_at AS order_date, o.total_price, o.status, oi.order_item_id, 
                oi.quantity, oi.price AS item_price, p.product_id, p.name AS product_name, 
                p.description AS product_description, p.image_url
                FROM `orders` o JOIN orderItems oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.cid = ? ORDER BY o.order_id DESC; ";

$stmt = $conn->prepare($order_query);
$stmt->bind_param('i', $customer_id); // Bind customer ID to the query
$stmt->execute();
$result = $stmt->get_result();
?>


<!-- Display the orders and items -->
<div class="my-orders">
<h2 style="text-align: center; padding: 20px;">My Orders</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product Name</th>
                <th>Product Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Product Image</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php while ($order = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
            <td><?php echo htmlspecialchars($order['product_description']); ?></td>
            <td><?php echo number_format($order['item_price'], 2); ?></td>
            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
            <td><?php echo number_format($order['quantity'] * $order['item_price'], 2); ?></td>
            <!-- <td><?php //echo htmlspecialchars($order['total_price']); ?></td> -->
            <td><img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt="Product Image" width="100"></td>
            <td><?php echo date('d-m-Y', strtotime($order['order_date'])); ?></td>
            <td><?php echo htmlspecialchars($order['status']); ?></td>
            <td>
            <!-- Cancel Order Form -->
            <form method="post" action="cancel_order.php">
                <input type="hidden" value="<?php echo htmlspecialchars($order['order_id']); ?>" name="orderid" />
                <!-- Disable cancel button if order is delivered/completed or already canceled -->
                <input type="submit" value="Cancel Order" name="cancel_order" 
                    <?php if ($order['status'] == 'shipped' || $order['status'] == 'delivered' || $order['status'] == 'canceled') echo 'disabled'; ?> />
            </form>
            <br>
            <!-- Give Review Form -->
            <form method="post" action="review.php">
                <input type="hidden" value="<?php echo htmlspecialchars($order['order_id']); ?>" name="orderid" />
                <!-- Disable Give Review button if order is not completed -->
                <input type="submit" value="Give Review" name="give_review" 
                    <?php if ($order['status'] != 'completed' && $order['status'] != 'delivered') echo 'disabled'; ?> />
            </form>
            </td>
        </tr>
            </div>  
        </div>
        <hr>
    <?php } ?> 
</div>


<a href="../dashboard.php">Back to Dashboard</a>
