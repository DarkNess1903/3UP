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
    SELECT o.order_id, o.customer_id, o.order_date, o.total_amount, o.payment_slip, o.status, c.name AS customer_name
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    WHERE o.order_id = ?
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
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/scripts.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <h1>Order Details</h1>
    </header>
    <main class="main-content">
        <section class="order-details-section">
            <h2>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h2>
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['order_date']))); ?></p>
            <p><strong>Total Amount:</strong>฿<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>

            <?php
            $payment_slip = isset($order['payment_slip']) ? $order['payment_slip'] : '';
            $image_path = "../Admin/uploads/" . htmlspecialchars(basename($payment_slip));
            $image_url = file_exists($image_path) && is_readable($image_path) ? $image_path : "../Admin/uploads/";
            ?>
            <p><strong>Payment Slip:</strong>
                <a href="#" class="view-payment-slip" data-image="<?php echo htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="fas fa-file-image"></i> View Payment Slip
                </a>
            </p>
            
            <form action="view_order.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>" method="post" class="update-status-form">
                <label for="status">Update Status:</label>
                <select name="status" id="status" required>
                    <option value="รอรับเรื่อง" <?php if ($order['status'] == 'รอรับเรื่อง') echo 'selected'; ?>>รอรับเรื่อง (Awaiting)</option>
                    <option value="กำลังดำเนินการ" <?php if ($order['status'] == 'กำลังดำเนินการ') echo 'selected'; ?>>กำลังดำเนินการ (In Progress)</option>
                    <option value="เสร็จสมบรูณ์" <?php if ($order['status'] == 'เสร็จสมบรูณ์') echo 'selected'; ?>>เสร็จสมบรูณ์ (Completed)</option>
                </select>
                <button type="submit" class="btn">Update Status</button>
            </form>

            <h3>Order Items</h3>
            <ul class="order-items">
                <?php while ($detail = mysqli_fetch_assoc($details_result)): ?>
                    <li class="order-item">
                        <img src="../product/<?php echo htmlspecialchars($detail['image']); ?>" alt="<?php echo htmlspecialchars($detail['name']); ?>" class="item-image">
                        <p><?php echo htmlspecialchars($detail['name']); ?> - Quantity: <?php echo htmlspecialchars($detail['quantity']); ?> - Price: ฿<?php echo number_format($detail['price'], 2); ?> - Total: ฿<?php echo number_format($detail['total'], 2); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        </section>
    </main>
    <footer class="footer">
        <p>&copy; 2024 Your Store. All rights reserved.</p>
    </footer>

    <!-- โมดัลสำหรับแสดงภาพ -->
    <div id="myModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="img01">
        <div id="caption"></div>
    </div>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
