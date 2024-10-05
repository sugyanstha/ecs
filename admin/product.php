<?php
session_start();
include('../database/connection.php');

if (!isset($_SESSION['adminemail'])) {
    header("Location: admin_login.php");
}

// Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if all expected form fields are set
    if (isset($_POST['name'], $_POST['description'], $_POST['price'], $_POST['stock'], $_POST['category_id'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category_id = $_POST['category_id'];

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../img/"; // Directory where images will be stored
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validate file type (only allow certain image formats)
            $allowed_file_types = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($imageFileType, $allowed_file_types)) {
                // Check if file already exists, if yes rename
                if (file_exists($target_file)) {
                    $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]); // Add timestamp to avoid duplicate filenames
                }

                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // Prepare the query with the image path
                    $query = "INSERT INTO products (name, description, price, stock, category_id, image_url) 
                              VALUES ('$name', '$description', '$price', '$stock', '$category_id', '$target_file')";

                    if (mysqli_query($conn, $query)) {
                        header("Location: admin_panel.php");
                    } else {
                        die("Error inserting product: " . mysqli_error($conn));
                    }
                } else {
                    echo "Error uploading the image.";
                }
            } else {
                echo "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            echo "Image file is required.";
        }
    }
}
?>



<link rel="stylesheet" href="../css/form.css">
<form method="post" action="product.php" enctype="multipart/form-data" autocomplete="off">
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

        <label for="image_url">Product Image</label>
        <input type="file" name="image" accept="image/*" required>
        
        <button type="submit">Add Product</button>
        <button><a href="product_list.php">Back</a></button>
    </div>
</form>
