<?php
session_start();
include('../database/connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['adminemail'])) {
    header("Location: admin_login.php");
    exit();
}

// Initialize variables
$product = [];

// Check if the form was submitted (for updating the product)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    
    // Handle image upload if a new image is uploaded
    $image_query = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../img/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check for valid image types
        $allowed_file_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_file_types)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_query = ", image_url = '$target_file'";
            } else {
                die("Error uploading the image.");
            }
        } else {
            die("Only JPG, JPEG, PNG & GIF files are allowed.");
        }
    }

    // Update the product in the database
    $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ? $image_query WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdiii", $name, $description, $price, $stock, $category_id, $product_id);
    
    if ($stmt->execute()) {
        header("Location: product_list.php");
        exit();
    } else {
        die("Error updating product: " . $stmt->error);
    }

    $stmt->close();
} else {
    // Fetch product details for the given product ID
    if (isset($_GET['product_id'])) {
        $product_id = intval($_GET['product_id']);
        
        // Fetch product details
        $query = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
        } else {
            die("Product not found.");
        }
    } else {
        die("Product ID is missing.");
    }
}

// Set default values to avoid undefined array keys
$name = isset($product['name']) ? htmlspecialchars($product['name']) : '';
$description = isset($product['description']) ? htmlspecialchars($product['description']) : '';
$price = isset($product['price']) ? htmlspecialchars($product['price']) : '';
$stock = isset($product['stock']) ? htmlspecialchars($product['stock']) : '';
$category_id = isset($product['category_id']) ? htmlspecialchars($product['category_id']) : '';
?>

<link rel="stylesheet" href="../css/form.css">

<form method="POST" action="edit_product.php" enctype="multipart/form-data">
    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
    
    <div class="container">
        <label for="name">Product Name</label>
        <input type="text" name="name" value="<?php echo $name; ?>" required>

        <label for="description">Product Description</label>
        <textarea name="description" required><?php echo $description; ?></textarea>

        <label for="price">Price</label>
        <input type="number" step="0.01" name="price" value="<?php echo $price; ?>" required>

        <label for="stock">Stock</label>
        <input type="number" name="stock" value="<?php echo $stock; ?>" required>

        <label for="category">Category</label>
        <select name="category_id" required>
            <?php
            $category_query = "SELECT * FROM Categories";
            $categories = mysqli_query($conn, $category_query);
            while ($category = mysqli_fetch_assoc($categories)) {
                $selected = ($category_id == $category['category_id']) ? 'selected' : '';
                echo "<option value='{$category['category_id']}' $selected>{$category['name']}</option>";
            }
            ?>
        </select>

        <label for="image">Product Image</label>
        <input type="file" name="image" accept="image/*">
        <p>Current Image: <img src="<?php echo htmlspecialchars($product['image_url']); ?>" width="100px" alt="Current Product Image"></p>

        <input type="submit" value="Update Product" name="edit"/>
        <button type="button" onclick="window.location.href='product_list.php'">Back</button>
    </div>
</form>
