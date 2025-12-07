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

// Query to get reviews with product + customer details
$sql = "SELECT reviews.review_id, reviews.rating, reviews.comment, reviews.created_at, 
        products.name AS product_name, customer.name AS customer_name 
        FROM reviews 
        JOIN products ON reviews.product_id = products.product_id
        JOIN customer ON reviews.cid = customer.cid
        ORDER BY reviews.created_at DESC";

$result = $conn->query($sql);

// Function to convert rating to stars
function displayStars($rating){
    $stars = "";
    for($i = 1; $i <= 5; $i++){
        if($i <= $rating){
            $stars .= "<span class='star filled'>&#9733;</span>";
        } else {
            $stars .= "<span class='star'>&#9733;</span>";
        }
    }
    return $stars;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Reviews</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f5f7;
            margin: 0;
            padding: 20px;
        }
        .main-content {
            width: 90%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 3px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 14px;
        }
        thead {
            background-color: #242627ff;
            color: white;
        }
        thead th {
            padding: 12px;
        }
        tbody tr {
            background-color: #fff;
            transition: .3s;
        }
        tbody tr:nth-child(even){
            background-color: #f0f8ff;
        }
        tbody tr:hover{
            background-color: #d6ecff;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .star {
            font-size: 18px;
            color: #ccc;
        }
        .star.filled {
            color: #ffb400;
        }
        .avatar {
            width: 35px;
            height: 35px;
            background: #0073e6;
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            margin-right: 8px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="main-content">
        <h2 align="center">Customer Reviews</h2>

        <div class="table-wrapper">
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                    $initials = substr($row['customer_name'], 0, 1);
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['review_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td>
                            <span class="avatar"><?php echo $initials; ?></span>
                            <?php echo htmlspecialchars($row['customer_name']); ?>
                        </td>
                        <td><?php echo displayStars($row['rating']); ?></td>
                        <td><?php echo htmlspecialchars($row['comment']); ?></td>
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
