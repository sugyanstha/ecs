<?php
/**
 * Only Cart Expiry Helper
 * Purpose: Provide expiry time for the customer's active cart
 */

require_once __DIR__ . '/../database/connection.php'; // $conn

// For demonstration/testing: 1 minute expiry
// In production change to: 60 (1 hour) or 1440 (1 day)
$CART_EXPIRY_MINUTES = 1;

/**
 * Fetch latest cart with items and compute expiry time.
 * Returns:
 * [
 *   'cart_id' => int,
 *   'created_at' => 'Y-m-d H:i:s',
 *   'expiry_at' => 'Y-m-d H:i:s',
 *   'item_count' => int,
 *   'total_value' => float
 * ]
 * OR null if no cart with items exists
 */
function getUserCartExpiryInfo(int $cid)
{
    global $conn, $CART_EXPIRY_MINUTES;

    $sql = "
        SELECT 
            c.cart_id,
            c.created_at,
            COALESCE(SUM(ci.quantity), 0) AS item_count,
            COALESCE(SUM(ci.quantity * p.price), 0) AS total_value
        FROM cart c
        INNER JOIN cartitems ci ON ci.cart_id = c.cart_id
        INNER JOIN products p ON p.product_id = ci.product_id
        WHERE c.cid = ?
        GROUP BY c.cart_id, c.created_at
        HAVING item_count > 0
        ORDER BY c.created_at DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;

    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart = $result->fetch_assoc();
    $stmt->close();

    if (!$cart) return null;

    $createdAt = $cart['created_at'];
    $expiryTimestamp = strtotime($createdAt) + ($CART_EXPIRY_MINUTES * 60);
    $expiryFormatted = date('Y-m-d H:i:s', $expiryTimestamp);

    return [
        'cart_id'     => (int)$cart['cart_id'],
        'created_at'  => $createdAt,
        'expiry_at'   => $expiryFormatted,
        'item_count'  => (int)$cart['item_count'],
        'total_value' => (float)$cart['total_value']
    ];
}
