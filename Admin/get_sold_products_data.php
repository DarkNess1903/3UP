<?php
include 'connectDB.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die(json_encode(['error' => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error()]));
}

// คำสั่ง SQL เพื่อดึงจำนวนคำสั่งซื้อที่เสร็จสิ้น
$sql = "SELECT COUNT(DISTINCT orders.order_id) AS totalSold
        FROM orders
        WHERE orders.status = 'เสร็จสิ้น'";

$result = mysqli_query($conn, $sql);

// ตรวจสอบผลลัพธ์จากการ query
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalSold = $row['totalSold'] ?? 0; // ใช้ null coalescing operator เพื่อตั้งค่าเป็น 0 ถ้า null
    echo json_encode(['totalSold' => $totalSold]);
} else {
    // ส่งข้อความผิดพลาดเมื่อ query ล้มเหลว
    echo json_encode(['error' => "เกิดข้อผิดพลาดในการดึงข้อมูล: " . mysqli_error($conn)]);
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>  
