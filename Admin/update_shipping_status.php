<?php
include 'connectDB.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีการส่งข้อมูลเข้ามาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจาก AJAX
    $order_id = intval($_POST['order_id']);
    $tracking_number = trim($_POST['tracking_number']);

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($tracking_number)) {
        echo json_encode(['success' => false, 'message' => 'หมายเลขติดตามการจัดส่งไม่ถูกต้อง']);
        exit();
    }

    // อัปเดตสถานะการจัดส่งในฐานข้อมูล
    $sql = "UPDATE orders SET tracking_number = ?, status = 'กำลังจัดส่ง' WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $tracking_number, $order_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'สถานะการจัดส่งได้รับการอัปเดตเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดตสถานะการจัดส่ง']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'คำขอไม่ถูกต้อง']);
}

$conn->close();
?>
