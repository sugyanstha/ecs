<?php
include('../database/connection.php');

// Check if the search query is set
if (isset($_GET['query'])) {
    $search_query = $_GET['query'];

    // Sanitize the search query to prevent SQL injection
    $search_query = '%' . $conn->real_escape_string($search_query) . '%';

    // Query the database for products that match the search query
    $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $search_query, $search_query);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // If no search query, redirect to home page or show a message
    echo "No search query provided.";
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Navbar (you can copy your navbar here as it is) -->

<div class="container mt-5">
    <h1>Search Results for "<?php echo htmlspecialchars($_GET['query']); ?>"</h1>

    <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="../img/<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <p class="card-text"><strong>Price: NRs. <?php echo number_format($row['price'], 2); ?></strong></p>
                            <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn btn-info">View Details</a>
                            <a href="customer/place_order.php?product_id=<?php echo $row['product_id']; ?>" class="btn btn-warning">Place Order</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No products found matching your search query.</p>
    <?php endif; ?>

</div>

</body>
</html>
