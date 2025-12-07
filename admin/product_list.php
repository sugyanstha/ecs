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

// === INVENTORY REORDER ALGORITHM INCLUDE (no extra table version) ===
require_once __DIR__ . '/../algorithms/inventory_reorder.php';

// Get current inventory reorder suggestions
$reorderSuggestions = get_reorder_suggestions();

// Delete Product
if (isset($_POST['delete'])) {
    $product_id = $_POST['product_id'];
    $sql = "DELETE FROM products WHERE product_id='$product_id'";
    $result = $conn->query($sql);
    if ($result) {
        header("Location: product_list.php");
        exit; // Redirect after deletion
    } else {
        die("Error: " . $conn->error);
    }
}

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

        <!-- INVENTORY REORDER SECTION (Styled to match your tables) -->
        <?php if (!empty($reorderSuggestions)): ?>
            <?php 
                $reorderCount = count($reorderSuggestions); 
                $plural = $reorderCount > 1 ? 'products' : 'product';
            ?>
            <div style="margin: 20px 0; text-align:center; font-size:16px; color:black;">
    There <?php echo $reorderCount > 1 ? 'are' : 'is'; ?>
    <strong style="color:black;"><?php echo $reorderCount; ?></strong>
    <?php echo $plural; ?> that need reordering.
</div>


            <div class="table-wrapper" style="margin-bottom: 30px;">
                <table class="fl-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current Stock</th>
                            <th>Avg Daily Sales</th>
                            <th>Reorder Point</th>
                            <th>Recommended Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Show all, or limit if you want (you can add a limit here)
                    foreach ($reorderSuggestions as $r): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($r['name']); ?><br>
                                <small>ID: <?php echo (int)$r['product_id']; ?></small>
                            </td>
                            <td><?php echo (int)$r['current_stock']; ?></td>
                            <td><?php echo number_format($r['avg_daily_sales'], 2); ?></td>
                            <td><?php echo number_format($r['reorder_point'], 2); ?></td>
                            <td><strong><?php echo (int)$r['recommended_qty']; ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <!-- END INVENTORY REORDER SECTION -->

        <h1 align="center">List of Products</h1>
        <div class="table-wrapper">
            <form action="product.php" method="post" style="margin-bottom:15px;">
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
                                <td><img src="../img/<?php echo htmlspecialchars($row['image_url']); ?>" alt="Product Image" width="50"></td>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                                <td>
                                    <div class="button-row">
                                        <form method="post" action="edit_product.php">
                                            <input type="hidden" value="<?php echo $row['product_id']; ?>" name="product_id" />
                                            <input type="submit" value="Edit" name="edit" />
                                        </form>
                                        <form method="post" action="">
                                            <input type="hidden" value="<?php echo $row['product_id']; ?>" name="product_id" />
                                            <input type="submit" value="Delete" name="delete" 
                                            onclick="return confirm('Are you sure you want to delete this category?');"
                                            style="background-color: red; color: white; border: none; cursor: pointer;" 
                                            onmouseover="this.style.backgroundColor='darkred';" 
                                            onmouseout="this.style.backgroundColor='red';" />
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
