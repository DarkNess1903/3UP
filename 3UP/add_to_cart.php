<?php
session_start();
include 'connectDB.php';

// ตรวจสอบว่ามีการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// รับ product_id จาก query string
$product_id = intval($_GET['product_id']);

// ตรวจสอบว่ามีการเลือกสินค้าหรือไม่
if ($product_id > 0) {
    // ตรวจสอบว่าเข้าสู่ระบบแล้วหรือยัง
    if (!isset($_SESSION['customer_id'])) {
        die("Please log in to add items to your cart.");
    }

    // รับ customer_id จาก session
    $customer_id = $_SESSION['customer_id'];

    // ตรวจสอบว่ามีการสร้างตะกร้าสินค้าใน session หรือไม่
    if (!isset($_SESSION['cart_id'])) {
        // สร้างตะกร้าสินค้าใหม่
        $query = "INSERT INTO Cart (customer_id) VALUES ($customer_id)";
        if (mysqli_query($conn, $query)) {
            $_SESSION['cart_id'] = mysqli_insert_id($conn);
        } else {
            die("Error creating cart: " . mysqli_error($conn));
        }
    }

    $cart_id = $_SESSION['cart_id'];

    // ตรวจสอบว่ามีสินค้านี้อยู่ในตะกร้าหรือไม่
    $query = "SELECT * FROM cart_items WHERE cart_id = $cart_id AND product_id = $product_id";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // ถ้ามีสินค้าในตะกร้าแล้ว ให้เพิ่มจำนวนสินค้า
        $query = "UPDATE cart_items SET quantity = quantity + 1 WHERE cart_id = $cart_id AND product_id = $product_id";
    } else {
        // ถ้ายังไม่มีสินค้าในตะกร้า ให้เพิ่มสินค้าใหม่
        $query = "INSERT INTO cart_items (cart_id, product_id, quantity, price)
        SELECT $cart_id, p.product_id, 1, p.price 
        FROM product p 
        WHERE p.product_id = $product_id";

    }

    if (mysqli_query($conn, $query)) {
        header("Location: index.php"); // กลับไปที่หน้าสินค้า
        exit();
    } else {
        die("Error adding item to cart: " . mysqli_error($conn));
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);

?>
