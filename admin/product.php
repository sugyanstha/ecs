<?php
session_start();
include('../database/connection.php');

if (!isset($_SESSION['adminemail'])) {
    header("Location: admin_login.php");
}

// Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if all expected form fields are set
    if (isset($_POST['name'], $_POST['description'], $_POST['price'], $_POST['stock'], $_POST['category_id'], $_POST['image_url'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category_id = $_POST['category_id'];
        $image_url = $_POST['image_url'];  // Handle image uploads as well

        // Prepare the query
        $query = "INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES ('$name', '$description', '$price', '$stock', '$category_id', '$image_url')";

        if (mysqli_query($conn, $query)) {
            header("Location: admin_panel.php");
        } else {
            die("Error inserting product: " . mysqli_error($conn));
        }
    // } else {
    //     echo "All fields are required.";
     }
}
?>


<link rel="stylesheet" href="../css/form.css">
<form method="post" action="product.php" autocomplete="off">
    <div class="container">
        <label for="productname">Product Name</label>
        <input type="text" name="name" placeholder="Enter Product Name" required>
        <label for="productdescription">Product Description</label>
        <textarea name="description" placeholder="Enter Product Description"></textarea>
        <label for="price">Price</label>
        <input type="number" name="price" placeholder="Price" required>
        <label for="stock">Stock</label>
        <input type="number" name="stock" placeholder="Stock" required>
        <label for="category">Category</label>
        <select name="category_id" required>
            <!-- Loop through categories dynamically -->
            <?php
            $category_query = "SELECT * FROM Categories";
            $categories = mysqli_query($conn, $category_query);
            while ($category = mysqli_fetch_assoc($categories)) {
                echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
            }
            ?>
        </select>
        <label for="image_url">Image URL</label>
        <input type="text" name="image_url" placeholder="Image URL">
        <button type="submit">Add Product</button>
        <button><a href="product_list.php">Back</a></button>
    </div>
</form>
