<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include($_SERVER['DOCUMENT_ROOT'] . '/ecs/customer/layout/cheader.php');
include('../database/connection.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];

// Add to cart functionality
if (isset($_POST['addtocart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Fetch product details to verify stock
    $stmt = $conn->prepare("SELECT name, description, price, stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product['stock'] >= $quantity) {
        // Sanitize inputs
        $name = $conn->real_escape_string($product['name']);
        $description = $conn->real_escape_string($product['description']);
        $price = $conn->real_escape_string($product['price']);

        // Insert into cart
        $sql = "INSERT INTO cart (product_id, name, description, price, quantity) VALUES ('$product_id', '$name', '$description', '$price', '$quantity')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Product added to cart successfully');</script>";
            header("Location: cart.php");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "<script>alert('Insufficient stock!');</script>";
    }
}

// Fetch all products
$sql = "SELECT product_id, name, description, price, stock, image_url FROM products";
$result = $conn->query($sql);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <h1 class="mb-4">Available Products</h1>
    <div class="row">
        <?php if ($result && $result->num_rows > 0) { 
            while ($row = $result->fetch_assoc()) { ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>" />
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="card-text"><strong>Price: NRs. <?php echo htmlspecialchars($row['price']); ?></strong></p>
                        <p class="card-text"> 
                            <?php if ($row['stock'] > 0): ?>
                                <span class="text-success">In Stock: <?php echo htmlspecialchars($row['stock']); ?></span>
                            <?php else: ?>
                                <span class="text-danger">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                        <?php if ($row['stock'] > 0): ?>
                            <!-- Add to Cart Form -->
                            <form method="post" action="cart.php" class="mb-3">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity:</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="<?php echo $row['stock']; ?>" required>
                                </div>
                                <button type="submit" name="addtocart" class="btn btn-primary w-100">Add to Cart</button>
                            </form>

                            <!-- Buy Now Form (Direct Order) -->
                            <form method="post" action="place_order.php">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="hidden" name="quantity" value="1" min="1" max="<?php echo $row['stock']; ?>" required> <!-- Default quantity -->
                                <button type="submit" class="btn btn-warning w-100">Buy Now</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php } 
        } else { ?>
            <div class="col-12">
                <p>No products available.</p>
            </div>
        <?php } ?>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<?php //include('customer/layout/cfooter.php'); ?>
