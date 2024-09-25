<?php
session_start();
if (!isset($_SESSION['cid'])) {
    header('Location: login.php');
    exit();
}

include('../database/connection.php'); // Database connection

// Add product to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $cid = $_SESSION['cid'];

    // Insert or update the product in the cart
    $stmt = $mysqli->prepare("SELECT * FROM cart WHERE cid = ? AND product_id = ?");
    $stmt->bind_param('ii', $cid, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $mysqli->prepare("UPDATE cart SET quantity = quantity + ? WHERE cid = ? AND product_id = ?");
        $stmt->bind_param('iii', $quantity, $cid, $product_id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO cart (cid, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $cid, $product_id, $quantity);
    }
    $stmt->execute();
    header('Location: cart.php');
    exit();
}

// Display cart
$stmt = $mysqli->prepare("
    SELECT cart.cart_id, products.name, products.price, cart.quantity 
    FROM cart 
    JOIN products ON cart.product_id = products.product_id 
    WHERE cart.cid = ?
");
$stmt->bind_param('i', $_SESSION['cid']);
$stmt->execute();
$cart_items = $stmt->get_result();
?>

<h1>Your Cart</h1>
<table>
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Total</th>
    </tr>
    <?php $total_price = 0; ?>
    <?php while ($item = $cart_items->fetch_assoc()): ?>
        <tr>
            <td><?= $item['name']; ?></td>
            <td>$<?= $item['price']; ?></td>
            <td><?= $item['quantity']; ?></td>
            <td>$<?= $item['price'] * $item['quantity']; ?></td>
        </tr>
        <?php $total_price += $item['price'] * $item['quantity']; ?>
    <?php endwhile; ?>
    <tr>
        <td colspan="3">Total</td>
        <td>$<?= $total_price; ?></td>
    </tr>
</table>

<form method="POST" action="place_order.php">
    <button type="submit">Place Order</button>
</form>

<a href="dashboard.php">Continue Shopping</a>
