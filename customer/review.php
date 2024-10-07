<?php
session_start();
include('../database/connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['cid'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['cid']; // Assuming customer ID is stored in session

// Initialize variables
$product = [];
$error = '';
$success = '';
$reviews = [];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['rating'], $_POST['comment'], $_POST['product_id'])) {
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        $product_id = intval($_POST['product_id']);
        
        if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
            // Insert the review into the database
            $query = "INSERT INTO reviews (product_id, cid, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiis", $product_id, $customer_id, $rating, $comment);
            
            if ($stmt->execute()) {
                $success = "Thank you! Your review has been submitted.";
            } else {
                $error = "Error submitting review: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Please provide a valid rating (1-5) and a comment.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch product details (for displaying the product information)
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    
    // Fetch product details
    $product_query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found.");
    }

    // Fetch reviews for the product
    $review_query = "SELECT r.*, c.name AS customer_name FROM reviews r JOIN customer c ON r.cid = c.cid WHERE r.product_id = ? ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($review_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $reviews_result = $stmt->get_result();
    
    while ($row = $reviews_result->fetch_assoc()) {
        $reviews[] = $row;
    }
} else {
    die("Product ID is missing.");
}
?>

<link rel="stylesheet" href="../css/form.css">

<div class="container">
    <!-- Display product details -->
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="200">

    <!-- Display review form -->
    <h3>Leave a Review</h3>
    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php elseif ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>
    
    <form method="POST" action="review.php?product_id=<?php echo $product_id; ?>">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        
        <label for="rating">Rating (1-5)</label>
        <input type="number" name="rating" min="1" max="5" required>

        <label for="comment">Comment</label>
        <textarea name="comment" placeholder="Write your review here" required></textarea>
        
        <button type="submit">Submit Review</button>
    </form>

    <!-- Display all reviews for the product -->
    <h3>Reviews</h3>
    <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review">
                <p><strong><?php echo htmlspecialchars($review['customer_name']); ?></strong> rated: <?php echo $review['rating']; ?>/5</p>
                <p><?php echo htmlspecialchars($review['comment']); ?></p>
                <p><small>Reviewed on: <?php echo $review['created_at']; ?></small></p>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No reviews yet. Be the first to review this product!</p>
    <?php endif; ?>
</div>
