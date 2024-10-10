<?php
$servername = "localhost";
$username = "nueahom_meat_store"; // ตัวอย่างชื่อผู้ใช้
$password = "1234567890"; // ตัวอย่างรหัสผ่าน (ถ้ามี)
$dbname = "nueahom_meat_store";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>