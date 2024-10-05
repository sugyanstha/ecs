<?php
include('../admin/layout/adminheader.php');

// Check if the user is logged in; redirect to login page if not logged in
session_start();
if (!isset($_SESSION['adminemail'])) {
    header("location:admin_login.php");
    exit; // It's good practice to call exit after a header redirect
}
$adminname = $_SESSION['adminemail'];

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include("../database/connection.php");

// Edit Category
// if (isset($_POST['edit'])) {
//     $category_id = $_POST['category_id'];
//     header("Location: edit_category.php?category_id=" . $category_id);
//     exit; // Redirect to edit category page
// }

// Delete Category
if (isset($_POST['delete'])) {
    $category_id = $_POST['category_id'];
    $sql = "DELETE FROM categories WHERE category_id='$category_id'";
    $result = $conn->query($sql);
    if ($result) {
        header("Location: category_list.php");
        exit; // Redirect after deletion
    } else {
        die("Error: " . $conn->error);
    }
}

// Query to get the Categories List
$category_query = "SELECT * FROM categories"; // Corrected SQL query

// Execute the query
$category_result = mysqli_query($conn, $category_query);

// Check if query succeeded
if (!$category_result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<link rel="stylesheet" href="../css/adminpanel.css">
<div class="main-content">
    <h1 align="center">List of Categories</h1>
    <div class="table-wrapper">
        <!-- <form action="create_category.php" method="post">
            <input type="submit" value="Add category" name="add">
        </form> -->
        <table class="fl-table">
            <thead>
                <tr>
                    <th>Category ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($category_result && $category_result->num_rows > 0) {
                    while ($row = $category_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['category_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td> <!-- Corrected field name -->
                            <td>
                                <div class="button-row">
                                    <form method="post" action="create_category.php" style="display:inline;">
                                        <!-- <input type="hidden" value="<?php //echo $row['category_id']; ?>" name="category_id" /> -->
                                        <input type="submit" value="Add category" name="add" />
                                    </form>
                                    <form method="post" action=" " style="display:inline;">
                                        <input type="hidden" value="<?php echo $row['category_id']; ?>" name="category_id" />
                                        <input type="submit" value="Delete" name="delete" onclick="return confirm('Are you sure you want to delete this category?');" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="3">No Category Listed.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
