<?php
session_start();
include('../database/connection.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = intval($_POST['order_id']);
    $response = [];
    $conn->begin_transaction();

    try {
        $itemsQuery = $conn->prepare("SELECT product_id, quantity FROM OrderItems WHERE order_id = ?");
        $itemsQuery->bind_param("i", $order_id);
        $itemsQuery->execute();
        $itemsResult = $itemsQuery->get_result();
        while ($item = $itemsResult->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $restockQuery = $conn->prepare("UPDATE Products SET stock = stock + ? WHERE product_id = ?");
            $restockQuery->bind_param("ii", $quantity, $product_id);
            $restockQuery->execute();
            $restockQuery->close();
        }
        $itemsQuery->close();
        $deleteItemsQuery = $conn->prepare("DELETE FROM OrderItems WHERE order_id = ?");
        $deleteItemsQuery->bind_param("i", $order_id);
        $deleteItemsQuery->execute();
        $deleteOrderQuery = $conn->prepare("DELETE FROM Orders WHERE order_id = ?");
        $deleteOrderQuery->bind_param("i", $order_id);
        $deleteOrderQuery->execute();

        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Your order has been deleted successfully.";
        
        $deleteItemsQuery->close();
        $deleteOrderQuery->close();
    } catch (Exception $e) {
        $conn->rollback();
        $response['success'] = false;
        $response['message'] = "Error deleting order: " . $e->getMessage();
    }

    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
