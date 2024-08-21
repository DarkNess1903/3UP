<?php
session_start();
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// ตรวจสอบจำนวนสินค้าที่มีในสต็อก
$stock_query = "SELECT stock_quantity FROM product WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $stock_query);
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$stock_result = mysqli_stmt_get_result($stmt);
$product_stock = mysqli_fetch_assoc($stock_result)['stock_quantity'];

// ตรวจสอบจำนวนสต็อกก่อนเพิ่มลงในตะกร้า
if ($quantity > $product_stock) {
    die("Not enough stock available.");
}

// ตรวจสอบว่ามีตะกร้าอยู่แล้วหรือไม่
$cart_query = "SELECT * FROM cart WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$cart_result = mysqli_stmt_get_result($stmt);
$cart = mysqli_fetch_assoc($cart_result);

if ($cart) {
    $cart_id = $cart['cart_id'];
} else {
    // ถ้าไม่มีตะกร้าให้สร้างใหม่
    $create_cart_query = "INSERT INTO cart (customer_id, created_at) VALUES (?, NOW())";
    $stmt = mysqli_prepare($conn, $create_cart_query);
    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
    mysqli_stmt_execute($stmt);
    $cart_id = mysqli_insert_id($conn);
}

// เพิ่มสินค้าในตะกร้า
$insert_item_query = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $insert_item_query);
mysqli_stmt_bind_param($stmt, 'iii', $cart_id, $product_id, $quantity);
if (!mysqli_stmt_execute($stmt)) {
    die("Error adding item to cart: " . mysqli_error($conn));
}

// ลดจำนวนสต็อกหลังจากเพิ่มสินค้าในตะกร้า
$update_stock_query = "UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $update_stock_query);
mysqli_stmt_bind_param($stmt, 'ii', $quantity, $product_id);
if (!mysqli_stmt_execute($stmt)) {
    die("Error updating stock: " . mysqli_error($conn));
}

mysqli_close($conn);
header("Location: cart.php");
exit();
?>
