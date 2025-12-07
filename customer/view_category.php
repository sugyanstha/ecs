<?php
session_start();

// Database connection
include("../database/connection.php");
include("../customer/layout/cheader.php");

// Add to cart logic
if (isset($_POST['addtocart'])) {
    // User must be logged in to add to cart
    if (!isset($_SESSION['cid'])) {
        header("Location: login.php");
        exit();
    }

    $cid        = (int)$_SESSION['cid'];
    $product_id = (int)$_POST['product_id'];
    $quantity   = 1; // from category view we add 1 by default

    // 1) Check product stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ? LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($stock);
    if (!$stmt->fetch()) {
        $stmt->close();
        // Product not found, just redirect back
        header("Location: view_category.php");
        exit();
    }
    $stmt->close();

    if ($stock <= 0) {
        // No stock, redirect back (you can add message via session if you want)
        header("Location: view_category.php");
        exit();
    }

    // 2) Get or create cart for this customer
    $cart_id = null;

    $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE cid = ? ORDER BY cart_id DESC LIMIT 1");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $stmt->bind_result($cart_id_db);
    if ($stmt->fetch()) {
        $cart_id = (int)$cart_id_db;
    }
    $stmt->close();

    if ($cart_id === null) {
        // No cart yet -> create one
        $stmt = $conn->prepare("INSERT INTO cart (cid) VALUES (?)");
        $stmt->bind_param("i", $cid);
        $stmt->execute();
        $cart_id = $stmt->insert_id;
        $stmt->close();
    }

    // 3) Check if this product is already in cartitems
    $stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cartitems WHERE cart_id = ? AND product_id = ? LIMIT 1");
    $stmt->bind_param("ii", $cart_id, $product_id);
    $stmt->execute();
    $stmt->bind_result($cart_item_id_db, $existing_qty);
    $exists = $stmt->fetch();
    $stmt->close();

    if ($exists) {
        // Update quantity (but don't exceed stock)
        $new_qty = $existing_qty + $quantity;
        if ($new_qty > $stock) {
            $new_qty = $stock;
        }

        $stmt = $conn->prepare("UPDATE cartitems SET quantity = ? WHERE cart_item_id = ?");
        $stmt->bind_param("ii", $new_qty, $cart_item_id_db);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new cart item
        $stmt = $conn->prepare("INSERT INTO cartitems (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect back to same category (if set) to prevent form resubmission
    $redirect = "view_category.php";
    if (!empty($_GET['category_id'])) {
        $redirect .= "?category_id=" . intval($_GET['category_id']);
    }
    header("Location: $redirect");
    exit();
}

// Fetch all categories to display in the select dropdown
$sql_categories   = "SELECT * FROM categories";
$categories_result = $conn->query($sql_categories);

// Check if category ID is set in URL for filtering products
if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    
    // Fetch products for the selected category
    $sql_products = "SELECT * FROM products WHERE category_id = ?";
    $stmt = $conn->prepare($sql_products);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch all products if no category selected
    $sql_products = "SELECT * FROM products";
    $result = $conn->query($sql_products);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container my-5">
    <h1 class="mb-4 text-center">Products</h1>
    
    <!-- Category Select Dropdown -->
    <div class="category-section mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="get" action="" class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label for="category-select" class="form-label">Select Category</label>
                        <select id="category-select" name="category_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php 
                            if ($categories_result && $categories_result->num_rows > 0) {
                                while ($category = $categories_result->fetch_assoc()) { 
                                    $selected = (isset($_GET['category_id']) && $_GET['category_id'] == $category['category_id']) ? 'selected' : '';
                                    echo "<option value='{$category['category_id']}' {$selected}>{$category['name']}</option>";
                                }
                            } else {
                                echo "<option value=''>No categories available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="submit" class="btn btn-primary mt-4 w-100 w-md-auto">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Products Display Section -->
    <?php if ($result && $result->num_rows > 0) { ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($row = $result->fetch_assoc()) { 
                $pid        = (int)$row['product_id'];
                $name       = htmlspecialchars($row['name']);
                $desc       = htmlspecialchars($row['description']);
                $shortDesc  = mb_strimwidth($desc, 0, 80, '...');
                $price      = (float)$row['price'];
                $priceText  = number_format($price, 2);
                $stock      = (int)$row['stock'];
                $image_url  = htmlspecialchars($row['image_url']);
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="ratio ratio-4x3">
                        <img src="../img/<?php echo $image_url; ?>" 
                             class="card-img-top" 
                             alt="<?php echo $name; ?>" 
                             style="object-fit: cover;">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $name; ?></h5>
                        <p class="card-text text-muted mb-2"><?php echo $shortDesc; ?></p>

                        <p class="card-text mb-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary">Price: NRs. <?php echo $priceText; ?></span>
                            <?php if ($stock > 0): ?>
                                <span class="badge bg-success">In Stock: <?php echo $stock; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php endif; ?>
                        </p>

                        <div class="mt-auto">
                            <?php if ($stock > 0): ?>
                                <!-- First row: View Details + Add to Cart -->
                                <div class="d-flex gap-2 mb-2">
                                    <a href="product_details.php?id=<?php echo $pid; ?>" 
                                       class="btn btn-outline-info flex-fill">
                                        View Details
                                    </a>

                                    <form method="post" class="flex-fill">
                                        <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                        <button type="submit" name="addtocart" class="btn btn-primary w-100">
                                            Add to Cart
                                        </button>
                                    </form>
                                </div>

                                <!-- Second row: Place Order (Modal Trigger) -->
                                <button class="btn btn-warning w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#checkoutModal"
                                        onclick="setProductDetails(
                                            <?php echo $pid; ?>, 
                                            '<?php echo htmlspecialchars($name, ENT_QUOTES); ?>', 
                                            <?php echo $price; ?>, 
                                            <?php echo $stock; ?>
                                        )">
                                    Place Order
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No products found in this category.
                </div>
            </div>
        </div>
    <?php } ?>
</div>


<!-- Need to add shipping address and City in checkout form and need to change order table query-->

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

<script src="../js/checkout.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
