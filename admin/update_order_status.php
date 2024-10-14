<?php
session_start();
include('../database/connection.php');

// Ensure the user is an admin (optional)
if (!isset($_SESSION['adminemail'])) {
    header("Location: admin_login.php");
    exit();
}

// Check if the request method is POST and the required fields are set
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['order_id'], $_POST['status'])) {
        $order_id = intval($_POST['order_id']);
        $status = $_POST['status'];
        
        // Check if the status is valid (optional validation)
        $valid_statuses = ['shipped', 'delivered']; 
        if (in_array($status, $valid_statuses)) {
            // Update the order status in the database
            $update_query = "UPDATE orders SET status = ? WHERE order_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $status, $order_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Order status updated successfully.";
            } else {
                $_SESSION['error_message'] = "Error updating order status: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Invalid order status.";
        }
    } else {
        $_SESSION['error_message'] = "Order ID and status are required.";
    }
}

// Redirect back to the orders management page (or any other page)
header("Location: admin_panel.php"); // Adjust this URL to your admin panel or order management page
exit();
?>
