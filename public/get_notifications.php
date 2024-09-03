<?php
session_start();
include '../connectDB.php';

$customer_id = $_SESSION['customer_id'];

$query = "SELECT * FROM notifications WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
$unread_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
    if (!$row['is_read']) {
        $unread_count++;
    }
}

echo json_encode(['notifications' => $notifications, 'unread_count' => $unread_count]);
?>
