<?php
session_start();
include 'connectDB.php';

// ตรวจสอบว่ามีการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// รับ product_id, unit, quantity, และ price จาก query string
$product_id = intval($_GET['product_id']);
$unit = $_GET['unit'] ?? '';
$quantity = intval($_GET['quantity']);
$price = floatval($_GET['price']); // รับราคาเป็น float

// ตรวจสอบว่ามีการเลือกสินค้าหรือไม่
if ($product_id > 0 && $quantity > 0) {
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

    // กำหนดน้ำหนักรวมตามที่ผู้ใช้เลือก
    if ($product_data) {
        $weight_per_piece = $product_data['weight_per_item']; // น้ำหนักของ 1 ชิ้น (กรัม)

        // คำนวณน้ำหนักรวมตามจำนวนที่กรอก
        if ($unit === '1kg') {
            $weight_in_grams = $quantity * 1000; // แปลงจากจำนวนกิโลกรัมเป็นกรัม
            $quantity = $weight_in_grams / $weight_per_piece; // คำนวณจำนวนชิ้นจากน้ำหนักรวม
        } elseif ($unit === '1piece') {
            $weight_in_grams = $quantity * $weight_per_piece; // น้ำหนักรวมเป็นน้ำหนักของจำนวนชิ้นที่กรอก
        }
    }
    // ตรวจสอบว่ามีสินค้าในตะกร้าแล้วหรือไม่
    $query = "SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $cart_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // ถ้ามีสินค้าในตะกร้าแล้ว
        $query = "UPDATE cart_items SET quantity = quantity + ?, weight_in_grams = weight_in_grams + ? WHERE cart_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iiii', $quantity, $weight_in_grams, $cart_id, $product_id);
    } else {
        // ถ้ายังไม่มีสินค้าในตะกร้า
        $query = "INSERT INTO cart_items (cart_id, product_id, quantity, price, weight_in_grams)
        VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iiidi', $cart_id, $product_id, $quantity, $price, $weight_in_grams);
    }

   // Execute the statement to add or update the item in the cart
   if (mysqli_stmt_execute($stmt)) {
       header("Location: index.php"); // กลับไปที่หน้าสินค้า
       exit();
   } else {
       die("Error adding item to cart: " . mysqli_error($conn));
   }
} else {
    die("Invalid product ID or quantity.");
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
