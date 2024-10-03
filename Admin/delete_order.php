<?php
include 'connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// รับ order_id จากการร้องขอ
$order_id = $_POST['order_id'];

// ตรวจสอบว่า order_id ถูกส่งมา
if (isset($order_id)) {
    // ลบคำสั่งซื้อจากตาราง orders และ orderdetails
    $query = "DELETE FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        // ลบรายการคำสั่งซื้อจาก orderdetails
        $query_details = "DELETE FROM orderdetails WHERE order_id = ?";
        $stmt_details = $conn->prepare($query_details);
        $stmt_details->bind_param("i", $order_id);
        $stmt_details->execute();

        // ส่งผลลัพธ์กลับไปยัง Ajax
        echo json_encode(array('success' => true));
    } else {
        // ส่งข้อความผิดพลาด
        echo json_encode(array('success' => false, 'message' => 'ไม่สามารถลบคำสั่งซื้อได้'));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'ไม่พบหมายเลขคำสั่งซื้อ'));
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);
?>
