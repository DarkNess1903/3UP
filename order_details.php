<?php
session_start();
include 'connectDB.php';
include 'topnavbar.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    die("รหัสคำสั่งซื้อไม่ถูกต้อง.");
}

// ดึงข้อมูลคำสั่งซื้อ
$order_query = "
    SELECT order_id, order_date, total_amount, payment_slip, status, tracking_number, shipping_fee
    FROM orders
    WHERE order_id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $customer_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($order_result) === 0) {
    die("ไม่พบคำสั่งซื้อ.");
}

$order = mysqli_fetch_assoc($order_result);

// ดึงรายละเอียดสินค้า รวมถึง weight_in_grams
$details_query = "
    SELECT p.name, p.image, od.quantity, od.price, od.weight_in_grams
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
<html lang="th">
<head>
    <title>รายละเอียดคำสั่งซื้อ</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS Links -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- JavaScript Links -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</head>
<body>
    <header class="bg-dark text-white text-center py-3">
        <h1>รายละเอียดคำสั่งซื้อ</h1>
    </header>
    <main class="container mt-4">
    <section class="order-details">
        <h2>รหัสคำสั่งซื้อ: <?php echo htmlspecialchars($order['order_id']); ?></h2>
        <p><strong>วันที่สั่งซื้อ:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['order_date']))); ?></p>
        <p><strong>ยอดรวมทั้งหมด:</strong> ฿<?php echo number_format($order['total_amount'], 2); ?></p>
        <p><strong>ค่าจัดส่ง:</strong> ฿<?php echo number_format($order['shipping_fee'], 2); ?></p> <!-- แสดงค่าจัดส่ง -->
        <?php
        $payment_slip = isset($order['payment_slip']) ? $order['payment_slip'] : '';
        $image_path = "./Admin/uploads/" . htmlspecialchars(basename($payment_slip));
        $image_url = file_exists($image_path) && is_readable($image_path) ? $image_path : "./Admin/uploads/";
        ?>
        <p><strong>สลิปการชำระเงิน:</strong>
            <a href="#" class="view-payment-slip" data-image="<?php echo htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'); ?>">
                <i class="fas fa-file-invoice-dollar"></i> ดูสลิปการชำระเงิน
            </a>
        </p>
        <p><strong>สถานะ:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
        <?php if ($order['status'] === 'กำลังจัดส่ง'): ?>
            <p><strong>เลขพัสดุ:</strong> <?php echo htmlspecialchars($order['tracking_number']); ?></p>
        <?php endif; ?>

        <h3>รายการสินค้า</h3>
        <ul class="list-group">
            <?php while ($detail = mysqli_fetch_assoc($details_result)): ?>
                <?php
                    // ตรวจสอบหน่วยของสินค้า
                    $quantity_text = '';
                    $weight_in_grams = $detail['weight_in_grams'];
                    $unit = ($weight_in_grams >= 1000) ? '1kg' : '1piece'; // สมมุติหน่วยน้ำหนัก

                    if ($unit == '1kg') {
                        // แสดงจำนวนเป็นกิโลกรัม
                        $quantity_kg = $weight_in_grams / 1000;
                        $quantity_text = "จำนวน: " . number_format($quantity_kg, 2) . " กก.";
                        $total = $quantity_kg * $detail['price'];
                    } else if ($unit == '1piece') {
                        // แสดงจำนวนเป็นชิ้น
                        $quantity_text = "จำนวน: " . $detail['quantity'] . " ชิ้น";
                        $total = $detail['quantity'] * $detail['price'];
                    }
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="./Admin/product/<?php echo htmlspecialchars($detail['image']); ?>" alt="<?php echo htmlspecialchars($detail['name']); ?>" width="100" class="me-2">
                        <div>
                            <p class="mb-0"><?php echo htmlspecialchars($detail['name']); ?></p>
                            <small><?php echo $quantity_text; ?></small>
                        </div>
                    </div>
                    <p class="mb-0">ราคา: ฿<?php echo number_format($detail['price'], 2); ?> - ยอดรวม: ฿<?php echo number_format($total, 2); ?></p>
                </li>
            <?php endwhile; ?>
        </ul>
    </section>
</main>
    <!-- โมดัลสำหรับแสดงภาพ -->
    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">สลิปการชำระเงิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img class="modal-img" id="img01" src="" alt="Payment Slip" style="width: 100%;">
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript สำหรับการเปิดโมดัล
        var modal = document.getElementById("myModal");
        var links = document.querySelectorAll('.view-payment-slip');
        
        links.forEach(function(link) {
            link.onclick = function(event) {
                event.preventDefault();
                var imageUrl = this.getAttribute('data-image');
                var modalImg = document.getElementById("img01");
                modalImg.src = imageUrl;
                var modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            }
        });
    </script>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
include 'footer.php';
?>
