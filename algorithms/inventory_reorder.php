<?php
// Inventory Reorder Algorithm with FIFO-aware batch info if available
// Run via CLI/cron: php algorithms/inventory_reorder.php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';

$config = [
    'lookback_days'    => 30, // window for average daily sales
    'lead_time_days'   => 3,  // supplier lead time
    'safety_stock_days'=> 2,  // buffer to absorb demand spikes
    'alert_email'      => 'admin@example.com', // replace with your admin email
];

if (php_sapi_name() === 'cli' || basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    runInventoryReorder($conn, $config);
}

function runInventoryReorder(mysqli $conn, array $config): array
{
    $products = detectReorderNeeds($conn, $config);

    foreach ($products as $product) {
        if ($product['needs_reorder']) {
            sendReorderAlert($product, $config['alert_email']);
        }
    }

    return $products;
}

function detectReorderNeeds(mysqli $conn, array $config): array
{
    $lookbackDays = max(1, (int)($config['lookback_days'] ?? 30));
    $leadTimeDays = max(0, (int)($config['lead_time_days'] ?? 3));
    $safetyStockDays = max(0, (int)($config['safety_stock_days'] ?? 2));
    $windowStart = (new DateTimeImmutable("-{$lookbackDays} days"))->format('Y-m-d H:i:s');

    $sql = "SELECT p.product_id,
                   p.name,
                   p.stock,
                   COALESCE(SUM(CASE WHEN o.created_at >= ? AND o.status IN ('pending','shipped','delivered') THEN oi.quantity ELSE 0 END), 0) AS sold_qty
            FROM products p
            LEFT JOIN orderitems oi ON oi.product_id = p.product_id
            LEFT JOIN orders o ON o.order_id = oi.order_id
            GROUP BY p.product_id, p.name, p.stock";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('s', $windowStart);
    $stmt->execute();
    $rows = $stmt->get_result();
    $products = $rows ? $rows->fetch_all(MYSQLI_ASSOC) : [];

    foreach ($products as &$product) {
        $avgDailySales = ((float)$product['sold_qty']) / $lookbackDays;
        $reorderLevel = ($avgDailySales * $leadTimeDays) + ($avgDailySales * $safetyStockDays);

        $product['avg_daily_sales'] = $avgDailySales;
        $product['reorder_level'] = (int)ceil($reorderLevel);
        $product['needs_reorder'] = ((int)$product['stock']) <= $product['reorder_level'];
        $product['fifo_batches'] = buildFifoPlan($conn, (int)$product['product_id']);
    }

    return $products;
}

function sendReorderAlert(array $product, string $adminEmail): void
{
    $subject = sprintf('Reorder needed: %s (ID %d)', $product['name'], $product['product_id']);
    $body = sprintf(
        "Product: %s (ID %d)\nCurrent stock: %d\nReorder level: %d\nAverage daily sales (lookback): %.2f\nFIFO batches: %s",
        $product['name'],
        $product['product_id'],
        $product['stock'],
        $product['reorder_level'],
        $product['avg_daily_sales'],
        json_encode($product['fifo_batches'])
    );

    if ($adminEmail) {
        @mail($adminEmail, $subject, $body);
    }

    error_log(sprintf('[inventory_reorder] alert sent for %s (ID %d)', $product['name'], $product['product_id']));
}

/**
 * Optional FIFO helper. If a `stocks` table with batch dates exists (pid, s_quantity, s_in_out, s_entryDate),
 * return the batches in consumption order; otherwise return an empty array.
 */
function buildFifoPlan(mysqli $conn, int $productId): array
{
    if (!tableExists($conn, 'stocks')) {
        return [];
    }

    $sql = "SELECT sid AS batch_id, s_quantity AS quantity, s_entryDate AS received_at
            FROM stocks
            WHERE pid = ? AND s_in_out = 0
            ORDER BY s_entryDate ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare failed for FIFO: ' . $conn->error);
    }

    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function tableExists(mysqli $conn, string $table): bool
{
    $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $table);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows > 0;
}
