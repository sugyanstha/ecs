<?php
// Cart Abandonment Detection (24h default)
// Run via CLI/cron: php algorithms/cart_abandonment.php
// Designed to be reused from Laravel jobs, cron, or manual admin triggers.
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';

$reminderWindowHours = 24;

if (php_sapi_name() === 'cli' || basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    runCartAbandonment($conn, $reminderWindowHours);
}

function runCartAbandonment(mysqli $conn, int $windowHours): array
{
    $stats = ['found' => 0, 'reminded' => 0];

    try {
        $abandonedCarts = findAbandonedCarts($conn, $windowHours);
        $stats['found'] = count($abandonedCarts);

        foreach ($abandonedCarts as $cart) {
            if (triggerCartReminder($cart)) {
                $stats['reminded']++;
            }
        }
    } catch (Throwable $e) {
        error_log('[cart_abandonment] ' . $e->getMessage());
    }

    return $stats;
}

/**
 * Fetch carts with items that have not led to any order since they were created.
 */
function findAbandonedCarts(mysqli $conn, int $windowHours): array
{
    $cutoff = (new DateTimeImmutable("-{$windowHours} hours"))->format('Y-m-d H:i:s');

    $sql = "SELECT c.cart_id,
                   c.cid,
                   c.created_at,
                   cust.email,
                   cust.name,
                   COUNT(ci.cart_item_id) AS item_count,
                   GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') AS product_names
            FROM cart c
            INNER JOIN cartitems ci ON ci.cart_id = c.cart_id
            INNER JOIN products p ON p.product_id = ci.product_id
            INNER JOIN customer cust ON cust.cid = c.cid
            LEFT JOIN orders o
                ON o.cid = c.cid
               AND o.created_at >= c.created_at
               AND o.status <> 'canceled'
            WHERE c.created_at <= ?
            GROUP BY c.cart_id, c.cid, c.created_at, cust.email, cust.name
            HAVING COUNT(o.order_id) = 0";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('s', $cutoff);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Send reminder using PHP mail() and log as fallback.
 */
function triggerCartReminder(array $cart): bool
{
    $email = $cart['email'] ?? null;
    $name = $cart['name'] ?? 'there';
    $itemCount = (int)($cart['item_count'] ?? 0);
    $productList = $cart['product_names'] ?? '';

    $subject = 'You left items in your cart';
    $body = sprintf(
        "Hi %s, you still have %d item(s) waiting: %s. Checkout within the next few hours to complete your order.",
        $name,
        $itemCount,
        $productList
    );

    $sent = false;
    if ($email) {
        $sent = @mail($email, $subject, $body);
    }

    error_log(sprintf('[cart_abandonment] reminder %s for %s (cart %s)', $sent ? 'sent' : 'attempted', $email ?: 'unknown', $cart['cart_id'] ?? 'n/a'));
    return $sent;
}
