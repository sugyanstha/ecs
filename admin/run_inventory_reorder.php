<?php
session_start();
if (!isset($_SESSION['adminemail'])) {
    header('Location: admin_login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../algorithms/inventory_reorder.php';

$config = [
    'lookback_days'     => isset($_GET['lookback']) ? max(1, (int)$_GET['lookback']) : 30,
    'lead_time_days'    => isset($_GET['lead']) ? max(0, (int)$_GET['lead']) : 3,
    'safety_stock_days' => isset($_GET['safety']) ? max(0, (int)$_GET['safety']) : 2,
    'alert_email'       => 'admin@example.com',
];

$products = runInventoryReorder($conn, $config);
$needsReorder = array_filter($products, fn($p) => $p['needs_reorder']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Reorder Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>Inventory Reorder Check</h3>
    <p>Total products scanned: <?php echo count($products); ?></p>
    <p>Products needing reorder: <?php echo count($needsReorder); ?></p>

    <?php if ($needsReorder): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Stock</th>
                    <th>Reorder Level</th>
                    <th>Avg Daily Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($needsReorder as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars($p['stock']); ?></td>
                        <td><?php echo htmlspecialchars($p['reorder_level']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($p['avg_daily_sales'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No products need reordering based on current thresholds.</p>
    <?php endif; ?>

    <a class="btn btn-secondary" href="admin_panel.php">Back to Admin</a>
</body>
</html>
