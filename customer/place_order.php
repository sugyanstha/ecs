<?php
session_start();
include('../database/connection.php');

// Check if user is logged in
if (!isset($_SESSION['cid'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['cid'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['product_id'], $_POST['quantity'], $_POST['shipping_address'], $_POST['city'])) {
        // Sanitize and validate inputs
        $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        $shipping_address = htmlspecialchars(trim($_POST['shipping_address']));
        $city = htmlspecialchars(trim($_POST['city']));

        if ($product_id === false || $quantity === false || empty($shipping_address) || empty($city)) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Invalid input data. Please check the form and try again.'
            ];
            header("Location: view_product.php");
            exit();
        }

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
                $sql_order = "INSERT INTO orders (cid, total_price, shipping_address, city, status) VALUES (?, ?, ?, ?, 'pending')";
                $stmt_order = $conn->prepare($sql_order);
                $stmt_order->bind_param("idss", $customer_id, $total_price, $shipping_address, $city);
                $stmt_order->execute();

                // Get the last inserted order ID
                $order_id = $conn->insert_id;

                // Insert order details into orderitems table
                $sql_order_item = "INSERT INTO orderitems (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt_order_item = $conn->prepare($sql_order_item);
                $stmt_order_item->bind_param("iiid", $order_id, $product_id, $quantity, $product['price']);
                $stmt_order_item->execute();

                // Update product stock
                $sql_update_stock = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
                $stmt_update_stock = $conn->prepare($sql_update_stock);
                $stmt_update_stock->bind_param("ii", $quantity, $product_id);
                $stmt_update_stock->execute();

                // Check if stock update was successful
                if ($stmt_update_stock->affected_rows == 0) {
                    throw new Exception("Error updating stock.");
                }

                // Commit the transaction
                $conn->commit();

                // Set success message in session
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Your order has been placed successfully.'
                ];
            } else {
                // Rollback transaction if stock is insufficient
                $conn->rollback();
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Insufficient stock or product not available.'
                ];
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'An error occurred while placing the order. Please try again.'
            ];

            // Log the error for debugging purposes
            error_log($e->getMessage());
        }

        // Redirect back to view_product.php with a message
        header("Location: view_product.php");
        exit();
    } else {
        // If required POST data is missing
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Missing required data. Please try again.'
        ];
        header("Location: view_product.php");
        exit();
    }
}
?>
