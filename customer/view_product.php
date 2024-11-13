<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

// Add to cart part  
if (isset($_POST['addtocart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $stmt = $conn->prepare("SELECT name, description, price, stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product && $product['stock'] >= $quantity) {
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO cart (product_id, name, description, price, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $product_id, $product['name'], $product['description'], $product['price'], $quantity);
        
        if ($stmt->execute()) {
            // Set session message for success
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Product added to cart successfully'];
            header("Location: cart.php");
            exit();
        } else {
            // Set session message for error
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error adding product to cart!'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Insufficient stock!'];
    }
}

// Display success or error message using SweetAlert
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    echo "<script>
            Swal.fire({
                title: '" . ($message['type'] == 'success' ? 'Success!' : 'Error!') . "',
                text: \"" . htmlspecialchars($message['text']) . "\",
                icon: '{$message['type']}'
            });
          </script>";
    // Clear the message after displaying it
    unset($_SESSION['message']);
}
//Add to cart code end here


// Fetch all products (Displaying Product list in card view)
$sql = "SELECT product_id, name, description, price, stock, image_url FROM products";
$result = $conn->query($sql);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <h1 class="mb-4">All Products</h1>
    <div id="productGrid" class="row">
        <?php if ($result && $result->num_rows > 0) { 
            while ($row = $result->fetch_assoc()) { ?>
            <div class="col-md-4 product-card">
                <div class="card mb-4">
                    <img src="../img/<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>" />
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
                            <!-- View Product Details -->
                            <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn btn-info w-100">View Details</a>
                            <br>
                            <br>
                            
                            <!-- Buy Now (Place Order Modal Trigger) -->
                            <button class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#checkoutModal" onclick="setProductDetails(<?php echo $row['product_id']; ?>, '<?php echo $row['name']; ?>', <?php echo $row['price']; ?>, <?php echo $row['stock']; ?>)">Place Order</button>

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

<?php // include('customer/layout/cfooter.php'); ?>
