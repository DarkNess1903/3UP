<?php
// getDailySales.php

header('Content-Type: application/json');
include 'connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    echo json_encode(['error' => 'การเชื่อมต่อฐานข้อมูลล้มเหลว']);
    exit();
}

// กำหนดเขตเวลาให้ตรงกับที่ใช้ในระบบ
date_default_timezone_set('Asia/Bangkok'); // ปรับตามเขตเวลาของคุณ

// ดึงข้อมูลยอดขายย้อนหลัง 7 วัน
$query = "SELECT DATE(order_date) AS orderDay, SUM(total_amount) AS dailySales
          FROM orders
          WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            AND status = 'เสร็จสิ้น'
          GROUP BY DATE(order_date)
          ORDER BY DATE(order_date) ASC";
$result = $conn->query($query);

$dailySalesData = [];
$labels = [];

// สร้าง array สำหรับ 7 วันที่ผ่านมา
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i day"));
    $labels[] = date('d/m', strtotime($date)); // รูปแบบวันที่ที่ต้องการ
    $dailySalesData[$date] = 0;
}

// เติมยอดขายที่ดึงมา
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dailySalesData[$row['orderDay']] = (float)$row['dailySales'];
    }
}

$data = [
    'labels' => $labels,
    'dailySales' => array_values($dailySalesData)
];

echo json_encode($data);

$conn->close();
?>
