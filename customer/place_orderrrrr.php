<?php
session_start();
include('../database/connection.php');

// Check if user is logged in
if (!isset($_SESSION['cid'])) {
    header('Location: login.php');
    exit();
}

$customer_id = $_SESSION['cid'];

// Fetch items from the Customer cart
$sql_cart = "SELECT Products.product_id, Products.price, CartItems.quantity FROM CartItems 
            INNER JOIN Products ON CartItems.product_id = Products.product_id INNER JOIN 
            Cart ON Cart.cart_id = CartItems.cart_id WHERE Cart.cid = ?";

$stmt = $conn->prepare($sql_cart);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Check if cart is not empty
if ($cart_items->num_rows > 0) {
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Calculate total amount
        $total_amount = 0;
        while ($item = $cart_items->fetch_assoc()) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        // Insert the order into the Orders table
        $sql_order = "
            INSERT INTO orders (cid, total_amount, order_status) 
            VALUES (?, ?, 'pending')
        ";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("id", $user_id, $total_amount);
        $stmt_order->execute();

        // Get the last inserted order_id
        $order_id = $conn->insert_id;

        // Insert each cart item into OrderItems table
        foreach ($cart_items as $item) {
            $sql_order_item = "
                INSERT INTO orderitems (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ";
            $stmt_order_item = $conn->prepare($sql_order_item);
            $stmt_order_item->bind_param(
                "iiid", 
                $order_id, 
                $item['product_id'], 
                $item['quantity'], 
                $item['price']
            );
            $stmt_order_item->execute();
        }

        // Clear the user's cart
        $sql_clear_cart = "DELETE FROM cartitems WHERE cart_id = (SELECT cart_id FROM cart WHERE cid = ?)";
        $stmt_clear_cart = $conn->prepare($sql_clear_cart);
        $stmt_clear_cart->bind_param("i", $user_id);
        $stmt_clear_cart->execute();

        // Commit transaction
        $conn->commit();

        echo "<script>alert('Order placed successfully!');</script>";
        header("Location: order_confirmation.php?order_id=$order_id");
        exit;
    } catch (Exception $e) {
        // Rollback transaction if any error occurs
        $conn->rollback();
        echo "<script>alert('Something went wrong! Please try again.');</script>";
    }
} else {
    echo "<script>alert('Your cart is empty!');</script>";
    header('Location: view_products.php');
    exit();
}
?>
