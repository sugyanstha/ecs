<?php
// Start session for login
session_start();

// Database connection details
include('../database/connection.php');

// Check if the user is logged in
if (!isset($_SESSION['adminemail'])) {
    echo "You need to login first.";
    exit();
}

// Function to list all categories
// function listCategories($mysqli) {
//     $stmt = $mysqli->prepare("SELECT * FROM Categories");
//     $stmt->execute();
//     $result = $stmt->get_result();
//     return $result->fetch_all(MYSQLI_ASSOC);
// }

// Function to create a new category
function createCategory($mysqli, $name) {
    $stmt = $mysqli->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param('s', $name);
    return $stmt->execute();
}

// Function to update an existing category
// function updateCategory($mysqli, $id, $name) {
//     $stmt = $mysqli->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
//     $stmt->bind_param('si', $name, $id);
//     return $stmt->execute();
// }

// Function to delete a category
// function deleteCategory($mysqli, $id) {
//     $stmt = $mysqli->prepare("DELETE FROM categories WHERE category_id = ?");
//     $stmt->bind_param('i', $id);
//     return $stmt->execute();
// }

// Function to list all products
// function listProducts($mysqli) {
//     $stmt = $mysqli->prepare("
//         SELECT products.*, categories.name AS category_name 
//         FROM products 
//         LEFT JOIN categories ON products.category_id = categories.category_id
//     ");
//     $stmt->execute();
//     $result = $stmt->get_result();
//     return $result->fetch_all(MYSQLI_ASSOC);
// }

// Function to create a new product
// function createProduct($mysqli, $name, $description, $price, $stock, $category_id, $image_url) {
//     $stmt = $mysqli->prepare("
//         INSERT INTO products (name, description, price, stock, category_id, image_url) 
//         VALUES (?, ?, ?, ?, ?, ?)
//     ");
//     $stmt->bind_param('ssdiis', $name, $description, $price, $stock, $category_id, $image_url);
//     return $stmt->execute();
// }

// Function to update an existing product
// function updateProduct($mysqli, $id, $name, $description, $price, $stock, $category_id, $image_url) {
//     $stmt = $mysqli->prepare("
//         UPDATE products 
//         SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image_url = ? 
//         WHERE product_id = ?
//     ");
//     $stmt->bind_param('ssdiisi', $name, $description, $price, $stock, $category_id, $image_url, $id);
//     return $stmt->execute();
// }

// Function to delete a product
// function deleteProduct($mysqli, $id) {
//     $stmt = $mysqli->prepare("DELETE FROM Products WHERE product_id = ?");
//     $stmt->bind_param('i', $id);
//     return $stmt->execute();
// }

// Main logic to handle CRUD operations using $_POST parameters
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission for Category or Product creation/update/delete
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_category':
                if (!empty($_POST['name'])) {
                    createCategory($mysqli, $_POST['name']);
                    echo "Category created successfully!";
                }
                break;

            // case 'update_category':
            //     if (!empty($_POST['category_id']) && !empty($_POST['name'])) {
            //         updateCategory($mysqli, $_POST['category_id'], $_POST['name']);
            //         echo "Category updated successfully!";
            //     }
            //     break;

            // case 'delete_category':
            //     if (!empty($_POST['category_id'])) {
            //         deleteCategory($mysqli, $_POST['category_id']);
            //         echo "Category deleted successfully!";
            //     }
            //     break;

            // case 'create_product':
            //     if (!empty($_POST['name']) && !empty($_POST['price']) && isset($_POST['stock'])) {
            //         createProduct(
            //             $mysqli, $_POST['name'], $_POST['description'], $_POST['price'], 
            //             $_POST['stock'], $_POST['category_id'], $_POST['image_url']
            //         );
            //         echo "Product created successfully!";
            //     }
            //     break;

            // case 'update_product':
            //     if (!empty($_POST['product_id']) && !empty($_POST['name']) && !empty($_POST['price']) && isset($_POST['stock'])) {
            //         updateProduct(
            //             $mysqli, $_POST['product_id'], $_POST['name'], $_POST['description'], 
            //             $_POST['price'], $_POST['stock'], $_POST['category_id'], $_POST['image_url']
            //         );
            //         echo "Product updated successfully!";
            //     }
            //     break;

            // case 'delete_product':
            //     if (!empty($_POST['product_id'])) {
            //         deleteProduct($mysqli, $_POST['product_id']);
            //         echo "Product deleted successfully!";
            //     }
            //     break;
        }
    }
}
?>

<!-- HTML Part to display form for CRUD operations -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Operations</title>
</head>
<body>
    <h1>Manage Categories</h1>

    <form action="" method="POST">
        <input type="hidden" name="action" value="create_category">
        <label for="name">Category Name:</label>
        <input type="text" name="name" required>
        <button type="submit">Create Category</button>
    </form>

    <h2>Existing Categories</h2>
    <ul>
        <?php foreach (listCategories($mysqli) as $category): ?>
            <li><?= $category['name'] ?>
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h1>Manage Products</h1>

    <form action="" method="POST">
        <input type="hidden" name="action" value="create_product">
        <label for="name">Product Name:</label>
        <input type="text" name="name" required>
        <label for="description">Description:</label>
        <textarea name="description"></textarea>
        <label for="price">Price:</label>
        <input type="number" step="0.01" name="price" required>
        <label for="stock">Stock:</label>
        <input type="number" name="stock" required>
        <label for="category_id">Category:</label>
        <select name="category_id">
            <?php foreach (listCategories($mysqli) as $category): ?>
                <option value="<?= $category['category_id'] ?>"><?= $category['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <label for="image_url">Image URL:</label>
        <input type="text" name="image_url">
        <button type="submit">Create Product</button>
    </form>

    <h2>Existing Products</h2>
    <ul>
        <?php foreach (listProducts($mysqli) as $product): ?>
            <li><?= $product['name'] ?> (Category: <?= $product['category_name'] ?>)
                <form action="" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

</body>
</html>
