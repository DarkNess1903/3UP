<?php
include 'connectDB.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

if (isset($_POST['id'])) {
    $district_id = $_POST['id'];

    // ดึงข้อมูล POSTCODE จาก amphur โดยใช้ district_id เพื่อเชื่อมโยงกับ amphur
    $query = "SELECT a.POSTCODE AS amphur_postcode
              FROM district d
              JOIN amphur a ON d.AMPHUR_ID = a.AMPHUR_ID
              WHERE d.DISTRICT_ID = ?";
    
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
        die("mysqli_prepare() failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $district_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['amphur_postcode'])) {
            echo $row['amphur_postcode']; // ส่ง POSTCODE จาก amphur กลับไปที่ AJAX
        } else {
            echo ''; // หากไม่พบ POSTCODE ให้คืนค่าว่าง
        }
    } else {
        echo ''; // หากไม่พบข้อมูล ให้คืนค่าว่าง
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo 'No district ID provided';
}

mysqli_close($conn);
?>
