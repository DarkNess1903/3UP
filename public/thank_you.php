<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบการสั่งซื้อล่าสุด
$customer_id = $_SESSION['customer_id'];

// ค้นหาคำสั่งซื้อล่าสุดของลูกค้า
$order_query = "SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if (!$order_result || mysqli_num_rows($order_result) == 0) {
    die("No order found.");
}

$order = mysqli_fetch_assoc($order_result);
$order_id = $order['order_id'];
$total_amount = $order['total_amount'];
$payment_slip = $order['payment_slip'];

// ดึงข้อมูลการสั่งซื้อทั้งหมด
$order_details_query = "SELECT p.name, p.image, od.quantity, od.price, (od.quantity * od.price) AS total
                        FROM orderdetails od
                        JOIN product p ON od.product_id = p.product_id
                        WHERE od.order_id = ?";
$stmt = mysqli_prepare($conn, $order_details_query);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order_details_result = mysqli_stmt_get_result($stmt);

if (!$order_details_result) {
    die("Error fetching order details: " . mysqli_error($conn));
}

// คำนวณยอดรวม
$grand_total = 0;
while ($detail = mysqli_fetch_assoc($order_details_result)) {
    $grand_total += $detail['total'];
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link rel="stylesheet" href="styles.css"> <!-- Assuming you have a CSS file -->
</head>
<body>
    <header>
        <h1>Thank You for Your Order!</h1>
    </header>
    <main>
        <section>
            <h2>Order Confirmation</h2>
            <p>Your order has been successfully placed. Here are the details:</p>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($total_amount, 2); ?></p>
            <?php if ($payment_slip): ?>
                <p><strong>Payment Slip:</strong> <a href="<?php echo htmlspecialchars($payment_slip); ?>" target="_blank">View Payment Slip</a></p>
            <?php endif; ?>
            <h3>Order Details</h3>
            <ul>
                <?php while ($detail = mysqli_fetch_assoc($order_details_result)): ?>
                    <li>
                        <img src="
                        <?php echo htmlspecialchars($detail['image']); ?>" alt="
                        <?php echo htmlspecialchars($detail['name']); ?>" width="100">
                        <p><?php echo htmlspecialchars($detail['name']); ?> - Quantity: 
                        <?php echo htmlspecialchars($detail['quantity']); ?> - Price: $
                        <?php echo number_format($detail['price'], 2); ?> - Total: $
                        <?php echo number_format($detail['total'], 2); ?></p>
                    </li>
                <?php endwhile; ?>  
            </ul>
        </section>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Company. All rights reserved.</p>
    </footer>
</body>
</html>
