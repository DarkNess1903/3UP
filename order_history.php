<?php
session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลการสั่งซื้อ
$order_query = "SELECT cart_id, created_at FROM cart WHERE customer_id = $customer_id ORDER BY created_at DESC";
$order_result = mysqli_query($conn, $order_query);

if (!$order_result) {
    echo "Error fetching orders: " . mysqli_error($conn);
    exit();
}

// ตรวจสอบว่ามีข้อมูลในผลลัพธ์หรือไม่
if (mysqli_num_rows($order_result) === 0) {
    echo "No orders found for this customer.";
    mysqli_close($conn);
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Meat Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Order History</h1>
    </header>

    <main>
        <section class="order-history">
            <h2>Your Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($order_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['cart_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td>
                                <a href="order_details.php?cart_id=<?php echo $order['cart_id']; ?>">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>
