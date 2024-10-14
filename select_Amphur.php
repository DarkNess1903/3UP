<?php
session_start();
include 'connectDB.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['id'])) {
    $province_id = mysqli_real_escape_string($conn, $_POST['id']);
    
    // ดึงข้อมูลอำเภอจากฐานข้อมูล
    $query = "SELECT AMPHUR_ID AS amphurID, AMPHUR_NAME AS amphurName FROM amphur WHERE PROVINCE_ID = '$province_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<option value="">เลือกอำเภอ/เขต</option>'; // Option for default selection
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['amphurID'] . '">' . $row['amphurName'] . '</option>';
        }
    } else {
        echo '<option value="">ไม่พบอำเภอในจังหวัดนี้</option>';
    }
}

mysqli_close($conn);
?>
