<?php
// Database connection
include("../database/connection.php");
include("../admin/layout/adminheader.php");

// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['adminemail'])) {
    header("location:adminlogin.php");
    exit;
}

// Query to get reviews along with product and customer details
$sql = "SELECT reviews.review_id, reviews.rating, reviews.comment, reviews.created_at, 
        products.name AS product_name, customer.name AS customer_name 
        FROM reviews 
        JOIN products ON reviews.product_id = products.product_id
        JOIN customer ON reviews.cid = customer.cid
        ORDER BY reviews.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Reviews</title>
    <link rel="stylesheet" href="../css/adminpanel.css">
</head>
<body>
    <div class="main-content">
        <h2 align="center">Customer Reviews</h2>
        <div class="table-wrapper">
        <?php if ($result->num_rows > 0): ?>
            <table class="f1-table">
                <thead>
                    <tr>
                        <th>Review ID</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['review_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['rating']); ?></td>
                            <td><?php echo htmlspecialchars($row['review_text']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No reviews found.</p>
        <?php endif; ?>
        </div>
    </div>
</body>
</html>
