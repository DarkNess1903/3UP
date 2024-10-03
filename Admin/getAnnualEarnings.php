<?php
include 'connectDB.php';

// ดึงข้อมูลรายได้รายปีจากฐานข้อมูล โดยนับเฉพาะออเดอร์ที่เสร็จสิ้นแล้ว
$query = "SELECT DATE_FORMAT(order_date, '%Y') AS year, SUM(total_amount) AS earnings 
          FROM orders 
          WHERE status = 'เสร็จสิ้น'
          GROUP BY DATE_FORMAT(order_date, '%Y')";
$result = $conn->query($query);

$annualEarnings = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $annualEarnings[] = $row;
    }
} else {
    echo json_encode(["error" => "Error executing query: " . $conn->error]);
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

// ส่งข้อมูลเป็น JSON
echo json_encode($annualEarnings);
?>
