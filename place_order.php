<?php
session_start();
include 'connectDB.php';

// ตรวจสอบว่ามีการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['customer_name'], $_POST['customer_phone']) && isset($_SESSION['cart_id'])) {
    $cart_id = $_SESSION['cart_id'];
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);

    // สร้างการสั่งซื้อใหม่
    $query = "INSERT INTO Orders (cart_id, customer_name, customer_phone, order_date, status)
              VALUES ($cart_id, '$customer_name', '$customer_phone', NOW(), 'Pending')";
    
    if (mysqli_query($conn, $query)) {
        // ล้างข้อมูลในตะกร้าสินค้า
        $query = "DELETE FROM CartItems WHERE cart_id = $cart_id";
        mysqli_query($conn, $query);

        // ล้าง session ของตะกร้า
        unset($_SESSION['cart_id']);

        echo '<p>Thank you for your order! Your order has been placed successfully.</p>';
    } else {
        die("Error placing order: " . mysqli_error($conn));
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
