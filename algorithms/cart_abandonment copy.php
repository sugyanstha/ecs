<?php
/**
 * Cart Abandonment Detection using a Decision Tree–style Rule-Based Algorithm
 *
 * HOW IT WORKS (DSA point of view):
 * --------------------------------
 * For each candidate cart, we compute features:
 *   - ageHours      : How old the cart is (in hours)
 *   - itemCount     : Number of items in the cart
 *   - totalAmount   : Total monetary value of the cart
 *   - hasRecentOrder: Whether the user has placed any order after the cart was created
 *
 * Then we feed these features into a Decision Tree–style function:
 *
 *   if (hasRecentOrder) -> NOT abandoned
 *   else if (ageHours < 1) -> NOT abandoned
 *   else if (ageHours > 24 && itemCount >= 1) -> ABANDONED
 *   else if (ageHours > 6 && totalAmount < 500) -> ABANDONED
 *   else -> NOT abandoned
 *
 * Carts classified as ABANDONED are stored in `abandoned_carts` with status 'pending'.
 */

// 1. Include DB connection (adjust path as needed)
require_once __DIR__ . '/../database/connection.php'; // must define $conn (mysqli)

// 2. CONFIG
$THRESHOLD_HOURS = 1; // minimum age to even consider a cart as candidate
$DEBUG = true;        // set false when using via cron/production

/**
 * Debug helper
 */
function debug_log($msg)
{
    global $DEBUG;
    if ($DEBUG) {
        echo htmlspecialchars($msg) . "<br>\n";
    }
}

/**
 * Decision Tree–style classifier for cart abandonment.
 *
 * Input features:
 *   - $ageHours      : float, hours since cart was created
 *   - $itemCount     : int, total quantity of items in the cart
 *   - $totalAmount   : float, total cart value
 *   - $hasRecentOrder: bool, true if user placed any order after cart creation
 *
 * Returns:
 *   - true  -> cart is considered ABANDONED
 *   - false -> cart is NOT abandoned
 */
function classify_cart_abandonment(float $ageHours, int $itemCount, float $totalAmount, bool $hasRecentOrder): bool
{
    // DECISION TREE (rule-based):

    // Node 1: If user has already placed an order after this cart -> not abandoned
    if ($hasRecentOrder) {
        return false;
    }

    // Node 2: If the cart is very recent (< 1 hour), give user time -> not abandoned
    if ($ageHours < 1.0) {
        return false;
    }

    // Node 3: If no items or 0 amount, ignore
    if ($itemCount <= 0 || $totalAmount <= 0) {
        return false;
    }

    // Node 4: If cart is older than 24 hours and has items -> abandoned
    if ($ageHours > 24.0 && $itemCount >= 1) {
        return true;
    }

    // Node 5: If cart is older than 6 hours and low value (e.g., < 500) -> likely abandoned
    if ($ageHours > 6.0 && $totalAmount < 500.0) {
        return true;
    }

    // Default: not classified as abandoned
    return false;
}

/**
 * Detect abandoned carts and insert them into abandoned_carts.
 * This function implements the "candidate selection" + "feature extraction".
 */
function detectAbandonedCartsDecisionTree(int $thresholdHours): void
{
    global $conn;

    // Only consider carts older than this time
    $cutoffTime = date('Y-m-d H:i:s', time() - $thresholdHours * 3600);
    debug_log("Cutoff time for candidate carts: $cutoffTime");

    /**
     * CANDIDATE SELECTION:
     *   - cart.created_at <= cutoffTime
     *   - has at least one item in cartitems
     *   - not already in abandoned_carts
     *
     * We also aggregate features:
     *   - itemCount   = SUM(ci.quantity)
     *   - totalAmount = SUM(ci.quantity * p.price)
     */

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
        debug_log("ERROR: Failed to prepare candidate query: " . $conn->error);
        return;
    }

    $stmt->bind_param("s", $cutoffTime);

    if (!$stmt->execute()) {
        debug_log("ERROR: Failed to execute candidate query: " . $stmt->error);
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
        debug_log("No candidate carts found for possible abandonment.");
        return;
    }

    debug_log("Found " . count($candidates) . " candidate carts.");

    // Prepare insert statement for abandoned_carts
    $insertSql = "
        INSERT INTO abandoned_carts (cart_id, cid, detected_at, status)
        VALUES (?, ?, NOW(), 'pending')
    ";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        debug_log("ERROR: Failed to prepare insert into abandoned_carts: " . $conn->error);
        return;
    }

    // For each candidate, compute features and classify using the decision tree
    foreach ($candidates as $cart) {
        $cartId      = (int)$cart['cart_id'];
        $cid         = (int)$cart['cid'];
        $createdAt   = $cart['created_at'];
        $itemCount   = (int)$cart['itemCount'];
        $totalAmount = (float)$cart['totalAmount'];

        // Compute age in hours
        $ageSeconds = time() - strtotime($createdAt);
        $ageHours   = $ageSeconds / 3600.0;

        // FEATURE: hasRecentOrder
        // -------------------------------------
        // We check if user has placed ANY order after cart.created_at.
        // Adjust `Orders` and `created_at` column name if different.
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

        debug_log("Cart {$cartId} | cid={$cid} | ageHours=" . round($ageHours, 2) . " | items={$itemCount} | total={$totalAmount} | hasRecentOrder=" . ($hasRecentOrder ? 'YES' : 'NO'));

        // Decision Tree classification
        $isAbandoned = classify_cart_abandonment($ageHours, $itemCount, $totalAmount, $hasRecentOrder);

        if ($isAbandoned) {
            // Insert into abandoned_carts
            $insertStmt->bind_param("ii", $cartId, $cid);
            if ($insertStmt->execute()) {
                debug_log(" -> Marked cart_id={$cartId} as ABANDONED (pending).");
            } else {
                debug_log(" -> ERROR inserting abandoned cart_id={$cartId}: " . $insertStmt->error);
            }
        } else {
            debug_log(" -> Cart_id={$cartId} NOT abandoned by decision tree.");
        }
    }

    $insertStmt->close();
}

/**
 * OPTIONAL: Get abandoned carts for a user (for reminders on dashboard).
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


// MAIN EXECUTION
detectAbandonedCartsDecisionTree($THRESHOLD_HOURS);

debug_log("Decision Tree–based cart abandonment detection finished.");

