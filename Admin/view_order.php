<?php
include 'connectDB.php';
include 'topnavbar.php';

if (!isset($_GET['order_id'])) {
    echo 'คำสั่งซื้อไม่ถูกต้อง';
    exit();
}

$order_id = intval($_GET['order_id']);

// ดึงข้อมูลคำสั่งซื้อพร้อมข้อมูลอำเภอและจังหวัด
$sql = "SELECT orders.*, customer.name AS customer_name, customer.address AS customer_address, 
               customer.province_id, customer.amphur_id, 
               amphur.AMPHUR_NAME AS amphurName, province.PROVINCE_NAME AS provinceName, 
               orders.payment_slip
        FROM orders
        JOIN customer ON orders.customer_id = customer.customer_id
        LEFT JOIN amphur ON customer.amphur_id = amphur.AMPHUR_ID
        LEFT JOIN province ON customer.province_id = province.PROVINCE_ID
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
$sql = "SELECT orderdetails.*, product.name, product.image
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
    <div class="container mt-4 order-details-container">
        <h1>รายละเอียดคำสั่งซื้อ</h1>
        <p>หมายเลขคำสั่งซื้อ: <?php echo htmlspecialchars($order_id); ?></p>
        <p>ชื่อ: <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p>ที่อยู่: <?php echo htmlspecialchars($order['customer_address']); ?></p>
        <p>อำเภอ: <?php echo htmlspecialchars($order['amphurName']); ?></p> <!-- แสดงชื่ออำเภอ -->
        <p>จังหวัด: <?php echo htmlspecialchars($order['provinceName']); ?></p> <!-- แสดงชื่อจังหวัด -->
        <p>ค่าส่ง: <?php echo number_format($order['shipping_fee'], 2); ?> บาท</p> <!-- แสดงค่าส่ง -->
        <p>ยอดรวมที่ต้องชำระ: <?php echo number_format($order['total_amount'], 2); ?> บาท</p>
        <p>สถานะคำสั่งซื้อ: <?php echo htmlspecialchars($order['status']); ?></p>

        <h2>รายการสินค้าที่สั่งซื้อ</h2>
        <ul class="list-group product-list">
            <?php while ($item = $items->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <img src="../Admin/product/<?php echo htmlspecialchars($item['image']); ?>" alt="Product Image" width="50px" height="50px" class="mr-2">
                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                    <span><?php echo number_format($item['price'], 2); ?> บาท</span>

                    <?php
                    // คำนวณจำนวนและหน่วย
                    $weight_in_grams = $item['weight_in_grams'];
                    $quantity_text = '';
                    $total = 0;

                    if ($weight_in_grams >= 1000) {
                        // สินค้าน้ำหนักเป็นกิโลกรัม
                        $quantity_kg = $weight_in_grams / 1000;
                        $quantity_text = "จำนวน: " . number_format($quantity_kg, 2) . " กก.";
                        $total = $quantity_kg * $item['price']; // คำนวณยอดรวม
                    } else {
                        // สินค้าเป็นชิ้น
                        $quantity_text = "จำนวน: " . $item['quantity'] . " ชิ้น";
                        $total = $item['quantity'] * $item['price']; // คำนวณยอดรวม
                    }
                    ?>

                    <span><?php echo $quantity_text; ?></span>
                    <span>รวม: <?php echo number_format($total, 2); ?> บาท</span>
                </li>
            <?php endwhile; ?>
        </ul>

        <h2>สลิปการชำระเงิน</h2>
        <?php if ($order['payment_slip']): ?>
            <button id="viewSlipBtn" class="btn btn-info">ดูสลิป</button>
            <div id="slipModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <img src="../Admin/uploads/<?php echo htmlspecialchars($order['payment_slip']); ?>" alt="Slip Image" class="img-fluid">
                </div>
            </div>
            <button id="verifySlipBtn" class="btn btn-success" style="display: <?php echo ($order['status'] === 'ตรวจสอบแล้วกำลังดำเนินการ' || $order['status'] === 'เสร็จสิ้น' || $order['status'] === 'กำลังจัดส่ง') ? 'none' : 'inline-block'; ?>;">
                ตรวจสอบสลิปเรียบร้อย
            </button>
            <p id="statusMessage" class="mt-2">
                <?php echo $order['status'] === 'รอตรวจ' ? 'กรุณาตรวจสอบสลิป' : 'ตรวจสอบแล้วกำลังดำเนินการ'; ?>
            </p>

            <!-- ปุ่มกำลังการจัดส่ง -->
            <div id="shippingSection" style="display: <?php echo ($order['status'] === 'ตรวจสอบแล้วกำลังดำเนินการ') ? 'block' : 'none'; ?>;">
                <h3>จัดการการจัดส่ง</h3>
                <input type="text" id="trackingNumber" placeholder="กรอกหมายเลขติดตามการจัดส่ง" class="form-control">
                <button id="updateShippingBtn" class="btn btn-primary mt-2">อัปเดตสถานะการจัดส่ง</button>
                <p id="shippingMessage" class="mt-2" style="display: none;"></p>
            </div>

            <?php else: ?>
                <p>ไม่มีสลิปการชำระเงิน</p>
            <?php endif; ?>
        </div>

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
            // แสดงกล่องยืนยัน
            if (confirm('คุณแน่ใจหรือไม่ว่าตรวจสอบสลิปแล้ว?')) {
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
                            }, 1000);
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์');
                    }
                });
            }
        });

        // อัปเดตสถานะการจัดส่ง
        $('#updateShippingBtn').on('click', function() {
            var trackingNumber = $('#trackingNumber').val().trim();
            if (trackingNumber === '') {
                alert('กรุณากรอกหมายเลขติดตามการจัดส่ง');
                return;
            }

            $.ajax({
                url: 'update_shipping_status.php',
                method: 'POST',
                data: { order_id: <?php echo $order_id; ?>, tracking_number: trackingNumber },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#shippingMessage').text('สถานะการจัดส่งได้รับการอัปเดตเรียบร้อยแล้ว').show();
                        setTimeout(function() {
                            location.reload(); // รีเฟรชหน้า
                        }, 1000);
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