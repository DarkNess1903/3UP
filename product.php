<?php
session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// ตรวจสอบว่ามีตะกร้าอยู่แล้วหรือไม่
$cart_query = "SELECT * FROM cart WHERE customer_id = $customer_id ORDER BY created_at DESC LIMIT 1";
$cart_result = mysqli_query($conn, $cart_query);
$cart = mysqli_fetch_assoc($cart_result);

if ($cart) {
    $cart_id = $cart['cart_id'];
} else {
    // ถ้าไม่มีตะกร้าให้สร้างใหม่
    $create_cart_query = "INSERT INTO cart (customer_id, created_at) VALUES ($customer_id, NOW())";
    mysqli_query($conn, $create_cart_query);
    $cart_id = mysqli_insert_id($conn);
}

// เพิ่มสินค้าในตะกร้า
$insert_item_query = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES ($cart_id, $product_id, $quantity)";
mysqli_query($conn, $insert_item_query);

mysqli_close($conn);
header("Location: cart.php");
exit();
?>
