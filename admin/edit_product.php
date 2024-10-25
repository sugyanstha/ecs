<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<?php
// include('../admin/layout/adminheader.php');

// Check if the user is logged in; redirect to login page if not logged in
session_start();
if (!isset($_SESSION['adminemail'])) {
    header("location:adminlogin.php");
}
$adminname = $_SESSION['adminemail'];

// Database connection
include("../database/connection.php");

// Initialize variables for editing
$name = $description = $price = $stock = $category_id = $product_id = $image = "";

// Editing food item
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category_id'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name'];
        $target = "../img/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        // Fetch the existing image URL if no new image is uploaded
        $stmt = $conn->prepare("SELECT image_url FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $image_result = $stmt->get_result();
        if ($image_result->num_rows > 0) {
            $image_row = $image_result->fetch_assoc();
            $image = $image_row['image_url'];
        }
        // If no new image is uploaded, retain the old image
        //$image = ''; // You might want to fetch the current image from the database if needed
    }

    // Prepare SQL statement
    $sql = "UPDATE products SET name='$name', description='$description', price='$price', stock='$stock', category_id='$category'";

    // Only update the image if it's new
    if ($image) {
        $sql .= ", image_url='$image'";
    }

    $sql .= " WHERE product_id='$product_id'";
    
    if ($conn->query($sql)===TRUE ) {
        echo "<script>
        swal({
            title: 'Product Updated',
            text: 'Product updated successfully!',
            icon: 'success',
            confirmButtonText: 'Ok',
        }).then((function) => {
        
            window.location.href = 'product_list.php';
        
        });
    </script>";
    exit;
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'An error occured while updating the product',
                confirmButtonText: 'OK'
            });
        </script>";
        // exit;
    }
}

// View food details for editing
if (isset($_POST['edit'])) {
    $product_id = $_POST['product_id'];
    $sql = "SELECT * FROM products WHERE product_id='$product_id'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $description = $row['description'];
        $price = $row['price'];
        $stock = $row['stock'];
        $category_id = $row['category_id'];
        $product_id = $row['product_id'];
        $image = $row['image_url'];
    } else {
        die("No food item found with the given ID.");
    }
}
?>
        
        <!-- Product Edit Form -->
        <body>
        <link rel="stylesheet" href="../css/form.css">
        <form method="post" action="" enctype="multipart/form-data">
            <div class="container">
                <label for="productname">Food Name</label>
                <input type="text" name="name" placeholder="Enter Food Name" required value="<?php echo htmlspecialchars($name); ?>">

                <label for="productdescription">Food Description</label>
                <textarea name="description" placeholder="Enter Food Description" required><?php echo htmlspecialchars($description); ?></textarea>

                <label for="price">Price</label>
                <input type="number" name="price" step="0.01" placeholder="Price" required value="<?php echo htmlspecialchars($price); ?>">
                
                <label for="stock">Stock</label>
                <input type="number" name="stock" placeholder="Stock" required value="<?php echo htmlspecialchars($stock); ?>">

                <label for="category">Category</label>
                <select name="category_id" required>
                    <option value='choose' disabled>Select Category</option>
                    <?php
                    $category_query = "SELECT * FROM categories";
                    $categories = mysqli_query($conn, $category_query);
                    while ($category = mysqli_fetch_assoc($categories)) {
                        $selected = ($category['category_id'] == $category_id) ? 'selected' : '';
                        echo "<option value='{$category['category_id']}' $selected>{$category['name']}</option>";
                    }
                    ?>
                </select>

                <label for="image_url">Product Image</label>
                <input type="file" name="image" value="<?php //echo htmlspecialchars($image); ?>" accept="image/*">
                <img src="../img/<?php echo htmlspecialchars($image); ?>" width="100px" alt="Product Image">
                <!-- <p>Current Image: <img src="<?php //echo htmlspecialchars($product['image_url']); ?>" width="100px" alt="Current Product Image"></p> -->

                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>" />
                <button type="submit" name="edit_product">Update Product</button>
                <button type="button" onclick="window.location.href='product_list.php'">Back</button>
            </div>
        </form>