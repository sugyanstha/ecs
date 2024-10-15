<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
</head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
</head>
<body>

<?php
session_start();
include('../database/connection.php');

if (!isset($_SESSION['adminemail'])) {
    header("Location: admin_login.php");
    exit;
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
            $target_dir = "../img/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_file_types = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($imageFileType, $allowed_file_types)) {
                if (file_exists($target_file)) {
                    $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
                }

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $query = "INSERT INTO products (name, description, price, stock, category_id, image_url) 
                              VALUES ('$name', '$description', '$price', '$stock', '$category_id', '$target_file')";

                    if (mysqli_query($conn, $query)) {
                        // Set success message
                        $_SESSION['message'] = "Product added successfully!";
                        $_SESSION['msg_type'] = "success"; // or any other type you want to define
                        // header("Location: view_product.php");
                        // exit;
                    } else {
                        // Set error message
                        $_SESSION['message'] = "Error inserting product: " . mysqli_error($conn);
                        $_SESSION['msg_type'] = "error";
                    }
                } else {
                    $_SESSION['message'] = "Error uploading the image.";
                    $_SESSION['msg_type'] = "error";
                }
            } else {
                $_SESSION['message'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
                $_SESSION['msg_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Image file is required.";
            $_SESSION['msg_type'] = "error";
        }
    }
}
?>

    <?php if (isset($_SESSION['message'])): ?>
        <script>
            swal({
                title: "<?php echo $_SESSION['msg_type'] == 'success' ? 'Success' : 'Error'; ?>",
                text: "<?php echo $_SESSION['message']; ?>",
                icon: "<?php echo $_SESSION['msg_type'] == 'success' ? 'success' : 'error'; ?>",
                button: "OK",
            }).then(() => {
                // Redirect if success
                <?php if ($_SESSION['msg_type'] == 'success'): ?>
                    window.location.href = "product_list.php"; // Redirect to the product list page
                <?php endif; ?>
            });
        </script>
        <?php unset($_SESSION['message']); ?>
        <?php unset($_SESSION['msg_type']); ?>
    <?php endif; ?>


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
</body>
</html>