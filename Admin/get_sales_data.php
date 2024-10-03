<?php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
include 'connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูลยอดขายของแต่ละสินค้า
$query = "SELECT p.name AS product_name, SUM(od.quantity) AS total_sold
          FROM orderdetails od
          JOIN product p ON od.product_id = p.product_id
          GROUP BY p.product_id, p.name";
$result = mysqli_query($conn, $query);

$sales_data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $sales_data[] = $row;
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);

// ส่งข้อมูลเป็น JSON
echo json_encode($sales_data);
?>
