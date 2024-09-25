<?php
session_start();
include('database/connection.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];

// Handle the form submission (check stock)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']); // Get the selected product ID

    // Query to get the stock of the selected product
    $stmt = $conn->prepare("SELECT name, stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the product exists
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $product_name = htmlspecialchars($product['name']);
        $stock = $product['stock'];
    } else {
        $error = "Product not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Check Stock</title>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Check Stock</h2>
        <p>Welcome, <?= htmlspecialchars($_SESSION['email']); ?></p>
        <a href="logout.php" class="btn btn-danger">Logout</a>

        <form method="POST" action="stock.php" class="mt-4">
            <div class="mb-3">
                <label for="product_id" class="form-label">Select Product</label>
                <select name="product_id" id="product_id" class="form-select" required>
                    <option value="" disabled selected>Choose a product</option>
                    <?php
                    // Fetch all products to populate the dropdown list
                    $stmt = $conn->prepare("SELECT product_id, name FROM products");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($product = $result->fetch_assoc()) {
                        echo "<option value='" . $product['product_id'] . "'>" . htmlspecialchars($product['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Check Stock</button>
        </form>

        <?php if (isset($stock)): ?>
            <div class="mt-4">
                <h4>Stock Information:</h4>
                <p>Product: <?= $product_name ?></p>
                <p>Available Stock: <?= $stock ?></p>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger mt-4"><?= $error ?></div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
