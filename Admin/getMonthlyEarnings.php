<?php
include '../connectDB.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูลรายได้รายเดือนจากฐานข้อมูล
$query = "SELECT SUM(total_amount) AS earnings FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')";
$result = $conn->query($query);

$monthlyEarnings = 0;

if ($result) {
    if ($row = $result->fetch_assoc()) {
        $monthlyEarnings = $row['earnings'];
    }
} else {
    echo "Error executing query: " . $conn->error;
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

echo number_format($monthlyEarnings, 2);
?>
