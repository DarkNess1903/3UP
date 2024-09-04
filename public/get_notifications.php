<?php
session_start();
include '../connectDB.php';

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลการสั่งซื้อทั้งหมด (หรือปรับ query ตามต้องการ)
$query = "SELECT order_id, total_amount, status, order_date FROM orders WHERE customer_id = ? ORDER BY order_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
$unread_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
    if (strpos($row['status'], 'รอรับเรื่อง') !== false) {
        $unread_count++;
    }
}

// ส่งข้อมูลการแจ้งเตือนและจำนวนที่ยังไม่ได้อ่านกลับไปยังเบราว์เซอร์
echo json_encode(['notifications' => $notifications, 'unread_count' => $unread_count]);
?>
