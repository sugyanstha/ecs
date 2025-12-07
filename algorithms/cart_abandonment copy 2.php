<?php
/**
 * Cart Abandonment Detection using a Decision Tree–style Rule-Based Algorithm
 * (Test version: uses MINUTES as unit so you can test quickly)
 *
 * This file ONLY defines functions & config.
 * It does NOT echo anything and does NOT auto-run detection.
 */

require_once __DIR__ . '/../database/connection.php'; // $conn

// CONFIG (you can change threshold later for production)
$CART_ABANDONMENT_THRESHOLD_MINUTES = 1; // consider carts abandoned after 1 minute (for testing)
$CART_ABANDONMENT_DEBUG = false;        // set true only when debugging in a separate script

/**
 * Debug helper (does NOT print in dashboard, only if debug is enabled).
 */
function cart_abandonment_debug_log($msg)
{
    global $CART_ABANDONMENT_DEBUG;
    if ($CART_ABANDONMENT_DEBUG) {
        echo htmlspecialchars($msg) . "<br>\n";
    }
}

/**
 * Decision Tree–style classifier for cart abandonment (using minutes).
 *
 * @param float $ageMinutes
 * @param int   $itemCount
 * @param float $totalAmount
 * @param bool  $hasRecentOrder
 * @return bool true if abandoned, false otherwise
 */
function classify_cart_abandonment(float $ageMinutes, int $itemCount, float $totalAmount, bool $hasRecentOrder): bool
{
    // If user already placed an order after this cart -> not abandoned
    if ($hasRecentOrder) {
        return false;
    }

    // Very recent cart (< 1 minute) -> not abandoned
    if ($ageMinutes < 1.0) {
        return false;
    }

    // Empty cart or zero total -> ignore
    if ($itemCount <= 0 || $totalAmount <= 0) {
        return false;
    }

    // For testing: any non-empty cart older than or equal to 1 minute -> abandoned
    if ($ageMinutes >= 1.0 && $itemCount >= 1) {
        return true;
    }

    return false;
}

/**
 * Detect abandoned carts and insert them into abandoned_carts.
 *
 * @param int $thresholdMinutes  minimum age (minutes) to consider a cart as candidate
 */
function detectAbandonedCartsDecisionTree(int $thresholdMinutes): void
{
    global $conn;
    global $CART_ABANDONMENT_DEBUG;

    $cutoffTime = date('Y-m-d H:i:s', time() - $thresholdMinutes * 60);
    cart_abandonment_debug_log("Cutoff time for candidate carts: $cutoffTime");

    $sql = "
        SELECT 
            c.cart_id,
            c.cid,
            c.created_at,
            COALESCE(SUM(ci.quantity), 0) AS itemCount,
            COALESCE(SUM(ci.quantity * p.price), 0) AS totalAmount
        FROM cart c
        INNER JOIN cartitems ci ON ci.cart_id = c.cart_id
        INNER JOIN products p ON p.product_id = ci.product_id
        LEFT JOIN abandoned_carts ac ON ac.cart_id = c.cart_id
        WHERE 
            c.created_at <= ?
            AND ac.cart_id IS NULL
        GROUP BY c.cart_id, c.cid, c.created_at
        HAVING itemCount > 0
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        cart_abandonment_debug_log("ERROR: Failed to prepare candidate query: " . $conn->error);
        return;
    }

    $stmt->bind_param("s", $cutoffTime);

    if (!$stmt->execute()) {
        cart_abandonment_debug_log("ERROR: Failed to execute candidate query: " . $stmt->error);
        $stmt->close();
        return;
    }

    $result = $stmt->get_result();
    $candidates = [];

    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }

    $stmt->close();

    if (empty($candidates)) {
        cart_abandonment_debug_log("No candidate carts found for possible abandonment.");
        return;
    }

    cart_abandonment_debug_log("Found " . count($candidates) . " candidate carts.");

    $insertSql = "
        INSERT INTO abandoned_carts (cart_id, cid, detected_at, status)
        VALUES (?, ?, NOW(), 'pending')
    ";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        cart_abandonment_debug_log("ERROR: Failed to prepare insert into abandoned_carts: " . $conn->error);
        return;
    }

    foreach ($candidates as $cart) {
        $cartId      = (int)$cart['cart_id'];
        $cid         = (int)$cart['cid'];
        $createdAt   = $cart['created_at'];
        $itemCount   = (int)$cart['itemCount'];
        $totalAmount = (float)$cart['totalAmount'];

        $ageSeconds = time() - strtotime($createdAt);
        $ageMinutes = $ageSeconds / 60.0;

        // Has user placed any order after this cart?
        $hasRecentOrder = false;
        $orderSql = "
            SELECT 1
            FROM Orders o
            WHERE o.cid = ?
              AND o.created_at >= ?
            LIMIT 1
        ";
        $oStmt = $conn->prepare($orderSql);
        if ($oStmt) {
            $oStmt->bind_param("is", $cid, $createdAt);
            if ($oStmt->execute()) {
                $oResult = $oStmt->get_result();
                if ($oResult && $oResult->fetch_row()) {
                    $hasRecentOrder = true;
                }
            }
            $oStmt->close();
        }

        cart_abandonment_debug_log(
            "Cart {$cartId} | cid={$cid} | ageMinutes=" . round($ageMinutes, 2) .
            " | items={$itemCount} | total={$totalAmount} | hasRecentOrder=" .
            ($hasRecentOrder ? 'YES' : 'NO')
        );

        $isAbandoned = classify_cart_abandonment($ageMinutes, $itemCount, $totalAmount, $hasRecentOrder);

        if ($isAbandoned) {
            $insertStmt->bind_param("ii", $cartId, $cid);
            if ($insertStmt->execute()) {
                cart_abandonment_debug_log(" -> Marked cart_id={$cartId} as ABANDONED (pending).");
            } else {
                cart_abandonment_debug_log(" -> ERROR inserting abandoned cart_id={$cartId}: " . $insertStmt->error);
            }
        } else {
            cart_abandonment_debug_log(" -> Cart_id={$cartId} NOT abandoned by decision tree.");
        }
    }

    $insertStmt->close();

    cart_abandonment_debug_log("Decision Tree–based cart abandonment detection finished (TEST MODE: minutes).");
}

/**
 * Get abandoned carts for a user (status 'pending') ordered by most recent detected.
 *
 * @param int $cid
 * @return array
 */
function getAbandonedCartsForUserDT(int $cid): array
{
    global $conn;

    $sql = "
        SELECT ac.*, c.created_at
        FROM abandoned_carts ac
        INNER JOIN cart c ON c.cart_id = ac.cart_id
        WHERE ac.cid = ?
          AND ac.status = 'pending'
        ORDER BY ac.detected_at DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $cid);
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    return $rows;
}
