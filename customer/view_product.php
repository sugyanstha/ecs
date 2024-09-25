<?php
include('customer/layout/cheader.php');
include('../database/connection.php');
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// session_start();




//Add Guidelines (to cart)
if(isset($_POST['addtocart'])){    //addtocart
    $cart_id = $_POST['cart_id'];   //cart_id

    // Add Guidelines
    if (isset($_POST['addtocart'])) {     //addtocart
        $product_id = $_POST['product_id'];    //product_id
        $name = $_POST['name'];   //product name
        $description = $_POST['description'];   //product description
        $price = $_POST['price'];   //price
        $quantity = $_POST['quantity'];   //price
        // $image_url = $_POST['image'];   //image

        // Sanitize inputs
        $name = $conn->real_escape_string($name);
        $description = $conn->real_escape_string($description);
        $price = $conn->real_escape_string($price);
        // $image_url = $conn->real_escape_string($image_url);

        // $sql = "INSERT INTO guidelines (counsellor_id, title, predicament_id, description) VALUES ('$counsellor_id', '$title', '$pid', '$description')";
        $sql = "INSERT INTO cart (product_id, name, description, price , quantity) VALUES
        ('$product_id', '$name', '$description', '$price', '$quantity')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Product addd to cart successfully')</script>";
            header("Location: cart.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}


// Fetch Predicament (fetch product)
if (isset($_SESSION['product_ id'])) { // Check if $_SESSION['id'] is set
    // $sql = "SELECT * FROM predicament";
    $sql = "SELECT *, product.name as product_name FROM product INNER JOIN cart ON cart.product_id = product.id";

    $result = $conn->query($sql);
}
?>
<!-- WHERE farmer_id = '" . $_SESSION['id'] . "' -->
<link rel="stylesheet" href="../css/table.css">
<div class="con">
    <h1>Products</h1>
    <div class="table-wrapper">
        <!-- <form action="add_guidelines.php" method="post">
            <input type="submit" value="Add Guidelines" name="add">
        </form> -->
        <table class="fl-table">
            <tbody>
                <tr>
                    <th width=10% >SN</th>
                    <th width=20% >Farmer Name</th>   <!--product name-->
                    <th width=25% >Title</th>
                    <th width=40% >Description</th>
                    <!-- <th>Submitted Date</th> -->
                    <th width=10% >Action</th>
                </tr>
                <?php if (isset($result) && $result->num_rows > 0) { // Check if $result is set
                    $i = 1;
                    while ($row = $result->fetch_assoc()) { 
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['farmer_name']; ?></td>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['description']; ?></td>
                            <!-- <td><?php //echo $row['submitted_date']; ?></td> -->
                            <td>
                            <!-- <form method="post" action="../counsellor/add_guidelines.php">
                                <input type="hidden" value="<?php //echo $row['pid']; ?>" name="pid" />
                                <input type="submit" value="Update" name="edit_guidelines" />
                            </form> -->

                            <form method="post" action="add_guidelines.php">    <!-- cart.php -->
                                <input type="hidden" value="<?php echo $row['pid']; ?>" name="pid" />   <!-- product_id -->
                                <input type="submit" value="Add Guidelines" name="add_guidelines" />    <!-- Add to Cart -->
                            </form>

                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="5">No Product to display.</td>  <!-- No Product Found -->
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

