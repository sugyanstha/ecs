<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">JNTHREADS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="../dashboard.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="http://localhost/ecs/customer/view_category.php">Category</a>
                </li>

                <!-- Category Dropdown -->
                <!-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Category
                    </a> -->
                    <!-- <ul class="dropdown-menu" aria-labelledby="navbarDropdown"> -->
                        <?php
                        // Database connection
                        // include('database/connection.php');

                        // Fetch categories from database
                        // $sql = "SELECT * FROM categories";
                        // $result = $conn->query($sql);

                        // if ($result) {
                        //     if ($result->num_rows > 0) {
                        //         while ($row = $result->fetch_assoc()) {
                        //             echo '<li><a class="dropdown-item" href="view_category.php?category_id=' . $row['category_id'] . '">' . htmlspecialchars($row['name']) . '</a></li>';
                        //         }
                        //     } else {
                        //         echo '<li><a class="dropdown-item" href="#">No categories available</a></li>';
                        //     }
                        // } else {
                        //     echo '<li><a class="dropdown-item" href="#">Error fetching categories: ' . $conn->error . '</a></li>';
                        // }
                        ?>
                    <!-- </ul> -->
                <!-- </li> -->

                <!-- Static Navigation Items (Products, Orders, Profile, etc.) -->
                <li class="nav-item">
                    <a class="nav-link active" href="http://localhost/ecs/customer/view_product.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="http://localhost/ecs/customer/myorders.php">My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="http://localhost/ecs/customer/view_profile.php">My Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="http://localhost/ecs/logout.php">Logout</a>
                </li>
            </ul>
            <form class="d-flex" role="search" id="searchForm" action="customer/search_results.php" method="get">
                <input class="form-control me-2" type="search" id="serachInput" placeholder="Search products..." aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>

<script src="../js/search.js"></script>
</body>
</html>
