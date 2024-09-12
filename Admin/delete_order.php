<?php
header('Content-Type: application/json');
session_start();
include '../connectDB.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if (!isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit();
}

$order_id = intval($_POST['order_id']);
if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Order ID']);
    exit();
}
// ลบรายการสินค้าในคำสั่งซื้อ
$sql = "DELETE FROM order_details WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
if ($stmt->affected_rows <= 0) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete order details']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// ลบคำสั่งซื้อ
$sql = "DELETE FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
if ($stmt->affected_rows <= 0) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete order']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// ตรวจสอบผลลัพธ์
echo json_encode(['success' => true]);

$conn->close();
?>
