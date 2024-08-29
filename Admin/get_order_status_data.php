<?php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
include '../connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูลสถานะการสั่งซื้อ
$query = "SELECT status, COUNT(*) AS count
          FROM orders
          GROUP BY status";
$result = mysqli_query($conn, $query);

$data = array();
$labels = array();
$values = array();

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['status']; // สถานะ
    $values[] = $row['count']; // จำนวนคำสั่งซื้อในแต่ละสถานะ
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);

// ส่งข้อมูลเป็น JSON
echo json_encode(array('labels' => $labels, 'data' => $values));
?>
