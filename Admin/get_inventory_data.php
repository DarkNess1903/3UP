<?php
header('Content-Type: application/json');
include 'connectDB.php'; // รวมไฟล์การเชื่อมต่อฐานข้อมูล

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

// สร้างคำสั่ง SQL เพื่อดึงข้อมูลสินค้าคงคลัง
$sql = "SELECT name AS product_name, stock_quantity FROM product";
$result = mysqli_query($conn, $sql);

$data = [];

if (mysqli_num_rows($result) > 0) {
    // ดึงข้อมูลแต่ละแถวและเพิ่มไปยัง array
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
} else {
    // ไม่มีข้อมูล
    $data = [];
}

// ส่งข้อมูลกลับเป็น JSON
echo json_encode($data);

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
