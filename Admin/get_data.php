<?php
include 'connectDB.php';

$period = $_GET['period'] ?? 'monthly'; // ค่าเริ่มต้นเป็น 'monthly'
$data = [];

switch ($period) {
    case 'daily':
        // ดึงข้อมูลรายได้จากฐานข้อมูลรายวัน
        $sql = "SELECT DATE(order_date) as order_date, SUM(total_amount) as total 
                FROM orders 
                WHERE status = 'เสร็จสิ้น' 
                GROUP BY DATE(order_date)";
        break;
    case 'weekly':
        // ดึงข้อมูลรายได้จากฐานข้อมูลรายสัปดาห์
        $sql = "SELECT YEARWEEK(order_date, 1) AS week, SUM(total_amount) AS total 
                FROM orders 
                WHERE status = 'เสร็จสิ้น' 
                GROUP BY YEARWEEK(order_date, 1)";
        break;
    case 'monthly':
        // ดึงข้อมูลรายได้จากฐานข้อมูลรายเดือน
        $sql = "SELECT MONTH(order_date) as order_month, SUM(total_amount) as total 
                FROM orders 
                WHERE status = 'เสร็จสิ้น' 
                GROUP BY MONTH(order_date)";
        break;
    case 'yearly':
        // ดึงข้อมูลรายได้จากฐานข้อมูลรายปี
        $sql = "SELECT YEAR(order_date) as order_year, SUM(total_amount) as total 
                FROM orders 
                WHERE status = 'เสร็จสิ้น' 
                GROUP BY YEAR(order_date)";
        break;
}

$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if ($period == 'daily') {
        $data['labels'][] = $row['order_date'];
    } elseif ($period == 'weekly') {
        // เปลี่ยนหมายเลขสัปดาห์เป็นวันที่เริ่มต้นของสัปดาห์นั้น
        $data['labels'][] = date("Y-m-d", strtotime("{$row['week']}-1")); // สัปดาห์เริ่มต้นวันจันทร์
    } elseif ($period == 'monthly') {
        $data['labels'][] = $row['order_month'];
    } elseif ($period == 'yearly') {
        $data['labels'][] = $row['order_year'];
    }
    $data['data'][] = (float)$row['total'];
}

header('Content-Type: application/json');
echo json_encode($data);
