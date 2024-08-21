<?php
session_start();
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_POST['customer_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];

// อัปเดตข้อมูลลูกค้า
$update_query = "
    UPDATE customer
    SET name = ?, email = ?, phone = ?, address = ?
    WHERE customer_id = ?
";
$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $phone, $address, $customer_id);
if (mysqli_stmt_execute($stmt)) {
    header("Location: profile.php");
} else {
    die("Error updating profile: " . mysqli_error($conn));
}

mysqli_close($conn);
?>
