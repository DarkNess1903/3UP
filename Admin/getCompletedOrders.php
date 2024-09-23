<?php
// getCompletedOrders.php

header('Content-Type: application/json');
include '../connectDB.php';

// ดึงจำนวนคำสั่งซื้อที่เสร็จสิ้นจากฐานข้อมูล
$query = "SELECT COUNT(*) AS completedOrders FROM orders WHERE status = 'เสร็จสิ้น'";
$result = $conn->query($query);

$completedOrders = [];

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $completedOrders = $row;
} else {
    $completedOrders = ['completedOrders' => 0];
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

// ส่งข้อมูลในรูปแบบ JSON
echo json_encode($completedOrders);
?>
