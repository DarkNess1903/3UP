<?php
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบของผู้ดูแลระบบ
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css"> <!-- ลิงก์ไปยังไฟล์ CSS -->
    <title>Admin Dashboard</title>
</head>
<body>
    <nav class="topnavbar">
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_customers.php">Manage Customers</a></li>
            <li><a href="manage_products.php">Manage Products</a></li>
            <li><a href="manage_orders.php">Manage Orders</a></li>
            <li><a href="reports.php">Reports</a></li>
            <li style="float:right"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</body>
</html>
