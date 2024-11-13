<?php
session_start();
include('../database/connection.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];

if (isset($_POST['addtocart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Fetch product details
    $stmt = $conn->prepare("SELECT name, description, price, stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Check stock availability
    if ($product && $product['stock'] >= $quantity) {
        // Check if user already has a cart
        $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE cid = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Create a new cart if it doesn't exist
        if ($stmt->num_rows === 0) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO cart (cid) VALUES (?)");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $cart_id = $stmt->insert_id; // Get the new cart_id
            } else {
                die("Error creating cart: " . $stmt->error);
            }
        } else {
            // Fetch the existing cart_id
            $stmt->bind_result($cart_id);
            $stmt->fetch();
        }

        // Add item to cartItems table
        $sql = "INSERT INTO cartItems (cart_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
        if ($stmt->execute()) {
            // Successful addition
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Product added to cart successfully'];
            header("Location: cart.php");
            exit();
        } else {
            die("Error adding product to cart: " . $stmt->error);
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Insufficient stock!'];
    }
}
?>
