<?php
session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ตรวจสอบว่าฟอร์มถูกส่งมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $province_id = intval($_POST['province_id']);
    $amphur_id = intval($_POST['amphur_id']);
    $district_id = intval($_POST['district_id']);
    $postcode = mysqli_real_escape_string($conn, $_POST['postcode']);

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($name) || empty($phone) || empty($address) || empty($province_id) || empty($amphur_id) || empty($district_id) || empty($postcode)) {
        header("Location: profile.php?update_error=1");
        exit();
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    $update_query = "
    UPDATE customer 
    SET name = ?, phone = ?, address = ?, province_id = ?, amphur_id = ?, district_id = ?
    WHERE customer_id = ?
    ";
    $stmt = mysqli_prepare($conn, $update_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sssiiii', $name, $phone, $address, $province_id, $amphur_id, $district_id, $customer_id);
        // ดำเนินการอัปเดต
        if (mysqli_stmt_execute($stmt)) {
            header("Location: profile.php?update=success");
        } else {
            header("Location: profile.php?update_error=4");
        }
        mysqli_stmt_close($stmt);
    }
 else {
        header("Location: profile.php?update_error=3");
    }
} else {
    header("Location: profile.php");
}

mysqli_close($conn);
?>
