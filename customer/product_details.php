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

// Fetch reviews with customer names
$review_stmt = $conn->prepare("SELECT r.cid, r.comment, r.rating, c.name AS username 
                                FROM reviews r 
                                JOIN customer c ON r.cid = c.cid 
                                WHERE r.product_id = ?");
$review_stmt->bind_param("i", $product_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();
?>

<div class="container mt-5">
    <h1 class="mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
    <div class="row">
        <div class="col-md-6">
            <img src="../img/<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>" />
        </div>
        <div class="col-md-6">
            <h5>Description</h5>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <h5>Price: NRs. <?php echo htmlspecialchars($product['price']); ?></h5>
            <h5>In Stock: <?php echo htmlspecialchars($product['stock']); ?></h5>

            <!-- Buy Now Form -->
            <?php if ($product['stock'] > 0): ?>
                <!-- Place Order Button -->
                <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#checkoutModal" onclick="setProductDetails(<?php echo $product_id; ?>, '<?php echo $product['name']; ?>', <?php echo $product['price']; ?>, <?php echo $product['stock']; ?>)">Place Order</button>
            <?php else: ?>
                <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>

    <h2 class="mt-4">Customer Reviews</h2>
    <?php if ($review_result->num_rows > 0) {
        while ($review = $review_result->fetch_assoc()) {
            ?>
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

<!-- Modal for Checkout -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="checkoutModalLabel">Place Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="place_order.php" id="orderForm">
            <input type="hidden" name="product_id" id="product_id">
            <div class="mb-3">
                <label for="order_name" class="form-label">Product Name</label>
                <input type="text" id="order_name" class="form-control" disabled>
            </div>
            <div class="mb-3">
                <label for="order_price" class="form-label">Price</label>
                <input type="text" id="order_price" class="form-control" disabled>
            </div>
            <div class="mb-3">
                <label for="order_quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="order_quantity" class="form-control" min="1" required onchange="updateTotalPrice()">
            </div>
            <div class="mb-3">
                <label for="order_total_price" class="form-label">Total Price</label>
                <input type="text" id="order_total_price" class="form-control" disabled>
            </div>

            <!-- Shipping Address -->
            <div class="mb-3">
                <label for="shipping_address" class="form-label">Shipping Address</label>
                <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" required></textarea>
            </div>

            <!-- City -->
            <div class="mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" name="city" id="city" class="form-control" required>
            </div>

            <!-- Payment Method Selection -->
            <div class="mb-3">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select id="payment_method" name="payment_method" class="form-select" required onchange="togglePaymentFields()">
                    <option value="cod">Cash on Delivery</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="mobile_payment">Mobile Payment</option>
                </select>
            </div>

            <!-- Credit Card Details (show when Credit Card is selected) -->
            <div id="credit_card_details" class="payment-fields mb-3" style="display: none;">
                <label for="credit_card_number" class="form-label">Card Number</label>
                <input type="text" name="credit_card_number" class="form-control" id="credit_card_number" placeholder="Enter your card number">
                <label for="credit_card_expiry" class="form-label">Expiry Date</label>
                <input type="text" name="credit_card_expiry" class="form-control" id="credit_card_expiry" placeholder="MM/YY">
                <label for="credit_card_cvc" class="form-label">CVC</label>
                <input type="text" name="credit_card_cvc" class="form-control" id="credit_card_cvc" placeholder="Enter CVC">
            </div>

            <!-- Mobile Payment Details (show when Mobile Payment is selected) -->
            <div id="mobile_payment_details" class="payment-fields mb-3" style="display: none;">
                <label for="mobile_payment_number" class="form-label">Mobile Number</label>
                <input type="text" name="mobile_payment_number" class="form-control" id="mobile_payment_number" placeholder="Enter your mobile number">
            </div>

            <button type="submit" class="btn btn-primary w-100">Confirm Order</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/checkout.js"></script>