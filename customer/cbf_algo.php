<?php
// Database connection
include('../database/connection.php');
include('../customer/layout/cheader.php');

// Step 1: Get categories of the products the user has purchased or reviewed
function getUserPreferredCategories($conn, $user_id) {
    // Fetch categories based on user's purchased products
    $query = "SELECT DISTINCT p.category_id FROM Orders o 
              JOIN OrderItems oi ON o.order_id = oi.order_id
              JOIN Products p ON oi.product_id = p.product_id 
              WHERE o.cid = ? 
              UNION 
              SELECT DISTINCT p.category_id FROM Reviews r 
              JOIN Products p ON r.product_id = p.product_id 
              WHERE r.user_id = ?";

    // Prepare statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $category_ids = [];
    while ($row = $result->fetch_assoc()) {
        $category_ids[] = $row['category_id'];
    }

    return $category_ids;
}

// Step 2: Get recommended products based on categories
function getRecommendedProducts($conn, $category_ids, $limit = 10) {
    if (empty($category_ids)) {
        return [];
    }

    // Convert array of category IDs to a comma-separated string for SQL IN clause
    $category_id_str = implode(',', array_map('intval', $category_ids));

    // Fetch recommended products from the same categories
    $query = "SELECT product_id, name, description, price, image_url 
              FROM Products 
              WHERE category_id IN ($category_id_str) 
              ORDER BY RAND() 
              LIMIT ?";

    // Prepare statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Step 3: Display products in grid view
function displayRecommendedProducts($products) {
    if (empty($products)) {
        echo "<p>No recommendations available at this time.</p>";
        return;
    }

    echo '<div class="product-grid">';
    foreach ($products as $product) {
        echo '
        <div class="product-card">
            <img src="' . htmlspecialchars($product['image_url']) . '" alt="' . htmlspecialchars($product['name']) . '">
            <h3>' . htmlspecialchars($product['name']) . '</h3>
            <p>' . htmlspecialchars($product['description']) . '</p>
            <p><strong>Price: $' . number_format($product['price'], 2) . '</strong></p>
        </div>';
    }
    echo '</div>';
}

// Step 4: Putting it all together (e.g., when a user visits the homepage)
$user_id = 1; // Replace with the logged-in user ID from your session

// Get the user's preferred categories
$preferred_categories = getUserPreferredCategories($conn, $user_id);

// Get recommended products based on categories
$recommended_products = getRecommendedProducts($conn, $preferred_categories, 10);

// Display the recommended products
displayRecommendedProducts($recommended_products);

?>
