<?php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
include 'connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูลออเดอร์ที่ยังไม่ถูกตรวจสอบ
$query = "SELECT order_id, order_date FROM orders WHERE status = 'รอตรวจสอบ'";
$result = mysqli_query($conn, $query);

$new_orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $new_orders[] = $row;
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);

// ส่งข้อมูลเป็น JSON
echo json_encode($new_orders);
?>
