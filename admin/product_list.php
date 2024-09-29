<?php
include('../admin/layout/adminheader.php');

// Check if the user is logged in; redirect to login page if not logged in
session_start();
if (!isset($_SESSION['adminemail'])) {
    header("location:admin_login.php");
}
$adminname = $_SESSION['adminemail'];

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include("../database/connection.php");

// Query to join Products and Categories and get the product list
$product_query = "SELECT p.product_id, p.name AS product_name, p.description, p.price, p.stock, 
                p.image_url, p.created_at, p.updated_at, c.name AS category_name
                FROM Products p JOIN Categories c ON p.category_id = c.category_id
                ORDER BY p.product_id;";

// Execute the query
$product_result = mysqli_query($conn, $product_query);

// Check if query succeeded
if (!$product_result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<link rel="stylesheet" href="../css/adminpanel.css">
<div class="main-content">
    <?php if (!isset($_POST['add'])) { ?>
        <h1 align="center">List of Products</h1>
        <div class="table-wrapper">
            <form action="product.php" method="post">
                <input type="submit" value="Add Product" name="add">
            </form>
            <table class="fl-table">
                <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                    <?php if ($product_result && $product_result->num_rows > 0) {
                        while ($row = $product_result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['price']); ?></td>
                                <td><?php echo htmlspecialchars($row['stock']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Product Image" width="50"></td>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                                <td>
                                    <div class="button-row">
                                        <form method="post" action="#">
                                            <input type="hidden" value="<?php echo $row['product_id']; ?>" name="product_id" />
                                            <input type="submit" value="Edit" name="edit" />
                                        </form>
                                        <form method="post" action="#">
                                            <input type="hidden" value="<?php echo $row['product_id']; ?>" name="product_id" />
                                            <input type="submit" value="Delete" name="delete" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="10">No Product Items Listed.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>
