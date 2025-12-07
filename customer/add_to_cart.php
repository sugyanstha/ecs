<?php
session_start();

if (!isset($_SESSION['cid'])) {
    header("Location: login.php");
    exit();
}

$cid = (int) $_SESSION['cid']; // logged in customer id

require_once 'cart_functions.php';

if (!isset($_POST['product_id'], $_POST['quantity'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

add_to_cart($cid, $product_id, $quantity);

header("Location: cart.php");
exit();
