<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    die("Invalid order ID.");
}

// ดึงข้อมูลคำสั่งซื้อ
$order_query = "
    SELECT order_id, customer_id, order_date, total_amount, payment_slip, status
    FROM orders
    WHERE order_id = ?
";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($order_result) === 0) {
    die("Order not found.");
}

$order = mysqli_fetch_assoc($order_result);
$customer_id = $order['customer_id'];

// ดึงรายละเอียดสินค้า
$details_query = "
    SELECT p.name, p.image, od.quantity, od.price, (od.quantity * od.price) AS total
    FROM orderdetails od
    JOIN product p ON od.product_id = p.product_id
    WHERE od.order_id = ?
";
$stmt = mysqli_prepare($conn, $details_query);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$details_result = mysqli_stmt_get_result($stmt);

// การอัปเดตสถานะคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $update_status_query = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $update_status_query);
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // รีเฟรชหน้าเพื่อแสดงสถานะล่าสุด
        header("Location: view_order.php?order_id=" . $order_id);
        exit();
    } else {
        die("Error updating status: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css"> <!-- ใส่ลิงก์ CSS -->
</head>
<body>
    <header>
        <h1>Order Details</h1>
    </header>
    <main>
        <section>
            <h2>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h2>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['order_date']))); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
            <?php if ($order['payment_slip']): ?>
                <p><strong>Payment Slip:</strong> 
                    <a href="#" class="view-payment-slip" data-image="/upload/<?php echo htmlspecialchars(basename($order['payment_slip'])); ?>">View Payment Slip</a>
                </p>
            <?php endif; ?>

            <!-- โมดัลสำหรับแสดงภาพ -->
            <div id="myModal" class="modal">
                <span class="close">&times;</span>
                <img class="modal-content" id="img01">
                <div id="caption"></div>
            </div>

            <!-- ฟอร์มสำหรับอัปเดตสถานะคำสั่งซื้อ -->
            <form action="view_order.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>" method="post">
                <label for="status">Update Status:</label>
                <select name="status" id="status" required>
                    <option value="รอรับเรื่อง" <?php if ($order['status'] == 'รอรับเรื่อง') echo 'selected'; ?>>รอรับเรื่อง (Awaiting)</option>
                    <option value="กำลังดำเนินการ" <?php if ($order['status'] == 'กำลังดำเนินการ') echo 'selected'; ?>>กำลังดำเนินการ (In Progress)</option>
                    <option value="เสร็จสมบรูณ์" <?php if ($order['status'] == 'เสร็จสมบรูณ์') echo 'selected'; ?>>เสร็จสมบรูณ์ (Completed)</option>
                </select>
                <button type="submit">Update Status</button>
            </form>

            <h3>Order Items</h3>
            <ul>
                <?php while ($detail = mysqli_fetch_assoc($details_result)): ?>
                    <li>
                        <img src="../product/<?php echo htmlspecialchars($detail['image']); ?>" alt="<?php echo htmlspecialchars($detail['name']); ?>" width="100">
                        <p><?php echo htmlspecialchars($detail['name']); ?> - Quantity: <?php echo htmlspecialchars($detail['quantity']); ?> - Price: $<?php echo number_format($detail['price'], 2); ?> - Total: $<?php echo number_format($detail['total'], 2); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        </section>
    </main>
    <footer>
        <!-- เพิ่มลิงก์หรือข้อมูลเกี่ยวกับเว็บไซต์ของคุณที่นี่ -->
    </footer>

    <script src="scripts.js"></script> <!-- ลิงก์ไปยังไฟล์ JavaScript -->
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
