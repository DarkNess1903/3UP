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

    // ตรวจสอบว่าตะกร้าสินค้ามีอยู่ในฐานข้อมูลหรือไม่
    $check_cart_query = "SELECT * FROM cart WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $check_cart_query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_id);
    mysqli_stmt_execute($stmt);
    $check_cart_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_cart_result) === 0) {
        // ถ้าตะกร้าไม่มีอยู่ในฐานข้อมูล ให้สร้างตะกร้าใหม่อีกครั้ง
        $query = "INSERT INTO cart (customer_id) VALUES (?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $customer_id);
        if (mysqli_stmt_execute($stmt)) {
            $cart_id = mysqli_insert_id($conn);
            $_SESSION['cart_id'] = $cart_id;
        } else {
            die("Error creating cart: " . mysqli_error($conn));
        }
    } else {
        // หากตะกร้ามีอยู่แล้ว ให้ใช้ cart_id จากฐานข้อมูล
        $row = mysqli_fetch_assoc($check_cart_result);
        $cart_id = $row['cart_id'];
        $_SESSION['cart_id'] = $cart_id;
    }

    // ตรวจสอบว่ามีสินค้านี้อยู่ในตะกร้าหรือไม่
    $query = "SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $cart_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // ถ้ามีสินค้าในตะกร้าแล้ว ให้เพิ่มจำนวนสินค้า
        $query = "UPDATE cart_items SET quantity = quantity + 1 WHERE cart_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $cart_id, $product_id);
    } else {
        // ถ้ายังไม่มีสินค้าในตะกร้า ให้เพิ่มสินค้าใหม่
        $query = "INSERT INTO cart_items (cart_id, product_id, quantity, price)
        SELECT ?, p.product_id, 1, p.price 
        FROM product p 
        WHERE p.product_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $cart_id, $product_id);
    }

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
