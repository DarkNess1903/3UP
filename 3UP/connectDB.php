<?php
$servername = "localhost";
$username = "root"; // ตัวอย่างชื่อผู้ใช้
$password = ""; // ตัวอย่างรหัสผ่าน (ถ้ามี)
$dbname = "meat_store";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
