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
    <title>รายละเอียดคำสั่งซื้อ</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
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
            display: <?php echo $order['status'] === 'ตรวจสอบแล้วกำลังดำเนินการ' ? 'none' : 'inline-block'; ?>;
        }

        #statusMessage {
            display: <?php echo $order['status'] === 'ตรวจสอบแล้วกำลังดำเนินการ' ? 'inline-block' : 'none'; ?>;
        }

        /* Container สำหรับรายละเอียดคำสั่งซื้อ */
        .order-details-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .order-details-container p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .product-list {
            list-style-type: none;
            padding: 0;
        }

        .product-list li {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .product-list img {
            margin-right: 10px;
        }

        .product-list span {
            margin-right: 10px;
        }

        /* โมดัล */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        /* ปุ่ม */
        button {
            background-color: #007bff;
            border: none;
            color: #fff;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin: 10px 0;
        }

        button:hover {
            background-color: #0056b3;
        }

        #verifySlipBtn {
            background-color: #28a745;
        }

        #verifySlipBtn:hover {
            background-color: #218838;
        }

        /* ข้อความสถานะ */
        #statusMessage {
            font-size: 16px;
            color: #333;
            margin-top: 10px;
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
              <?php echo $order['status'] === 'รอตรวจ' ? 'กรุณาตรวจสอบสลิป' : 'ตรวจสอบแล้วกำลังดำเนินการ'; ?>
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
                    data: { order_id: <?php echo $order_id; ?>, status: 'ตรวจสอบแล้วกำลังดำเนินการ' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#verifySlipBtn').hide();
                            $('#statusMessage').text('สลิปได้รับการตรวจสอบเรียบร้อยแล้ว').show();
                            setTimeout(function() {
                                location.reload(); // รีเฟรชหน้า
                            }, 1000); // หน่วงเวลา 1 วินาทีเพื่อให้เห็นข้อความ
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
<style>
    #verifySlipBtn {
        display: <?php echo ($order['status'] === 'ตรวจสอบแล้วกำลังดำเนินการ' || $order['status'] === 'เสร็จสิ้น') ? 'none' : 'inline-block'; ?>;
    }

    #statusMessage {
        display: <?php echo $order['status'] === 'ตรวจสอบแล้วกำลังดำเนินการ' ? 'inline-block' : 'none'; ?>;
    }
</style>

