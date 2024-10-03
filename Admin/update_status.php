<?php
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
include 'connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit();
}

// ตรวจสอบว่าได้รับค่า order_id หรือไม่
if (!isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$order_id = intval($_POST['order_id']);

// อัปเดตสถานะคำสั่งซื้อเป็น 'ตรวจสอบสลิปแล้วและกำลังดำเนินการ'
$query = "UPDATE orders SET status = 'เสร็จสิ้น' WHERE order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}

$stmt->close();
$conn->close();
?>
