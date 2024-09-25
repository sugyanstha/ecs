<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();  // Start the session

if (!isset($_SESSION['adminemail'])) {
    // If admin is not logged in, redirect to the login page
    header("Location: admin_login.php");
    exit();
}

// Database Connection
include('../database/connection.php');

// Fetch orders using JOIN
// $order_query = "SELECT o.order_id, o.cid AS customer_id, o.total_price, o.status, o.created_at, 
//                 oi.order_item_id, oi.product_id, p.name AS product_name, oi.quantity, oi.price
//                 FROM `Order` o 
//                 JOIN OrderItems oi ON o.order_id = oi.order_id
//                 JOIN Products p ON oi.product_id = p.product_id
//                 ORDER BY o.order_id";

$order_query = "SELECT o.order_id, o.cid AS customer_id, o.total_price, o.status, o.created_at, 
                oi.order_item_id, oi.product_id, p.name AS product_name, oi.quantity, oi.price
                FROM orders o 
                JOIN orderitems oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                ORDER BY o.order_id";
$orders = mysqli_query($conn, $order_query);

if (!$orders) {
    // Handle query error
    die("Query Failed: " . mysqli_error($conn));
}

include('../admin/layout/adminheader.php');
?>


<link rel="stylesheet" href="../css/adminpanel.css">
<div class="main-content">
    <h2 align="center">Orders List</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Order Item ID</th>
                <th>Created At</th>
                <th>Customer ID</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($order = mysqli_fetch_assoc($orders)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                <td><?php echo htmlspecialchars($order['order_item_id']); ?></td>
                <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_id']); ?></td>
                <td><?php echo htmlspecialchars($order['product_id']); ?></td>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                <td><?php echo htmlspecialchars($order['total_price']); ?></td>
                <td><?php echo htmlspecialchars($order['status']); ?></td>
                <td>
                    <form action="update_order_status.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                        <select name="status" onchange="this.form.submit()" class="form-select">
                            <option value="pending" <?php if ($order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                            <option value="completed" <?php if ($order['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                        </select>
                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>


<!-- 
<h2>Product Management</h2>
<form action="product.php" method="post">
                <input type="submit" value="Add Product" name="add">
            </form>
<table>
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Actions</th>
    </tr> -->
    <?php //while ($product = mysqli_fetch_assoc($products)) { ?>
        <!-- <tr>
                <td><?php //echo $product['name']; ?></td> -->
            <!-- <td><?php //echo $product['description']; ?></td> -->
            <!-- <td><?php //echo $product['price']; ?></td> -->
            <!-- <td><?php //echo $product['stock']; ?></td> -->
            <!-- <td> -->
                <!-- <a href="edit_product.php?id=<?php //echo $product['product_id']; ?>">Edit</a> | -->
                <!-- <a href="delete_product.php?id=<?php //echo $product['product_id']; ?>">Delete</a> -->
            <!-- </td>
        </tr> --> 
    <?php //} ?>
<!-- </table> -->



<!-- <h2>Category Management</h2>
<ul> -->
    <!-- <?php //while ($category = mysqli_fetch_assoc($categories)) { ?> -->
        <!-- <li><?php //echo $category['name']; ?>  -->
            <!-- <a href="delete_category.php?id=<?php //echo $category['category_id']; ?>">Delete</a> -->
        <!-- </li> -->
    <!-- <?php //} ?> -->
<!-- </ul>

</div> -->



        <!-- Main Content -->
        <!-- <div class="main-content">
            Products Section 
            <section id="products">
                <h3>Manage Products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>T-Shirt</td>
                            <td>$25</td>
                            <td>100</td>
                            <td><button>Edit</button> <button>Delete</button></td>
                        </tr>
                        Repeat for more products 
                    </tbody>
                </table>
            </section>

            Categories Section
            <section id="categories">
                <h3>Manage Categories</h3>
                <form>
                    <input type="text" placeholder="New Category Name">
                    <button>Add Category</button>
                </form>
                <ul>
                    <li>T-Shirts <button>Delete</button></li>
                    <li>Jeans <button>Delete</button></li>
                    Repeat for more categories
                </ul>
            </section>

             Orders Section 
            <section id="orders">
                <h3>Customer Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#001</td>
                            <td>John Doe</td>
                            <td>Pending</td>
                            <td>$50</td>
                        </tr>
                        Repeat for more orders 
                    </tbody>
                </table>
            </section>

            Customers Section
            <section id="customers">
                <h3>Manage Customers</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Order History</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Doe</td>
                            <td>john@example.com</td>
                            <td>3 Orders</td>
                        </tr>
                        Repeat for more customers
                    </tbody>
                </table>
            </section>
        </div>
    </div> -->
</body>
</html>
