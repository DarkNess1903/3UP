<?php
// earnings_data.php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูลที่มีสถานะ "เสร็จสมบูรณ์"
$sql = "SELECT DATE(order_date) AS date, SUM(total_amount) AS earnings 
        FROM orders 
        WHERE status = 'เสร็จสมบูรณ์'
        GROUP BY DATE(order_date) 
        ORDER BY DATE(order_date)";
$result = $conn->query($sql);

$data = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// ส่งข้อมูลเป็น JSON
echo json_encode($data);

$conn->close();
?>
