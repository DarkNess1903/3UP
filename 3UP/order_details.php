<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$cart_id = isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0;

// ตรวจสอบความถูกต้องของ cart_id
if ($cart_id <= 0) {
    echo "Invalid Order ID.";
    exit();
}

// ดึงรายละเอียดการสั่งซื้อ
$order_query = "SELECT p.name, ci.quantity, p.price, (ci.quantity * ci.price) AS total
                FROM cart_items ci
                JOIN product p ON ci.product_id = p.product_id
                WHERE ci.cart_id = $cart_id";
$order_result = mysqli_query($conn, $order_query);

if (!$order_result) {
    echo "Error fetching order details: " . mysqli_error($conn);
    exit();
}

// ตรวจสอบว่ามีข้อมูลในผลลัพธ์หรือไม่
if (mysqli_num_rows($order_result) === 0) {
    echo "No order details found for this order.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Meat Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Order Details</h1>
    </header>

    <main>
        <section class="order-details">
            <h2>Order #<?php echo htmlspecialchars($cart_id); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($order_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['total'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="order_history.php">Back to Order History</a>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>
