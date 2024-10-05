<?php
session_start();
include('../database/connection.php');

// Check if user is logged in
if (!isset($_SESSION['cid'])) {
    header('Location: login.php');
    exit();
}

$customer_id = $_SESSION['cid'];

// Fetch Cart Items for Logged-in User
$sql = "SELECT products.product_id, products.name AS product_name, products.description, products.price,
        cartItems.quantity, (products.price * cartItems.quantity) AS total_price FROM 
        cart INNER JOIN cartItems ON Cart.cart_id = cartItems.cart_id INNER JOIN 
        products ON cartItems.product_id = products.product_id WHERE cart.cid = ? ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);  // Bind the customer_id to the query
$stmt->execute();
$result = $stmt->get_result();

// Check if there are items in the cart
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='cart-item'>";
        echo "<h3>" . htmlspecialchars($row['product_name']) . "</h3>";
        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
        echo "<p>Price: $" . htmlspecialchars($row['price']) . "</p>";
        echo "<p>Quantity: " . htmlspecialchars($row['quantity']) . "</p>";
        echo "<p>Total Price: $" . htmlspecialchars($row['total_price']) . "</p>";
        echo "</div><hr>";
    }
} else {
    echo "<p>Your cart is empty.</p>";
}
?>
<form method="POST" action="view_product.php">
    <button type="submit">Place Order</button>
</form>
<a href="../dashboard.php">Continue Shopping</a>

































