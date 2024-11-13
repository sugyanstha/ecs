<link rel="stylesheet" href="css/card.css">

<?php
session_start();
include('customer/layout/cheader.php'); // Including header layout
// include('customer/layout/sidebar.php'); // Including sidebar layout
include('database/connection.php'); // Including database connection

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email']; // Fetch logged-in user email

// Enable error reporting for debugging  
error_reporting(E_ALL);
ini_set('display_errors', 1);

// <?php
// Start session at the beginning of the script
// session_start();

// Database connection
// include('../database/connection.php');
// include('../customer/layout/cheader.php');

// Step 1: Get categories of the products the user has purchased or reviewed
function getUserPreferredCategories($conn, $cid) {
    $query = "SELECT DISTINCT p.category_id 
              FROM Orders o 
              JOIN OrderItems oi ON o.order_id = oi.order_id 
              JOIN Products p ON oi.product_id = p.product_id 
              WHERE o.cid = ? 
              UNION 
              SELECT DISTINCT p.category_id 
              FROM Reviews r 
              JOIN Products p ON r.product_id = p.product_id 
              WHERE r.cid = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ii", $cid, $cid);
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

    $query = "SELECT product_id, name, description, price, image_url 
              FROM Products 
              WHERE category_id IN ($category_id_str) 
              ORDER BY RAND() 
              LIMIT ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

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

    // Title for Recommended Products
    echo '<h2 class="recommended-title">Recommended Products</h2>';

    // Container for the grid layout of products
    echo '<div class="product-grid">';
    foreach ($products as $product) {
        $image_url = htmlspecialchars($product['image_url']);
        $product_id = htmlspecialchars($product['product_id']);
        $product_name = htmlspecialchars($product['name']);
        $product_description = htmlspecialchars($product['description']);
        // $stock =  htmlspecialchars($product['stock']);
        $product_price = number_format($product['price'], 2);
    
        echo '
        <div class="product-card">
            <div class="product-image">
                <!-- Display the product image -->
                <img src="img/' . $image_url . '" alt="' . $product_name . '" class="img-fluid">
            </div>
            <div class="product-details">
                <h3>' . $product_name . '</h3>
                <p>' . $product_description . '</p>
                <p><strong>Price: NRs ' . $product_price . '</strong></p>
            </div>
        </div>';
    }
    // echo '</div>';
    
    echo '</div>';  // End of product grid
}



// Step 4: Putting it all together (e.g., when a user visits the homepage)
$user_id = $_SESSION['cid']; // Retrieve the logged-in user ID from the session

// Check if user is logged in
if (!isset($user_id)) {
    die("User not logged in.");
}

// Get the user's preferred categories
$preferred_categories = getUserPreferredCategories($conn, $user_id);

// Get recommended products based on categories
$recommended_products = getRecommendedProducts($conn, $preferred_categories, 10);

// Display the recommended products
displayRecommendedProducts($recommended_products);





// You can uncomment this and fetch the products when ready
// $stmt = $conn->prepare("SELECT * FROM products");
// $stmt->execute();
// $products = $stmt->get_result();
?>



<!------------------ FRONTEND ------------------->
<!-- <!DOCTYPE html>
<html lang="en">
<head>
   Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Dashboard</title>
</head>
<body> -->

    <!-- Display Welcome Message -->
    <!-- <h2 class="text-center mt-4">Welcome,  //htmlspecialchars($email); ?></h2> -->
    
    

    <!-- Option 1: Bootstrap Bundle with Popper 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
</html> -->
