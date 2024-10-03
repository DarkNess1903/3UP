<?php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
include 'connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die(json_encode(array('error' => "Connection failed: " . mysqli_connect_error())));
}

// ดึงข้อมูลสถานะการสั่งซื้อ
$query = "SELECT status, COUNT(*) AS count FROM orders GROUP BY status";
$result = mysqli_query($conn, $query);

$data = array();
$labels = ['รอตรวจสอบ', 'กำลังดำเนินการ', 'กำลังจัดส่ง', 'เสร็จสิ้น'];
$values = array_fill(0, count($labels), 0); // กำหนดค่าเริ่มต้นเป็น 0

while ($row = mysqli_fetch_assoc($result)) {
    $statusIndex = array_search($row['status'], $labels);
    if ($statusIndex !== false) {
        $values[$statusIndex] = $row['count']; // จำนวนคำสั่งซื้อในแต่ละสถานะ
    }
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);

// ส่งข้อมูลเป็น JSON
echo json_encode(array('labels' => $labels, 'data' => $values));
?>
