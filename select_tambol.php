<?php
session_start();
include 'connectDB.php';

if (isset($_POST['id'])) {
    $amphur_id = mysqli_real_escape_string($conn, $_POST['id']);
    $query = "SELECT DISTRICT_ID, DISTRICT_NAME FROM district WHERE AMPHUR_ID = '$amphur_id'";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        echo '<option value="">เลือกตำบล</option>'; // Option for default selection
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value=\"{$row['DISTRICT_ID']}\">{$row['DISTRICT_NAME']}</option>";
        }
    } else {
        echo '<option value="">ไม่สามารถดึงข้อมูลตำบลได้</option>';
    }
}
?>