<?php
// cart_functions.php

// Use the same DB connection as the rest of your project
require_once __DIR__ . '/../database/connection.php';

/**
 * Get or create active cart for a customer (cid).
 *
 * @param int $cid
 * @return int cart_id
 */
function get_cart_id(int $cid): int
{
    global $conn;

    // Find latest cart for this customer
    $sql = "SELECT cart_id 
            FROM cart 
            WHERE cid = ? 
            ORDER BY cart_id DESC 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $stmt->bind_result($cart_id);

    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$cart_id;
    }
    $stmt->close();

    // Create a new cart if none exists
    $sql = "INSERT INTO cart (cid) VALUES (?)"; // created_at has default
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $new_cart_id = $stmt->insert_id;
    $stmt->close();

    return (int)$new_cart_id;
}

/**
 * Add product to cart (or update quantity if already there).
 *
 * @param int $cid
 * @param int $product_id
 * @param int $quantity
 * @return bool success
 */
function add_to_cart(int $cid, int $product_id, int $quantity): bool
{
    global $conn;

    if ($quantity <= 0) {
        $quantity = 1;
    }

    // Check product & stock
    $sql = "SELECT stock 
            FROM products 
            WHERE product_id = ? 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($stock);
    if (!$stmt->fetch()) {
        $stmt->close();
        return false; // product not found
    }
    $stmt->close();

    if ($stock < $quantity) {
        return false; // not enough stock
    }

    // Get or create cart
    $cart_id = get_cart_id($cid);

    // Check if this product is already in the cart
    $sql = "SELECT cart_item_id, quantity 
            FROM cartitems 
            WHERE cart_id = ? AND product_id = ? 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $product_id);
    $stmt->execute();
    $stmt->bind_result($cart_item_id, $existing_qty);
    $has_item = $stmt->fetch();
    $stmt->close();

    if ($has_item) {
        // Update quantity
        $new_qty = $existing_qty + $quantity;

        // Optional: prevent exceeding stock
        if ($new_qty > $stock) {
            $new_qty = $stock;
        }

        $sql = "UPDATE cartitems 
                SET quantity = ? 
                WHERE cart_item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_qty, $cart_item_id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    } else {
        // Insert new cart item
        $sql = "INSERT INTO cartitems (cart_id, product_id, quantity) 
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}

/**
 * Get all cart items for this customer.
 * Returns mysqli_result (use mysqli_fetch_assoc in cart.php).
 *
 * @param int $cid
 * @return mysqli_result|false
 */
function get_cart_items(int $cid)
{
    global $conn;

    $cart_id = get_cart_id($cid);

    $sql = "SELECT 
                ci.cart_item_id,
                ci.product_id,
                ci.quantity,
                p.name,
                p.price,
                (ci.quantity * p.price) AS line_total
            FROM cartitems ci
            JOIN products p ON p.product_id = ci.product_id
            WHERE ci.cart_id = ?
            ORDER BY ci.cart_item_id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    // Do not close $stmt yet if you still need $result fully. But it's ok:
    $stmt->close();

    return $result;
}

/**
 * Get cart totals (subtotal, tax, shipping, total, item_count).
 *
 * @param int $cid
 * @return array
 */
function get_cart_totals(int $cid): array
{
    global $conn;

    $cart_id = get_cart_id($cid);

    $sql = "SELECT 
                COALESCE(SUM(ci.quantity * p.price), 0) AS subtotal,
                COALESCE(SUM(ci.quantity), 0) AS item_count
            FROM cartitems ci
            JOIN products p ON p.product_id = ci.product_id
            WHERE ci.cart_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $stmt->bind_result($subtotal, $item_count);
    $stmt->fetch();
    $stmt->close();

    $subtotal   = (float)$subtotal;
    $item_count = (int)$item_count;

    // Example business rules
    $tax_rate = 0.07; // 7%
    $tax = $subtotal * $tax_rate;

    // Free shipping if subtotal >= 50 or no items
    if ($subtotal >= 50 || $subtotal == 0) {
        $shipping = 0.0;
    } else {
        $shipping = 5.0;
    }

    $total = $subtotal + $tax + $shipping;

    return [
        'item_count' => $item_count,
        'subtotal'   => $subtotal,
        'tax'        => $tax,
        'shipping'   => $shipping,
        'total'      => $total,
    ];
}

/**
 * Update quantity of a cart item for this customer.
 *
 * @param int $cid
 * @param int $cart_item_id
 * @param int $quantity
 * @return bool
 */
function update_cart_quantity(int $cid, int $cart_item_id, int $quantity): bool
{
    global $conn;

    if ($quantity <= 0) {
        return remove_item($cid, $cart_item_id);
    }

    // Ensure this cart_item belongs to this customer
    $sql = "UPDATE cartitems ci
            JOIN cart c ON ci.cart_id = c.cart_id
            SET ci.quantity = ?
            WHERE ci.cart_item_id = ? 
              AND c.cid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $cart_item_id, $cid);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

/**
 * Remove a cart item for this customer.
 *
 * @param int $cid
 * @param int $cart_item_id
 * @return bool
 */
function remove_item(int $cid, int $cart_item_id): bool
{
    global $conn;

    $sql = "DELETE ci 
            FROM cartitems ci
            JOIN cart c ON ci.cart_id = c.cart_id
            WHERE ci.cart_item_id = ? 
              AND c.cid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_item_id, $cid);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
