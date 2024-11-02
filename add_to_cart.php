<?php
session_start();
include 'connectDB.php';

// ตรวจสอบว่ามีการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// รับ product_id และ unit จาก query string
$product_id = intval($_GET['product_id']);
$unit = $_GET['unit'] ?? ''; // รับค่าจากฟอร์ม

// ตรวจสอบว่ามีการเลือกสินค้าหรือไม่
if ($product_id > 0) {
    // ตรวจสอบว่ามีการเข้าสู่ระบบหรือยัง
    if (!isset($_SESSION['customer_id'])) {
        header("Location: login.php");
        exit();
    }

    // รับ customer_id จาก session
    $customer_id = $_SESSION['customer_id'];

    // ตรวจสอบว่ามีการสร้างตะกร้าสินค้าใน session หรือไม่
    if (!isset($_SESSION['cart_id'])) {
        // สร้างตะกร้าสินค้าใหม่
        $query = "INSERT INTO cart (customer_id) VALUES (?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $customer_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['cart_id'] = mysqli_insert_id($conn);
        } else {
            die("Error creating cart: " . mysqli_error($conn));
        }
    }

    $cart_id = $_SESSION['cart_id'];

    // ดึงข้อมูลน้ำหนักต่อชิ้นจากฐานข้อมูล
    $product_query = "SELECT weight_per_item FROM product WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $product_query);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $product_result = mysqli_stmt_get_result($stmt);
    $product_data = mysqli_fetch_assoc($product_result);

    // กำหนดจำนวนสินค้าตามที่ผู้ใช้เลือก
    $quantity = 0;
    $weight_in_grams = 0; // เพิ่มตัวแปรสำหรับน้ำหนักรวม
    if ($product_data) {
        $weight_per_piece = $product_data['weight_per_item']; // น้ำหนักของ 1 ชิ้น (กรัม)

        // หากเลือก 1 กก ให้เพิ่มเป็นจำนวนชิ้นตามน้ำหนัก
        if ($unit === '1kg') {
            $quantity = 1000 / $weight_per_piece; // แปลง 1 กิโลกรัมเป็นจำนวนชิ้น
            $weight_in_grams = 1000; // น้ำหนักรวมเป็น 1000 กรัม
        } elseif ($unit === '1piece') {
            $quantity = 1; // เพิ่ม 1 ชิ้น
            $weight_in_grams = $weight_per_piece; // น้ำหนักรวมเป็นน้ำหนักของ 1 ชิ้น
        }
    }

    // ตรวจสอบว่ามีสินค้านี้อยู่ในตะกร้าหรือไม่
    $query = "SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $cart_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // ถ้ามีสินค้าในตะกร้าแล้ว ให้เพิ่มจำนวนสินค้าและอัปเดตน้ำหนักรวม
        $query = "UPDATE cart_items SET quantity = quantity + ?, weight_in_grams = weight_in_grams + ? WHERE cart_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iiii', $quantity, $weight_in_grams, $cart_id, $product_id);
    } else {
        // ถ้ายังไม่มีสินค้าในตะกร้า ให้เพิ่มสินค้าใหม่
        $query = "INSERT INTO cart_items (cart_id, product_id, quantity, price, weight_in_grams)
        SELECT ?, p.product_id, ?, p.price, ? 
        FROM product p 
        WHERE p.product_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iiii', $cart_id, $quantity, $weight_in_grams, $product_id);
    }    

    // Execute the statement to add or update the item in the cart
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php"); // กลับไปที่หน้าสินค้า
        exit();
    } else {
        die("Error adding item to cart: " . mysqli_error($conn));
    }
} else {
    die("Invalid product ID.");
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
