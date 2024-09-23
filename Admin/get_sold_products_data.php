<?php
include '../connectDB.php';

if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}

// Query to get the total quantity of products sold
$sql = "
    SELECT SUM(orderdetails.quantity) AS totalSold
    FROM orderdetails
    INNER JOIN orders ON orderdetails.order_id = orders.order_id
    WHERE orders.status = 'เสร็จสิ้น'
";

$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalSold = $row['totalSold'];
    // Handle null value
    $totalSold = $totalSold ? $totalSold : 0;
    echo json_encode(['totalSold' => $totalSold]);
} else {
    echo json_encode(['totalSold' => 0]);
}

mysqli_close($conn);
?>
