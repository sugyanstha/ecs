<?php
session_start();
include('database/connection.php'); //Database Connection
if (!isset($_SESSION['adminemail'])) {
    header("Location: admin_login.php");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $image_url = $_POST['image_url'];  // Handle image uploads as well

    $query = "INSERT INTO Products (name, description, price, stock, category_id, image_url) VALUES ('$name', '$description', '$price', '$stock', '$category_id', '$image_url')";
    mysqli_query($conn, $query);
    header("Location: admin_panel.php");
}
?>

<form method="post" action="product.php">
    <input type="text" name="name" placeholder="Product Name" required>
    <textarea name="description" placeholder="Description"></textarea>
    <input type="number" name="price" placeholder="Price" required>
    <input type="number" name="stock" placeholder="Stock" required>
    <select name="category_id">
        <!-- Loop through categories dynamically -->
        <?php
        $category_query = "SELECT * FROM Categories";
        $categories = mysqli_query($conn, $category_query);
        while ($category = mysqli_fetch_assoc($categories)) {
            echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
        }
        ?>
    </select>
    <input type="text" name="image_url" placeholder="Image URL">
    <button type="submit">Add Product</button>
</form>
