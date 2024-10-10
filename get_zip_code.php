<?php
include 'connectDB.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

if (isset($_POST['id'])) {
    $district_id = $_POST['id'];

    // ดึงข้อมูลรหัสไปรษณีย์จากฐานข้อมูลตาม district_id
    $query = "SELECT POSTCODE FROM district WHERE DISTRICT_ID = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
        die("mysqli_prepare() failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $district_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo $row['POSTCODE']; // ส่งรหัสไปรษณีย์กลับไปที่ AJAX
    } else {
        echo ''; // หากไม่พบข้อมูล ให้คืนค่าว่าง
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo 'No district ID provided';
}

mysqli_close($conn);
?>
