<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

include '../database/connection.php'; // Database connection

// Fetch all products
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Dashboard</title>
</head>
<body>
    <div class="container">
    <a href="cart.php">View Cart</a> | <a href="orders.php">View Orders</a> | <a href="logout.php">Logout</a>

        <h1>Welcome, <?= htmlspecialchars($_SESSION['email']); ?></h1>

        <!-- List Products -->
        <h2>Available Products</h2>
        <div class="products">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?= htmlspecialchars($product['image_url']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                    <h3><?= htmlspecialchars($product['name']); ?></h3>
                    <p><?= htmlspecialchars($product['description']); ?></p>
                    <p>Price: $<?= htmlspecialchars($product['price']); ?></p>
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']); ?>">
                        <label>Quantity:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?= htmlspecialchars($product['stock']); ?>">
                        <button type="submit">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- <a href="cart.php">View Cart</a> | <a href="orders.php">View Orders</a> | <a href="logout.php">Logout</a> -->
    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>


































<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

//Include customer header and database connection
// include('customer/c_layout/cheader.php');
// include('customer/c_layout/sidebar.php');
// include('database/connection.php');

?>

<?php
// session_start();
// if (!isset($_SESSION['email'])) {
//     header('Location: login.php');
//     exit();
// }

// include 'database/connection.php'; // Database connection

// Fetch all products
// $stmt = $mysqli->prepare("SELECT * FROM products");
// $stmt->execute();
// $products = $stmt->get_result();
// ?>

<!-- <h1>Welcome, <?=// $_SESSION['name']; ?></h1> -->

<!-- List Products -->
<!-- <h2>Available Products</h2>
<div class="products"> -->
    <?php //while ($product = $products->fetch_assoc()): ?>
        <!-- <div class="product">
            <img src="<?= //$product['image_url']; ?>" alt="<?= //$product['name']; ?>">
            <h3><?= //$product['name']; ?></h3>
            <p><?= //$product['description']; ?></p>
            <p>Price: $<?= //$product['price']; ?></p>
            <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?= //$product['product_id']; ?>">
                <label>Quantity:</label>
                <input type="number" name="quantity" value="1" min="1" max="<?= //$product['stock']; ?>">
                <button type="submit">Add to Cart</button>
            </form>
        </div> -->
    <?php //endwhile; ?>
<!-- </div> -->

<!-- <a href="cart.php">View Cart</a> | <a href="orders.php">View Orders</a> | <a href="logout.php">Logout</a> -->
