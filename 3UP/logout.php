<?php
session_start();
session_unset(); // ล้าง session
session_destroy(); // ทำลาย session
header("Location: login.php"); // เปลี่ยนเส้นทางไปยังหน้าเข้าสู่ระบบ
exit();
?>
