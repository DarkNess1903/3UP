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
$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    die("Invalid order ID.");
}

// ดึงข้อมูลคำสั่งซื้อ
$order_query = "
    SELECT order_id, order_date, total_amount, payment_slip, status
    FROM orders
    WHERE order_id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $customer_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($order_result) === 0) {
    die("Order not found.");
}

$order = mysqli_fetch_assoc($order_result);

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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
</head>
<body>
    <header>
        <h1>Order Details</h1>
    </header>
    <main>
        <section class="order-details">
            <h2>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h2>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['order_date']))); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            <?php
            $payment_slip = isset($order['payment_slip']) ? $order['payment_slip'] : '';
            $image_path = "../Admin/uploads/" . htmlspecialchars(basename($payment_slip));
            // ตรวจสอบการเข้าถึงไฟล์
            $image_url = file_exists($image_path) && is_readable($image_path) ? $image_path : "../Admin/uploads/";
            ?>
            <p><strong>Payment Slip:</strong>
                <a href="<?php echo htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'); ?>" class="view-payment-slip" data-image="<?php echo htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="fas fa-file-image"></i> View Payment Slip
                </a>
            </p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
            <h3>Order Items</h3>
            <ul class="order-items">
                <?php while ($detail = mysqli_fetch_assoc($details_result)): ?>
                    <li>
                        <img src="../public/<?php echo htmlspecialchars($detail['image']); ?>" alt="<?php echo htmlspecialchars($detail['name']); ?>" width="100">
                        <p><?php echo htmlspecialchars($detail['name']); ?> - Quantity: <?php echo htmlspecialchars($detail['quantity']); ?> - Price: $<?php echo number_format($detail['price'], 2); ?> - Total: $<?php echo number_format($detail['total'], 2); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        </section>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Company. All rights reserved.</p>
    </footer>

    <!-- โมดัลสำหรับแสดงภาพ -->
    <div id="myModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="img01">
        <div id="caption"></div>
    </div>

    <script>
        // JavaScript สำหรับการเปิดและปิดโมดัล
        var modal = document.getElementById("myModal");
        var links = document.querySelectorAll('.view-payment-slip');
        var span = document.getElementsByClassName("close")[0];
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");

        links.forEach(function(link) {
            link.onclick = function(event) {
                event.preventDefault();
                var imageUrl = this.getAttribute('data-image');
                modal.style.display = "block";
                modalImg.src = imageUrl;
                captionText.innerHTML = "Payment Slip";
            }
        });

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>


<?php
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
