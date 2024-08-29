<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงประวัติการสั่งซื้อของลูกค้าจากฐานข้อมูล
$order_query = "
    SELECT order_id, order_date, total_amount
    FROM orders
    WHERE customer_id = ?
    ORDER BY order_date DESC
";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

// ตรวจสอบว่าไม่พบข้อมูลการสั่งซื้อ
if (mysqli_num_rows($order_result) === 0) {
    echo "<p>No orders found.</p>";
    mysqli_close($conn);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/scripts.js"></script>
</head>
<body>
    <header>
        <h1>Order History</h1>
    </header>
    <main>
        <section>
            <table border="1">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($order_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['order_date']))); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>

<?php
include 'footer.php';
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
