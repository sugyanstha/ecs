<?php
session_start();

include('../database/connection.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = intval($_POST['order_id']);

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
            
            // Assume there's a Products table with stock column
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
        echo "Order canceled and products restocked successfully.";
        
        $itemsQuery->close();
        $cancelQuery->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error canceling order: " . $e->getMessage();
    }
}

// Close connection
$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Order</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <h2>Cancel Your Order</h2>
    <!-- Button to open the modal -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#cancelOrderModal">
        Cancel Order
    </button>

    <!-- Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Enter Order ID</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="cancel_order.php" method="post">
                        <div class="form-group">
                            <label for="order_id">Order ID:</label>
                            <input type="number" class="form-control" id="order_id" name="order_id" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Cancel Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
