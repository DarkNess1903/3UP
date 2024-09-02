<?php
session_start();
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// รับค่าจากฟอร์ม
$customer_id = $_POST['customer_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);

// ตรวจสอบความถูกต้องของข้อมูลที่ส่งมา
if (empty($name) || empty($email) || empty($phone) || empty($address)) {
    die("All fields are required.");
}

// ตรวจสอบอีเมลให้ถูกต้อง
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format.");
}

// เตรียมคำสั่ง SQL สำหรับอัปเดตข้อมูลลูกค้า
$update_query = "
    UPDATE customer
    SET name = ?, email = ?, phone = ?, address = ?
    WHERE customer_id = ?
";
$stmt = mysqli_prepare($conn, $update_query);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

// ผูกค่าจากฟอร์มเข้ากับคำสั่ง SQL
mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $phone, $address, $customer_id);  

// ดำเนินการอัปเดตข้อมูลลูกค้า
if (mysqli_stmt_execute($stmt)) {
    header("Location: profile.php?update=success");
    exit();
} else {
    $error = mysqli_error($conn);
    echo "Error updating profile: " . $error;
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
