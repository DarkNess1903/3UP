<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

if (!isset($_GET['order_id'])) {
    echo 'คำสั่งซื้อไม่ถูกต้อง';
    exit();
}

$order_id = intval($_GET['order_id']);

// ดึงข้อมูลคำสั่งซื้อ
$sql = "SELECT orders.*, customer.name AS customer_name, customer.email AS customer_email, orders.payment_slip
        FROM orders
        JOIN customer ON orders.customer_id = customer.customer_id
        WHERE orders.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo 'ไม่พบคำสั่งซื้อ';
    exit();
}

// ดึงข้อมูลรายการสินค้าที่สั่งซื้อ
$sql = "SELECT orderdetails.*, product.name, product.price, product.image
        FROM orderdetails
        JOIN product ON orderdetails.product_id = product.product_id
        WHERE orderdetails.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css"> <!-- ลิงก์ไปยังไฟล์ CSS ของคุณ -->
    <title>รายละเอียดคำสั่งซื้อ</title>
    <style>
        /* CSS สำหรับโมดัล */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            position: relative;
        }

        .modal-content img {
            width: 100%;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 25px;
            color: #aaa;
            font-size: 35px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        #verifySlipBtn {
            display: <?php echo $order['status'] === 'Completed checking of slip' ? 'none' : 'inline-block'; ?>;
        }

        #statusMessage {
            display: <?php echo $order['status'] === 'Completed checking of slip' ? 'inline-block' : 'none'; ?>;
        }
    </style>
</head>
<body>
    <header>
        <!-- ใส่ Navbar ของคุณที่นี่ -->
    </header>

    <div class="order-details-container">
        <h1>รายละเอียดคำสั่งซื้อ</h1>
        <p>หมายเลขคำสั่งซื้อ: <?php echo htmlspecialchars($order_id); ?></p>
        <p>ชื่อ: <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p>อีเมล: <?php echo htmlspecialchars($order['customer_email']); ?></p>
        <p>ยอดรวมที่ต้องชำระ: <?php echo number_format($order['total_amount'], 2); ?> บาท</p>
        <p>สถานะคำสั่งซื้อ: <?php echo htmlspecialchars($order['status']); ?></p>

        <h2>รายการสินค้าที่สั่งซื้อ</h2>
        <ul class="product-list">
            <?php while ($item = $items->fetch_assoc()): ?>
                <li>
                 <img src="../Admin/product/<?php echo htmlspecialchars($item['image']); ?>" alt="Product Image" width="50px" height="50px">
                    <span><?php echo htmlspecialchars($item['name']); ?></span> -
                    <span><?php echo number_format($item['price'], 2); ?> บาท</span> -
                    <span><?php echo htmlspecialchars($item['quantity']); ?> ชิ้น</span> -
                    <span>ราคารวม: <?php echo number_format($item['price'] * $item['quantity'], 2); ?> บาท</span>
                </li>
            <?php endwhile; ?>
        </ul>

        <h2>สลิปการชำระเงิน</h2>
        <?php if ($order['payment_slip']): ?>
            <button id="viewSlipBtn">ดูสลิป</button>
            <div id="slipModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <img src="../Admin/uploads/<?php echo htmlspecialchars($order['payment_slip']); ?>" alt="Slip Image">
                </div>
            </div>
            <button id="verifySlipBtn">ตรวจสอบสลิปเรียบร้อย</button>
            <p id="statusMessage">
              <?php echo $order['status'] === 'Pending' ? 'กรุณาตรวจสอบสลิป' : 'สลิปได้รับการตรวจสอบเรียบร้อยแล้ว'; ?>
            </p>
        <?php else: ?>
            <p>ไม่มีสลิปการชำระเงิน</p>
        <?php endif; ?>
    </div>

    <footer>
        <!-- ใส่ Footer ของคุณที่นี่ -->
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var modal = $('#slipModal');
            var btn = $('#viewSlipBtn');
            var span = $('.close');
            var statusMessage = $('#statusMessage');

            // แสดงโมดัลเมื่อคลิกปุ่มดูสลิป
            btn.on('click', function() {
                modal.show();
            });

            // ปิดโมดัลเมื่อคลิกที่ปุ่มปิด
            span.on('click', function() {
                modal.hide();
            });

            // ปิดโมดัลเมื่อคลิกนอกโมดัล
            $(window).on('click', function(event) {
                if ($(event.target).is(modal)) {
                    modal.hide();
                }
            });

            $('#verifySlipBtn').on('click', function() {
                $.ajax({
                    url: 'update_order_status.php',
                    method: 'POST',
                    data: { order_id: <?php echo $order_id; ?> },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#verifySlipBtn').hide();
                            statusMessage.text('สลิปได้รับการตรวจสอบเรียบร้อยแล้ว').show();
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์');
                    }
                });
            });
        });
    </script>
</body>
</html>
