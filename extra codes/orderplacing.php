<?php
session_start();
include('../database/connection.php');

// Check if user is logged in
if (!isset($_SESSION['cid'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['cid'];

// Fetch product ID and quantity from the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['product_id'], $_POST['quantity'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Fetch product details (price, stock) to calculate total and check availability
            $stmt = $conn->prepare("SELECT price, stock FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();

            // Ensure the product exists and stock is sufficient
            if ($product && $product['stock'] >= $quantity) {
                $total_price = $product['price'] * $quantity;

                // Insert the order into the orders table
                $sql_order = "INSERT INTO orders (cid, total_price, status) VALUES (?, ?, 'pending')";
                $stmt_order = $conn->prepare($sql_order);
                $stmt_order->bind_param("id", $customer_id, $total_price);
                $stmt_order->execute();

                // Get the last inserted order ID
                $order_id = $conn->insert_id;

                // Insert order details into order_items table
                $sql_order_item = "INSERT INTO orderitems (order_id, product_id, quantity, price) 
                                   VALUES (?, ?, ?, ?)";
                $stmt_order_item = $conn->prepare($sql_order_item);
                $stmt_order_item->bind_param("iiid", $order_id, $product_id, $quantity, $product['price']);
                $stmt_order_item->execute();

                // Update product stock
                $sql_update_stock = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
                $stmt_update_stock = $conn->prepare($sql_update_stock);
                $stmt_update_stock->bind_param("ii", $quantity, $product_id);
                $stmt_update_stock->execute();

                // Commit the transaction
                $conn->commit();

                // Order placed successfully, redirect to my orders page
                header("Location: myorders.php");
                exit();

            } else {
                // Rollback transaction if stock is insufficient
                $conn->rollback();
                echo "<script>alert('Insufficient stock or product not available.');</script>";
                header("Location: view_product.php");
                exit();
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "<script>alert('An error occurred while placing the order. Please try again.');</script>";
            header("Location: view_product.php");
            exit();
        }
    }
}
?>