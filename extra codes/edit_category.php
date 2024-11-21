<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include files
// include('layout/header.php');
// include('layout/left.php');

// Database connection
include('../database/connection.php');

// Edit farm
if (isset($_POST['edit'])) {
    $categpry_name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $sql = "UPDATE categories SET name='$category_name' WHERE category_id='$category_id'";
    $result = $conn->query($sql);
    if ($result) {
        header("Location: category_list.php");
        exit;
    } else {
        die("Error: " . $conn->error);
    }
}

// View farm details
if (isset($_POST['edit'])) {
    $catgeory_id = $_POST['category_id'];
    $sql = "SELECT * FROM categories WHERE category_id='$category_id'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $category_name = $row['name'];
    } else {
        die("No category found with the given ID.");
    }
}
?>

<!-- <link rel="stylesheet" href="css/myfarm.css"> -->

            <form action=" " method="post">
                <div class="container">
                    <h2>Edit Category</h2>
                        <label for="category">Category Name:</label>
                        <input type="text" name="name" placeholder="Enter Category Name" required>
                        <input type="hidden" value="<?php echo $category_id; ?>" name="category_id">
                        <input type="submit" value="Update" name="edit" />
                </div>
            </form>
            <a href="category_list.php">Back</a>
            </form>