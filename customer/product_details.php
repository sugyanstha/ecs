<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ecs/customer/layout/cheader.php');
include('../database/connection.php');

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header('Location: view_products.php'); // Redirect if no ID
    exit();
}

$product_id = intval($_GET['id']);

// Fetch product details
$stmt = $conn->prepare("SELECT name, description, price, stock, image_url FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

// Check if product exists
if (!$product) {
    echo "<div class='container mt-5'><h2>Product not found.</h2></div>";
    exit();
}

// Fetch reviews
$review_stmt = $conn->prepare("SELECT cid, comment, rating FROM reviews WHERE product_id = ?");
$review_stmt->bind_param("i", $product_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();

?>

<div class="container mt-5">
    <h1 class="mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>" />
        </div>
        <div class="col-md-6">
            <h5>Description</h5>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <h5>Price: NRs. <?php echo htmlspecialchars($product['price']); ?></h5>
            <h5>In Stock: <?php echo htmlspecialchars($product['stock']); ?></h5>

            <!-- Buy Now Form -->
            <?php if ($product['stock'] > 0): ?>
            <form method="post" action="place_order.php">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="quantity" value="1"> <!-- Default quantity -->
                <button type="submit" class="btn btn-warning w-100">Buy Now</button>
            </form>
            <?php else: ?>
                <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>

    <h2 class="mt-4">Customer Reviews</h2>
    <?php if ($review_result->num_rows > 0) {
        while ($review = $review_result->fetch_assoc()) { ?>
            <div class="review mb-3">
                <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                <p><?php echo htmlspecialchars($review['comment']); ?></p>
                <small>Rating: <?php echo htmlspecialchars($review['rating']); ?> / 5</small>
            </div>
        <?php }
    } else { ?>
        <p>No reviews yet for this product.</p>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
