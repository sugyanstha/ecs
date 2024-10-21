<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['cid'])) {
    header('Location: login.php');
    exit();
}

include('../database/connection.php'); // Database connection
include('../customer/layout/cheader.php');

// Get customer ID from the session
$customer_id = $_SESSION['cid'];

// SQL query to fetch the order details for the logged-in customer
$order_query = "SELECT o.order_id, o.created_at AS order_date, o.total_price, o.status, 
                oi.order_item_id, oi.quantity, oi.price AS item_price, p.product_id, 
                p.name AS product_name, p.description AS product_description, p.image_url
                FROM orders o 
                JOIN orderItems oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.cid = ? ORDER BY o.order_id DESC;";

$stmt = $conn->prepare($order_query);
$stmt->bind_param('i', $customer_id); // Bind customer ID to the query
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="my-orders">
    <h2 style="text-align: center; padding: 20px;">My Orders</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product Name</th>
                <th>Product Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Product Image</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $result->fetch_assoc()) { ?>
                <tr data-orderid="<?php echo htmlspecialchars($order['order_id']); ?>">
                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['product_description']); ?></td>
                    <td><?php echo number_format($order['item_price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                    <td><?php echo number_format($order['quantity'] * $order['item_price'], 2); ?></td>
                    <td><img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt="Product Image" width="100"></td>
                    <td><?php echo date('d-m-Y', strtotime($order['order_date'])); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td>
                        <?php if ($order['status'] == 'pending'): ?>
                            <button type="button" class="btn btn-danger cancelOrderBtn" data-orderid="<?php echo htmlspecialchars($order['order_id']); ?>">
                                Cancel Order
                            </button>
                        <?php elseif ($order['status'] == 'delivered'): ?>
                            <button class="btn btn-success" disabled>Delivered</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary" disabled>Cancel Order</button>
                        <?php endif; ?>
                        <br><br>
                        <!-- Give Review Form -->
                        <form method="post" action="review.php?product_id=<?php echo htmlspecialchars($order['product_id']); ?>">
                            <input type="hidden" value="<?php echo htmlspecialchars($order['order_id']); ?>" name="orderid" />
                            <input type="submit" value="Give Review" name="give_review" 
                            style="background-color: #07b934; color: white; border-radius: 5px;
                            border: none; padding: 10px 15px; cursor: pointer;"
                            onmouseover="this.style.backgroundColor='#04a004';"
                            onmouseout="this.style.backgroundColor='#07b934';"
                            <?php if ($order['status'] != 'delivered') echo 'disabled'; ?> />
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel" aria-hidden="true" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Enter Order ID</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cancelOrderForm">
                    <div class="form-group">
                        <label for="order_id">Order ID:</label>
                        <input type="number" class="form-control" id="order_id" name="order_id" required>
                    </div>
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </form>
            </div>
        </div>
    </div>
</div>


<a href="../dashboard.php">Back to Dashboard</a>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.cancelOrderBtn').click(function() {
        const orderId = $(this).data('orderid');
        $('#order_id').val(orderId); // Set the order ID in the modal input
        $('#cancelOrderModal').modal('show'); // Show the modal
    });

    $('#cancelOrderForm').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        $.ajax({
            url: 'cancel_order.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Handle the response
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload(); 
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong while canceling your order.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
