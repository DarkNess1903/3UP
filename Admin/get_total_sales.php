<?php
include 'connectDB.php';

if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}

// Query to get the total sales
$sql = "SELECT SUM(total_amount) AS totalSales FROM orders WHERE status = 'เสร็จสิ้น'";
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalSales = $row['totalSales'];
    // Use default value of 0 if totalSales is NULL
    if ($totalSales === NULL) {
        $totalSales = 0;
    }
    echo json_encode(['totalSales' => $totalSales]);
} else {
    echo json_encode(['totalSales' => 0]);
}

mysqli_close($conn);
?>
