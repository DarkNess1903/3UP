<?php
session_start();
include '../connectDB.php';

$customer_id = $_SESSION['customer_id'];

// ดึงการแจ้งเตือนล่าสุดจากตาราง orders
$query = "SELECT order_id, order_date, total_amount, status 
          FROM orders 
          WHERE customer_id = ? AND status = '' 
          ORDER BY order_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = [
        'order_id' => $row['order_id'],
        'order_date' => $row['order_date'],
        'total_amount' => $row['total_amount'],
        'status' => $row['status'],
        'link' => "view_order.php?order_id=" . $row['order_id']
    ];
}

$response = [
    'count' => count($notifications),
    'notifications' => $notifications
];

echo json_encode($response);
?>
