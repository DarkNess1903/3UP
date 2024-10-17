<?php
session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['cart_item_id'])) {
    $cart_item_id = $_GET['cart_item_id'];

    // ดึงข้อมูลสินค้าที่จะลบ
    $query = "SELECT ci.quantity, p.stock_quantity 
              FROM cart_items ci 
              JOIN product p ON ci.product_id = p.product_id 
              WHERE ci.cart_item_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result) {
        $item = mysqli_fetch_assoc($result);
        $quantity_to_remove = $item['quantity'];
        
        // อัปเดตสต็อกสินค้า
        $update_stock_query = "UPDATE product 
                                SET stock_quantity = stock_quantity + ? 
                                WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $update_stock_query);
        mysqli_stmt_bind_param($stmt, 'ii', $quantity_to_remove, $item['product_id']);
        mysqli_stmt_execute($stmt);
        
        // ลบสินค้าจากตะกร้า
        $delete_query = "DELETE FROM cart_items WHERE cart_item_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $cart_item_id);
        mysqli_stmt_execute($stmt);
    }

    // เปลี่ยนเส้นทางกลับไปที่ตะกร้าสินค้า
    header("Location: cart.php");
    exit();
}
?>
