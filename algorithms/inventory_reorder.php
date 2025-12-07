<?php
/**
 * inventory_reorder_algorithm.php
 *
 * PURPOSE:
 * ---------------------------------
 * - Analyze products and recent sales to determine which products
 *   should be reordered NOW.
 * - Does NOT write to any extra table.
 * - Instead, it returns an array of "reorder suggestions" which can be
 *   displayed directly on an admin page or used in any logic.
 *
 * HOW IT WORKS:
 * -------------
 * For each product:
 *   1. Calculate average daily sales from Orders + OrderItems for last N days.
 *   2. Compute dynamic reorder point using:
 *        reorderPoint = avgDailySales * (LEAD_TIME_DAYS + SAFETY_STOCK_DAYS)
 *      If no sales history exists, a default static threshold is used.
 *   3. If currentStock <= reorderPoint -> product is added to reorder list
 *      with a recommended reorder quantity.
 */

// 1. Include DB connection (adjust path if needed)
require_once __DIR__ . '/../database/connection.php'; // $conn must be mysqli

// 2. CONFIGURATION
$LOOKBACK_DAYS      = 30;   // Sales history window (days)
$LEAD_TIME_DAYS     = 7;    // Supplier lead time (days)
$SAFETY_STOCK_DAYS  = 3;    // Safety stock coverage (days)
$TARGET_EXTRA_DAYS  = 5;    // Extra days coverage beyond lead + safety
$MIN_ORDER_QTY      = 10;   // Minimum reorder quantity if very low
$DEFAULT_REORDER_LVL = 5;   // If no sales history, fallback threshold

$INVENTORY_DEBUG = false;    // set to true to see debug logs

/**
 * Debug helper (only logs when $INVENTORY_DEBUG = true).
 */
function inventory_debug_log($msg)
{
    global $INVENTORY_DEBUG;
    if ($INVENTORY_DEBUG) {
        echo htmlspecialchars($msg) . "<br>\n";
    }
}

/**
 * Calculate average daily sales for a product for the last N days.
 *
 * Tables used:
 *  - Orders(order_id, cid, created_at, ...)
 *  - OrderItems(order_item_id, order_id, product_id, quantity, ...)
 *
 * NOTE: If your Orders table uses a different datetime column, change o.created_at.
 *
 * @param int $productId
 * @param int $lookbackDays
 * @return float avgDailySales
 */
function inventory_get_avg_daily_sales($productId, $lookbackDays)
{
    global $conn;

    if ($lookbackDays <= 0) {
        return 0.0;
    }

    $sql = "
        SELECT COALESCE(SUM(oi.quantity), 0) AS total_sold
        FROM Orders o
        INNER JOIN OrderItems oi ON oi.order_id = o.order_id
        WHERE oi.product_id = ?
          AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        inventory_debug_log("ERROR: AVG sales prepare failed: " . $conn->error);
        return 0.0;
    }

    $stmt->bind_param("ii", $productId, $lookbackDays);
    if (!$stmt->execute()) {
        inventory_debug_log("ERROR: AVG sales execute failed: " . $stmt->error);
        $stmt->close();
        return 0.0;
    }

    $result = $stmt->get_result();
    $totalSold = 0;

    if ($row = $result->fetch_assoc()) {
        $totalSold = (int)$row['total_sold'];
    }

    $stmt->close();

    if ($totalSold <= 0) {
        return 0.0;
    }

    return $totalSold / (float)$lookbackDays;
}

/**
 * MAIN FUNCTION:
 *  Get a list of products that need reordering RIGHT NOW.
 *
 * Each suggestion includes:
 *  - product_id
 *  - name
 *  - current_stock
 *  - avg_daily_sales
 *  - reorder_point
 *  - recommended_qty
 *  - reason
 *
 * @return array
 */
function get_reorder_suggestions()
{
    global $conn,
           $LOOKBACK_DAYS,
           $LEAD_TIME_DAYS,
           $SAFETY_STOCK_DAYS,
           $TARGET_EXTRA_DAYS,
           $MIN_ORDER_QTY,
           $DEFAULT_REORDER_LVL;

    $suggestions = [];

    // Fetch all products
    $sql = "SELECT product_id, name, stock FROM products";
    $result = $conn->query($sql);

    if (!$result) {
        inventory_debug_log("ERROR: Failed to fetch products: " . $conn->error);
        return $suggestions;
    }

    while ($product = $result->fetch_assoc()) {
        $productId    = (int)$product['product_id'];
        $name         = $product['name'];
        $currentStock = (int)$product['stock'];

        // Step 1: Average daily sales
        $avgDailySales = inventory_get_avg_daily_sales($productId, $LOOKBACK_DAYS);

        // Step 2: Reorder point + target stock
        if ($avgDailySales > 0) {
            $reorderPoint = $avgDailySales * ($LEAD_TIME_DAYS + $SAFETY_STOCK_DAYS);
            $targetStock  = $avgDailySales * ($LEAD_TIME_DAYS + $SAFETY_STOCK_DAYS + $TARGET_EXTRA_DAYS);
            $reason = "Dynamic; avgDailySales=" . round($avgDailySales, 2);
        } else {
            // No sales data: fallback rule
            $reorderPoint = $DEFAULT_REORDER_LVL;
            $targetStock  = max($DEFAULT_REORDER_LVL * 2, $currentStock + $MIN_ORDER_QTY);
            $reason = "No sales history; using default threshold";
        }

        inventory_debug_log("Product {$productId} ({$name}) -> stock={$currentStock}, avgDailySales=" . round($avgDailySales, 2) . ", reorderPoint=" . round($reorderPoint, 2));

        // Step 3: Decide if reorder is needed
        if ($currentStock <= $reorderPoint) {
            // Step 4: Recommended quantity
            $recommendedQty = (int)ceil($targetStock - $currentStock);
            if ($recommendedQty < $MIN_ORDER_QTY) {
                $recommendedQty = $MIN_ORDER_QTY;
            }

            $suggestions[] = [
                'product_id'     => $productId,
                'name'           => $name,
                'current_stock'  => $currentStock,
                'avg_daily_sales'=> $avgDailySales,
                'reorder_point'  => $reorderPoint,
                'recommended_qty'=> $recommendedQty,
                'reason'         => $reason,
            ];
        }
    }

    $result->free();
    return $suggestions;
}

/**
 * OPTIONAL:
 * If this file is opened directly in the browser,
 * show a simple HTML table of reorder suggestions.
 */
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $reorders = get_reorder_suggestions();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Inventory Reorder Suggestions</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="container my-5">
        <h1 class="mb-4 text-center">Inventory Reorder Suggestions</h1>

        <?php if (empty($reorders)): ?>
            <div class="alert alert-success text-center">
                No products require reordering at this time.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Current Stock</th>
                                    <th>Avg Daily Sales</th>
                                    <th>Reorder Point</th>
                                    <th>Recommended Qty</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($reorders as $r): ?>
                                <tr>
                                    <td><?php echo (int)$r['product_id']; ?></td>
                                    <td><?php echo htmlspecialchars($r['name']); ?></td>
                                    <td><?php echo (int)$r['current_stock']; ?></td>
                                    <td><?php echo number_format($r['avg_daily_sales'], 2); ?></td>
                                    <td><?php echo number_format($r['reorder_point'], 2); ?></td>
                                    <td><?php echo (int)$r['recommended_qty']; ?></td>
                                    <td><small><?php echo htmlspecialchars($r['reason']); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    </body>
    </html>
    <?php
}
