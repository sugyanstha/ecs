<?php
session_start();
include('../database/connection.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];

// Fetch Cart ID for the Logged-in User
$sql = "SELECT cart_id FROM cart WHERE cid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "<p>Your cart is empty.</p>";
    exit();
} else {
    $stmt->bind_result($cart_id);
    $stmt->fetch();
}

// Fetch Cart Items
$sql = "SELECT products.product_id, products.name AS product_name, products.description, products.price,
        cartItems.quantity, (products.price * cartItems.quantity) AS total_price 
        FROM cart 
        INNER JOIN cartItems ON cart.cart_id = cartItems.cart_id 
        INNER JOIN products ON cartItems.product_id = products.product_id 
        WHERE cart.cart_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Your Cart</title>
</head>
<body>
<div class="container mt-5">
    <h1>Your Cart</h1>
    <?php if ($result->num_rows > 0): ?>
        <div class="cart-items">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['product_name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="card-text">Price: NRs. <?php echo htmlspecialchars($row['price']); ?></p>
                        <p class="card-text">Quantity: <?php echo htmlspecialchars($row['quantity']); ?></p>
                        <p class="card-text">Total Price: NRs. <?php echo htmlspecialchars($row['total_price']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <form method="POST" action="place_order.php">
            <button type="submit" class="btn btn-success">Place Order</button>
        </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
    <a href="../dashboard.php" class="btn btn-primary mt-3">Continue Shopping</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
