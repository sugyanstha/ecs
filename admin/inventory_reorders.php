<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TODO: Optionally check admin login here
// if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
//     header('Location: ../customer/login.php');
//     exit();
// }

// Include DB connection (adjust path if needed)
require_once __DIR__ . '/../database/connection.php';

// Handle actions: mark as ordered / ignored
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id     = (int)$_POST['id'];
    $action = $_POST['action'];

    if (in_array($action, ['ordered', 'ignored'], true)) {
        $stmt = $conn->prepare("UPDATE inventory_reorders SET status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $action, $id);
            if ($stmt->execute()) {
                $_SESSION['reorder_message'] = [
                    'type' => 'success',
                    'text' => ($action === 'ordered' 
                                ? 'Reorder marked as ordered successfully.' 
                                : 'Reorder marked as ignored.')
                ];
            } else {
                $_SESSION['reorder_message'] = [
                    'type' => 'error',
                    'text' => 'Database error while updating reorder status.'
                ];
            }
            $stmt->close();
        } else {
            $_SESSION['reorder_message'] = [
                'type' => 'error',
                'text' => 'Failed to prepare statement.'
            ];
        }
    } else {
        $_SESSION['reorder_message'] = [
            'type' => 'error',
            'text' => 'Invalid action requested.'
        ];
    }

    // Redirect to avoid form resubmission
    header('Location: inventory_reorders.php');
    exit();
}

// Fetch pending reorders
$sql = "
    SELECT 
        ir.id,
        ir.product_id,
        ir.recommended_qty,
        ir.reason,
        ir.detected_at,
        p.name AS product_name,
        p.stock AS current_stock
    FROM inventory_reorders ir
    INNER JOIN products p ON p.product_id = ir.product_id
    WHERE ir.status = 'pending'
    ORDER BY ir.detected_at DESC
";

$result = $conn->query($sql);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Inventory Reorders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert 2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">

<div class="container my-5">
    <h1 class="mb-4 text-center">Pending Inventory Reorders</h1>

    <?php
    // SweetAlert message if exists
    if (isset($_SESSION['reorder_message'])) {
        $m = $_SESSION['reorder_message'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '" . ($m['type'] === 'success' ? 'success' : 'error') . "',
                    title: '" . ($m['type'] === 'success' ? 'Success' : 'Error') . "',
                    text: '" . htmlspecialchars($m['text'], ENT_QUOTES) . "'
                });
            });
        </script>";
        unset($_SESSION['reorder_message']);
    }
    ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Current Stock</th>
                                <th>Recommended Qty</th>
                                <th>Reason</th>
                                <th>Detected At</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int)$row['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['product_name']); ?></strong><br>
                                    <small class="text-muted">ID: <?php echo (int)$row['product_id']; ?></small>
                                </td>
                                <td><?php echo (int)$row['current_stock']; ?></td>
                                <td><?php echo (int)$row['recommended_qty']; ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($row['reason']); ?></small>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($row['detected_at']); ?></small>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <!-- Mark as Ordered -->
                                        <form method="post" onsubmit="return confirmAction(event, 'Mark this reorder as ordered?');">
                                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                            <input type="hidden" name="action" value="ordered">
                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                Mark Ordered
                                            </button>
                                        </form>

                                        <!-- Mark as Ignored -->
                                        <form method="post" onsubmit="return confirmAction(event, 'Ignore this reorder suggestion?');">
                                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                            <input type="hidden" name="action" value="ignored">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                                Ignore
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mb-0 text-center">No pending reorders at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Use SweetAlert as a confirm dialog for actions
function confirmAction(e, message) {
    e.preventDefault();
    const form = e.target;

    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });

    return false;
}
</script>

</body>
</html>
