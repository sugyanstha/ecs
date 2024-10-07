<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
<style>

.header{
    /* min-height: 10vh; */
    width: 100%;
    background-color: #333;
    background-position: center;
    background-size: cover;
    position: relative;
}
nav{
    display: flex;
    padding: 1% 3%;
    justify-content: space-between;
    align-items: center;
}
nav img{
    width: 100px;
    height: 50px;
}
a{
    text-decoration: none; /* Remove underline for anchor links */
    color: #fff; /* Text color for links */
}
strong{
    color: #fff; /* Text color */
    font-size: 20px; /* Font size */
    font-weight: bold; /* Make the text bold */
    text-transform: uppercase; /* Transform text to uppercase */
    letter-spacing: 1px; /* Space between letters */
    transition: color 0.3s ease; /* Smooth transition for hover effect */}
h1{
    padding: 0px 355px 30px; /*top left&right bottom*/
    color: #fff;
    font-size: 30px;
    font-weight: bold;
}
.nav-links{
    flex: 1;
    text-align: right;
}
.nav-links ul li{
    list-style: none;
    display: inline-block;
    padding: 8px 12px;
    position: relative; 
}
.nav-links ul li a{
    color: #fff;
    text-decoration: none;
    font-size: 13px;
}
.nav-links ul li::after{
    content: '';
    width: 0%;
    height: 2px;
    background: #f44336;
    display: block;
    margin: auto;
    transition: 0.5s;
}
.nav-links ul li:hover::after{
    width: 100%;
}
</style>
<body>
<!------------- FOR NAVBAR -------------->
<section class="header">
    <nav>
        <a href="admin_panel.php">
            <!-- <img src="../img/JN_logo.jpg" alt="logo"> -->
            <strong>JNTHREADS</strong>
        </a>
        <!-- <div class="title">
            <h1 align="center">ADMIN PANEL</h2>
        </div> -->
        <div class="nav-links">
            <ul>
                <li><a href="product_list.php">PRODUCTS</a></li>
                <li><a href="category_list.php">CATEGORIES</a></li>
                <li><a href="admin_panel.php">ORDERS</a></li>
                <li><a href="stock.php">CHECK STOCK</a></li>
                <li><a href="view_review.php">REVIEW</a></li>
                <li><a href="customer_list.php">CUSTOMERS</a></li>
                <li><a href="admin_logout.php">LOGOUT</a></li>
            </ul>
        </div>
    </nav>
</section>
</body>
</html>

