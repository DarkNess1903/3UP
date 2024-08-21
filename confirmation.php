<?php
session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลตะกร้าสินค้า
$cart_query = "SELECT * FROM orderdetails WHERE customer_id = $customer_id ORDER BY created_at DESC LIMIT 1";
$cart_result = mysqli_query($conn, $cart_query);

if (!$cart_result || mysqli_num_rows($cart_result) == 0) {
    die("No cart found.");
}

$cart = mysqli_fetch_assoc($cart_result);
$cart_id = $cart['cart_id'];

// ตรวจสอบการอัปโหลดสลิป
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['slip'])) {
    $file_name = $_FILES['slip']['name'];
    $file_tmp = $_FILES['slip']['tmp_name'];
    $file_size = $_FILES['slip']['size'];
    $file_error = $_FILES['slip']['error'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // กำหนดประเภทไฟล์ที่อนุญาต
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    $max_file_size = 5 * 1024 * 1024; // 5MB

    // ตรวจสอบประเภทไฟล์
    if (in_array($file_ext, $allowed_exts)) {
        // ตรวจสอบข้อผิดพลาด
        if ($file_error === 0) {
            // ตรวจสอบขนาดไฟล์
            if ($file_size <= $max_file_size) {
                // กำหนดตำแหน่งที่เก็บไฟล์
                $upload_dir = __DIR__ . '/uploads/slips/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file_path = $upload_dir . uniqid('', true) . '.' . $file_ext;

                // อัปโหลดไฟล์
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // อัปเดตสถานะการชำระเงินในฐานข้อมูล
                    $update_query = "UPDATE cart SET payment_status = 'Pending', slip_path = '$file_path' WHERE cart_id = $cart_id";
                    if (mysqli_query($conn, $update_query)) {
                        mysqli_close($conn);
                        header("Location: confirmation_success.php"); // เปลี่ยนไปที่หน้าแจ้งเตือนหลังจากอัปโหลดสลิปสำเร็จ
                        exit();
                    } else {
                        die("Error updating payment status: " . mysqli_error($conn));
                    }
                } else {
                    die("Failed to upload file.");
                }
            } else {
                die("File size exceeds the limit of 5MB.");
            }
        } else {
            die("Error uploading file: " . $file_error);
        }
    } else {
        die("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Meat Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Confirmation</h1>
    </header>

    <main>
        <section class="confirmation">
            <h2>Confirm Your Payment</h2>
            <form action="confirmation.php" method="post" enctype="multipart/form-data">
                <p>Please upload the slip of your payment.</p>
                <input type="file" name="slip" accept="image/*" required>
                <input type="submit" value="Upload Slip">
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>
