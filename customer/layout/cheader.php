<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script></head>
    <script src="js/dropdown.js"></script>
    <script src="js/multipledropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <!-- <style>
        .dropdown-submenu {
            position: relative;
        }
        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-left: .1rem;
            margin-right: .1rem;
        }
    </style> -->
<body>
<!------------- FOR NAVBAR -------------->
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">JNTHREADS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="../dashboard.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="../customer/view_product.php">Products</a>
                </li>
                    <!-- <ul class="dropdown-menu">
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="">Shop Men</a>
                            <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="category.php">T-shirts</a></li>
                            <li><a class="dropdown-item" href="category.php">Shirts</a></li>
                            <li><a class="dropdown-item" href="category.php">SweatShirts</a></li>
                            <li><a class="dropdown-item" href="category.php">Jackets</a></li>
                            <li><a class="dropdown-item" href="category.php">Hoodies</a></li>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Shop Women</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="category.php">T-shirts</a></li>
                                <li><a class="dropdown-item" href="category.php">Shirts</a></li>
                                <li><a class="dropdown-item" href="category.php">SweatShirts</a></li>
                                <li><a class="dropdown-item" href="category.php">Jackets</a></li>
                                <li><a class="dropdown-item" href="category.php">Hoodies</a></li>
                            </ul>
                        </li>
                    </ul>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="customer/myorders.php">My orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="customer/cart.php">Shopping Cart</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="customer/profile.php">My Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="logout.php">Logout</a>
                </li>
            </ul>
            <form class="d-flex" role="search">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>

