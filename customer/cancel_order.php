<?php
session_start();
include('../database/connection.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = intval($_POST['order_id']);
    $message = '';
    $isError = false;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get the order items and their quantities
        $itemsQuery = $conn->prepare("SELECT product_id, quantity FROM OrderItems WHERE order_id = ?");
        $itemsQuery->bind_param("i", $order_id);
        $itemsQuery->execute();
        $itemsResult = $itemsQuery->get_result();
        
        // Update stock for each product
        while ($item = $itemsResult->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Update stock in the Products table
            $restockQuery = $conn->prepare("UPDATE Products SET stock = stock + ? WHERE product_id = ?");
            $restockQuery->bind_param("ii", $quantity, $product_id);
            $restockQuery->execute();
            $restockQuery->close();
        }

        // Cancel the order
        $cancelQuery = $conn->prepare("UPDATE Orders SET status = 'canceled' WHERE order_id = ?");
        $cancelQuery->bind_param("i", $order_id);
        $cancelQuery->execute();

        // Commit transaction
        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Your order has been canceled successfully.";
        
        $itemsQuery->close();
        $cancelQuery->close();
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error canceling order: " . $e->getMessage();
        // $isError = true;
    }

    // Close connection
    $conn->close();

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($response);

    // Output the SweetAlert script
    // echo "<script>
    //         Swal.fire({
    //             title: '" . ($isError ? "Error!" : "Success!") . "',
    //             text: '$message',
    //             icon: '" . ($isError ? "error" : "success") . "',
    //             confirmButtonText: 'OK'
    //         });
    //       </script>";
}
?>
