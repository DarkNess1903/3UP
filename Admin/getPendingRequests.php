<?php
include 'connectDB.php';

// ดึงจำนวนคำขอที่รออยู่จากฐานข้อมูล
$query = "SELECT COUNT(*) AS pending_count FROM orders WHERE status = 'รอตรวจสอบ'";
$result = $conn->query($query);

$pendingRequests = [];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pendingRequests = $row;
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

// ส่งข้อมูลในรูปแบบ JSON
header('Content-Type: application/json');
echo json_encode($pendingRequests);
?>
