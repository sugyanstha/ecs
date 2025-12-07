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

// Get logged-in customer ID (cid)
if (isset($_SESSION['cid'])) {
    $cid = (int)$_SESSION['cid'];
} else {
    $cid = null;
    $stmt = $conn->prepare("SELECT cid FROM customer WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($cid_db);
    if ($stmt->fetch()) {
        $cid = (int)$cid_db;
        $_SESSION['cid'] = $cid;
    }
    $stmt->close();

    if ($cid === null) {
        header('Location: login.php');
        exit();
    }
}

// Add to cart part  
if (isset($_POST['addtocart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity   = intval($_POST['quantity']);
    if ($quantity <= 0) {
        $quantity = 1;
    }

    // Get product and stock
    $stmt = $conn->prepare("SELECT name, description, price, stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result  = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product && $product['stock'] >= $quantity) {

        // 1) Get or create cart row for this customer
        $cart_id = null;

        $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE cid = ? ORDER BY cart_id DESC LIMIT 1");
        $stmt->bind_param("i", $cid);
        $stmt->execute();
        $stmt->bind_result($existing_cart_id);
        if ($stmt->fetch()) {
            $cart_id = (int)$existing_cart_id;
        }
        $stmt->close();

        if ($cart_id === null) {
            // Create new cart (created_at has default)
            $stmt = $conn->prepare("INSERT INTO cart (cid) VALUES (?)");
            $stmt->bind_param("i", $cid);
            if ($stmt->execute()) {
                $cart_id = $stmt->insert_id;
            }
            $stmt->close();

            if ($cart_id === null) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error creating cart!'];
            }
        }

        if ($cart_id !== null) {
            // 2) Check if this product already in cartitems
            $stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cartitems WHERE cart_id = ? AND product_id = ? LIMIT 1");
            $stmt->bind_param("ii", $cart_id, $product_id);
            $stmt->execute();
            $stmt->bind_result($cart_item_id, $existing_qty);
            $has_item = $stmt->fetch();
            $stmt->close();

            if ($has_item) {
                // Update quantity
                $new_qty = $existing_qty + $quantity;
                if ($new_qty > $product['stock']) {
                    $new_qty = $product['stock'];
                }
                $stmt = $conn->prepare("UPDATE cartitems SET quantity = ? WHERE cart_item_id = ?");
                $stmt->bind_param("ii", $new_qty, $cart_item_id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Cart updated successfully'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error updating cart item!'];
                }
                $stmt->close();
            } else {
                // Insert new cart item
                $stmt = $conn->prepare("INSERT INTO cartitems (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
                if ($stmt->execute()) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Product added to cart successfully'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error adding product to cart!'];
                }
                $stmt->close();
            }
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Insufficient stock!'];
    }

    // Redirect back to this page so form resubmission doesn't happen on refresh
    header("Location: view_product.php");
    exit();
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
    unset($_SESSION['message']);
}
//Add to cart code end here


// Fetch all products (Displaying Product list in card view)
$sql    = "SELECT product_id, name, description, price, stock, image_url FROM products";
$result = $conn->query($sql);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container my-5">
    <h1 class="mb-4 text-center">All Products</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $pid         = (int)$row['product_id'];
                    $name        = htmlspecialchars($row['name']);
                    $name_js     = htmlspecialchars($row['name'], ENT_QUOTES);
                    $desc        = htmlspecialchars($row['description']);
                    $short_desc  = mb_strimwidth($desc, 0, 80, '...');
                    $price       = (float)$row['price'];
                    $stock       = (int)$row['stock'];
                    $image_url   = htmlspecialchars($row['image_url']);
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm product-card">
                        <div class="ratio ratio-4x3">
                            <img src="../img/<?php echo $image_url ?: 'placeholder.png'; ?>"
                                 class="card-img-top"
                                 alt="<?php echo $name; ?>"
                                 style="object-fit: cover;">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $name; ?></h5>
                            <p class="card-text text-muted mb-2">
                                <?php echo $short_desc; ?>
                            </p>

                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">NRs. <?php echo number_format($price, 2); ?></span>
                                <?php if ($stock > 0): ?>
                                    <span class="badge bg-success">In stock: <?php echo $stock; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Out of stock</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($stock > 0): ?>
                                <!-- First row: View Details + Add to Cart -->
                                <div class="d-flex gap-2 mt-2">
                                    <a href="product_details.php?id=<?php echo $pid; ?>"
                                       class="btn btn-outline-info flex-fill">
                                        View Details
                                    </a>

                                    <form method="post" class="flex-fill">
                                        <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="addtocart"
                                                class="btn btn-primary w-100">
                                            Add to Cart
                                        </button>
                                    </form>
                                </div>

                                <!-- Second row: Place Order -->
                                <button class="btn btn-warning w-100 mt-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#checkoutModal"
                                        onclick="setProductDetails(
                                            <?php echo $pid; ?>,
                                            '<?php echo $name_js; ?>',
                                            <?php echo $price; ?>,
                                            <?php echo $stock; ?>
                                        )">
                                    Place Order
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 mt-3" disabled>
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            No products available.
        </div>
    <?php endif; ?>
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

            <!-- Credit Card Details -->
            <div id="credit_card_details" class="payment-fields mb-3" style="display: none;">
                <label for="credit_card_number" class="form-label">Card Number</label>
                <input type="text" name="credit_card_number" class="form-control" id="credit_card_number" placeholder="Enter your card number">
                <label for="credit_card_expiry" class="form-label mt-2">Expiry Date</label>
                <input type="text" name="credit_card_expiry" class="form-control" id="credit_card_expiry" placeholder="MM/YY">
                <label for="credit_card_cvc" class="form-label mt-2">CVC</label>
                <input type="text" name="credit_card_cvc" class="form-control" id="credit_card_cvc" placeholder="Enter CVC">
            </div>

            <!-- Mobile Payment Details -->
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
