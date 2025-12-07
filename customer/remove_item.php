<?php
session_start();

if (!isset($_SESSION['cid'])) {
    header("Location: login.php");
    exit();
}

$cid = (int) $_SESSION['cid']; // logged in customer id

require_once 'cart_functions.php';

if (!isset($_GET['id'])) {
    header("Location: cart.php");
    exit();
}

$cart_item_id = (int) $_GET['id'];
remove_item($cid, $cart_item_id);

header("Location: cart.php");
exit();
