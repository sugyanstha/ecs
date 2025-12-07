<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// correct paths
include __DIR__ . '/layout/cheader.php';
include __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/cart_functions.php';

if (!isset($_SESSION['cid'])) {
    header("Location: login.php");
    exit();
}

$cid = (int) $_SESSION['cid']; // logged in customer id

// Get cart items and totals
$items  = get_cart_items($cid);
$totals = get_cart_totals($cid);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>

    <!-- Bootstrap CSS (if not already included in cheader.php, this is safe) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <h1 class="mb-4 text-center">Your Shopping Cart</h1>

    <?php if (!$items || mysqli_num_rows($items) == 0): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 class="card-title mb-3">Your cart is empty 🛒</h4>
                <p class="card-text mb-4">Looks like you haven't added any products yet.</p>
                <a href="view_product.php" class="btn btn-primary">
                    Browse Products
                </a>
            </div>
        </div>
    <?php else: ?>

        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Items in your cart</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Price (NRs)</th>
                                        <th class="text-end">Total (NRs)</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = mysqli_fetch_assoc($items)): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($row['name']); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?= (int)$row['quantity']; ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($row['price'], 2); ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($row['line_total'], 2); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="remove_item.php?id=<?= (int)$row['cart_item_id']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Remove this item from cart?');">
                                                Remove
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="view_product.php" class="btn btn-outline-secondary">
                            ← Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <p class="d-flex justify-content-between">
                            <span>Items:</span>
                            <span><strong><?= $totals['item_count']; ?></strong></span>
                        </p>
                        <p class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span>NRs. <?= number_format($totals['subtotal'], 2); ?></span>
                        </p>
                        <p class="d-flex justify-content-between">
                            <span>Tax (7%):</span>
                            <span>NRs. <?= number_format($totals['tax'], 2); ?></span>
                        </p>
                        <p class="d-flex justify-content-between">
                            <span>Shipping:</span>
                            <span>NRs. <?= number_format($totals['shipping'], 2); ?></span>
                        </p>
                        <hr>
                        <p class="d-flex justify-content-between fs-5">
                            <span><strong>Total:</strong></span>
                            <span><strong>NRs. <?= number_format($totals['total'], 2); ?></strong></span>
                        </p>

                        <button class="btn btn-success w-100 mt-3"
                                onclick="window.location.href='checkout.php'">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- Bootstrap JS (if not already included in cheader.php, this is safe) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
