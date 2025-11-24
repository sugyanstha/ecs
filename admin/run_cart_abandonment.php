<?php
session_start();
if (!isset($_SESSION['adminemail'])) {
    header('Location: admin_login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../algorithms/cart_abandonment.php';

$hours = isset($_GET['hours']) ? max(1, (int)$_GET['hours']) : 24;
$stats = runCartAbandonment($conn, $hours);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart Abandonment Run</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>Cart Abandonment Reminder</h3>
    <p>Window: <?php echo htmlspecialchars($hours); ?> hour(s)</p>
    <p>Abandoned carts found: <?php echo htmlspecialchars($stats['found']); ?></p>
    <p>Reminders sent: <?php echo htmlspecialchars($stats['reminded']); ?></p>
    <a class="btn btn-secondary" href="admin_panel.php">Back to Admin</a>
</body>
</html>
