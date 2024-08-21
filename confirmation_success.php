<?php
session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลตะกร้าสินค้า
$customer_id = $_SESSION['customer_id'];
$cart_query = "SELECT * FROM cart WHERE customer_id = $customer_id ORDER BY created_at DESC LIMIT 1";
$cart_result = mysqli_query($conn, $cart_query);
$cart = mysqli_fetch_assoc($cart_result);
$cart_id = $cart['cart_id'];

// ตรวจสอบสถานะการอัปโหลดสลิป
$check_query = "SELECT * FROM cart WHERE cart_id = $cart_id";
$check_result = mysqli_query($conn, $check_query);
$cart_details = mysqli_fetch_assoc($check_result);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation Success - Meat Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Confirmation Success</h1>
    </header>

    <main>
        <section class="confirmation-success">
            <h2>Thank You for Your Payment</h2>
            <p>Your payment has been successfully processed. We have received your payment slip and your order is being reviewed.</p>
            <p>Order Details:</p>
            <ul>
                <li><strong>Order ID:</strong> <?php echo htmlspecialchars($cart_id); ?></li>
                <li><strong>Total Amount:</strong> <?php echo number_format($cart_details['total_price'], 2); ?></li>
            </ul>
            <p>We will contact you shortly for further steps. Thank you for shopping with us!</p>
            <a href="index.php">Back to Home</a>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>
