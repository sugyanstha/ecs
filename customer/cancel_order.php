<?php
session_start();
include('../database/connection.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = intval($_POST['order_id']);
    $response = []; // Initialize response array

    // Start transaction
    $conn->begin_transaction();

    try {
        // First, delete the order items
        $deleteItemsQuery = $conn->prepare("DELETE FROM OrderItems WHERE order_id = ?");
        $deleteItemsQuery->bind_param("i", $order_id);
        $deleteItemsQuery->execute();
        
        // Then, delete the order itself
        $deleteOrderQuery = $conn->prepare("DELETE FROM Orders WHERE order_id = ?");
        $deleteOrderQuery->bind_param("i", $order_id);
        $deleteOrderQuery->execute();

        // Commit transaction
        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Your order has been deleted successfully.";
        
        // Close the prepared statements
        $deleteItemsQuery->close();
        $deleteOrderQuery->close();
    } catch (Exception $e) {
        $conn->rollback();
        $response['success'] = false;
        $response['message'] = "Error deleting order: " . $e->getMessage();
    }

    // Close connection
    $conn->close();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
