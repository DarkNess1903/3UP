<?php
// getOrdersInProgress.php

header('Content-Type: application/json');

include 'connectDB.php';

// SQL query to get the count of orders in progress
$sql = "SELECT COUNT(*) AS inProgress FROM orders WHERE status = 'ตรวจสอบแล้วกำลังดำเนินการ'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(array('inProgress' => $row['inProgress']));
} else {
    echo json_encode(array('inProgress' => 0));
}

$conn->close();
?>
