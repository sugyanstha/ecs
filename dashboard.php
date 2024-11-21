<link rel="stylesheet" href="css/card.css">


<?php
session_start();
include('customer/layout/cheader.php'); // Including header layout
include('database/connection.php'); // Including database connection

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email']; // Fetch logged-in user email
$user_id = $_SESSION['cid']; // Retrieve the logged-in user ID from the session

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Function to build user's category preference vector
 */
function getUserPreferenceVector($conn, $cid, $categories) {
    $preference_vector = array_fill_keys($categories, 0);

    // Fetch category preferences based on orders
    $orderQuery = "SELECT p.category_id, COUNT(*) AS count 
                   FROM Orders o 
                   JOIN OrderItems oi ON o.order_id = oi.order_id 
                   JOIN Products p ON oi.product_id = p.product_id 
                   WHERE o.cid = ? 
                   GROUP BY p.category_id";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $preference_vector[$row['category_id']] += $row['count'];
    }

    // Fetch category preferences based on reviews
    $reviewQuery = "SELECT p.category_id, COUNT(*) AS count 
                    FROM Reviews r 
                    JOIN Products p ON r.product_id = p.product_id 
                    WHERE r.cid = ? 
                    GROUP BY p.category_id";
    $stmt = $conn->prepare($reviewQuery);
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $preference_vector[$row['category_id']] += $row['count'];
    }

    return $preference_vector;
}

/**
 * Function to build product vectors
 */
function getProductVectors($conn, $categories) {
    $product_vectors = [];

    $query = "SELECT product_id, category_id, name, description, price, image_url FROM Products";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $vector = array_fill_keys($categories, 0);
        $vector[$row['category_id']] = 1;
        $row['vector'] = $vector;
        $product_vectors[] = $row;
    }

    return $product_vectors;
}

/**
 * Function to compute cosine similarity
 */
function computeCosineSimilarity($user_vector, $product_vector) {
    $dot_product = 0;
    $user_magnitude = 0;
    $product_magnitude = 0;

    foreach ($user_vector as $category => $user_score) {
        $product_score = $product_vector[$category];
        $dot_product += $user_score * $product_score;
        $user_magnitude += $user_score ** 2;
        $product_magnitude += $product_score ** 2;
    }

    if ($user_magnitude == 0 || $product_magnitude == 0) {
        return 0; // Avoid division by zero
    }

    return $dot_product / (sqrt($user_magnitude) * sqrt($product_magnitude));
}

/**
 * Function to get recommended products
 */
function getRecommendedProducts($user_vector, $product_vectors, $limit = 10) {
    $recommendations = [];

    foreach ($product_vectors as $product) {
        $similarity = computeCosineSimilarity($user_vector, $product['vector']);
        $product['similarity'] = $similarity;
        $recommendations[] = $product;
    }

    // Sort products by similarity score
    usort($recommendations, function ($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });

    return array_slice($recommendations, 0, $limit);
}

/**
 * Function to display recommended products
 */
function displayRecommendedProducts($products) {
    if (empty($products)) {
        echo "<p>No recommendations available at this time.</p>";
        return;
    }

    // Title for Recommended Products
    echo '<h2 class="recommended-title">Recommended Products</h2>';

    // Container for the grid layout of products
    echo '<div class="product-grid">';
    foreach ($products as $product) {
        $image_url = htmlspecialchars($product['image_url']);
        $product_name = htmlspecialchars($product['name']);
        $product_description = htmlspecialchars($product['description']);
        $product_price = number_format($product['price'], 2);

        echo '
        <div class="product-card">
            <div class="product-image">
                <img src="img/' . $image_url . '" alt="' . $product_name . '" class="img-fluid">
            </div>
            <div class="product-details">
                <h3>' . $product_name . '</h3>
                <p>' . $product_description . '</p>
                <p><strong>Price: NRs ' . $product_price . '</strong></p>
            </div>
        </div>';
    }
    echo '</div>';
}

// Step 1: Get all category IDs
$categoriesQuery = "SELECT DISTINCT category_id FROM Categories";
$result = $conn->query($categoriesQuery);
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row['category_id'];
}

// Step 2: Build user preference vector
$user_vector = getUserPreferenceVector($conn, $user_id, $categories);

// Step 3: Build product vectors
$product_vectors = getProductVectors($conn, $categories);

// Step 4: Get recommended products
$recommended_products = getRecommendedProducts($user_vector, $product_vectors, 10);

// Step 5: Display recommended products
displayRecommendedProducts($recommended_products);
?>
