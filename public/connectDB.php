<?php
$servername = "localhost";
$username = "root"; // ตัวอย่างชื่อผู้ใช้
$password = ""; // ตัวอย่างรหัสผ่าน (ถ้ามี)
$dbname = "meat_store";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>