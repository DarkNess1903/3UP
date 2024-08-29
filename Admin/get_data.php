<?php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
include '../connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูลยอดขายรายเดือน
$query = "SELECT MONTHNAME(order_date) AS month, SUM(total_amount) AS total
          FROM orders
          WHERE status = 'เสร็จสมบรูณ์'
          GROUP BY MONTH(order_date)
          ORDER BY MONTH(order_date)";
$result = mysqli_query($conn, $query);

$data = array();
$labels = array();
$values = array();

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['month']; // เดือน
    $values[] = $row['total']; // จำนวนเงิน
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);

// ส่งข้อมูลเป็น JSON
echo json_encode(array('labels' => $labels, 'data' => $values));
?>
