<?php
session_start();
include 'connectDB.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$cart_id = $_POST['cart_id'] ?? null;

if (!$cart_id) {
    die("ไม่พบ Cart ID");
}

// ดึงข้อมูลที่อยู่ของลูกค้า รวมทั้งจังหวัดและอำเภอ
$address_query = "SELECT customer.name, customer.address, amphur.amphurName, province.provinceName 
                  FROM customer 
                  JOIN amphur ON customer.amphur_id = amphur.amphurID 
                  JOIN province ON amphur.provinceID = province.provinceID 
                  WHERE customer.customer_id = ?";
$stmt = mysqli_prepare($conn, $address_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $customer_name, $address, $amphurName, $provinceName); // ดึงชื่อลูกค้า
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); 

// ตรวจสอบว่ามีข้อมูลในตะกร้าหรือไม่
$cart_query = "SELECT * FROM cart WHERE cart_id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, 'ii', $cart_id, $customer_id);
mysqli_stmt_execute($stmt);
$cart_result = mysqli_stmt_get_result($stmt);

if (!$cart_result || mysqli_num_rows($cart_result) == 0) {
    die("ไม่พบตะกร้า");
}

// ดึงข้อมูลตะกร้า
$items_query = "SELECT ci.cart_item_id, p.product_id, p.name, p.image, ci.quantity, ci.price, (ci.quantity * ci.price) AS total
                FROM cart_items ci
                JOIN product p ON ci.product_id = p.product_id
                WHERE ci.cart_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, 'i', $cart_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);

if (!$items_result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . mysqli_error($conn));
}

// คำนวณยอดรวม
$grand_total = 0;
while ($item = mysqli_fetch_assoc($items_result)) {
    $grand_total += $item['total'];
}

// รีเซ็ตตัวชี้ผลลัพธ์เพื่อดึงข้อมูลสินค้าซ้ำ
mysqli_data_seek($items_result, 0);

// การแทรกข้อมูลการสั่งซื้อและอัพเดตสต็อก
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_slip'])) {
    $payment_slip = $_FILES['payment_slip'];
    $upload_dir = realpath(__DIR__ . '/./Admin/uploads/');
    $file_name = basename($payment_slip['name']); // รับชื่อไฟล์
    $upload_file = $upload_dir . '/' . $file_name;

    // ตรวจสอบประเภทและขนาดไฟล์
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($payment_slip['type'], $allowed_types)) {
        die("ประเภทไฟล์ไม่ถูกต้อง");
    }
    if ($payment_slip['size'] > 2 * 1024 * 1024) { // 2MB
        die("ขนาดไฟล์เกินกว่าที่กำหนด");
    }

    if (move_uploaded_file($payment_slip['tmp_name'], $upload_file)) {
        // แทรกคำสั่งซื้อใหม่ลงในตาราง orders
        $order_query = "INSERT INTO orders (customer_id, total_amount, payment_slip, order_date, status, address) VALUES (?, ?, ?, NOW(), ?, ?)";
        $stmt = mysqli_prepare($conn, $order_query);
        $status = 'รอตรวจสอบ';
        mysqli_stmt_bind_param($stmt, 'idsss', $customer_id, $grand_total, $file_name, $status, $address); // ใช้ชื่อไฟล์
        if (!mysqli_stmt_execute($stmt)) {  
            die("ข้อผิดพลาดในการแทรกคำสั่งซื้อ: " . mysqli_error($conn));
        }
    
        $order_id = mysqli_insert_id($conn);
    
        // แทรกข้อมูลการสั่งซื้อและอัพเดตสต็อก
        $items_query = "SELECT ci.product_id, ci.quantity, ci.price, p.name
                FROM cart_items ci
                JOIN product p ON ci.product_id = p.product_id
                WHERE ci.cart_id = ?";
        $stmt = mysqli_prepare($conn, $items_query);
        mysqli_stmt_bind_param($stmt, 'i', $cart_id);
        mysqli_stmt_execute($stmt);
        $items_result = mysqli_stmt_get_result($stmt);

        // ตั้งค่า timezone
        date_default_timezone_set('Asia/Bangkok'); // ตั้งเป็นเวลาประเทศไทย

        // สร้างข้อความสำหรับ Line Notify
        $line_message = "มีออเดอร์ใหม่เข้ามา\n";
        $line_message .= "เลขออเดอร์: $order_id\n";
        $line_message .= "เวลาที่สั่ง: " . date('Y-m-d H:i:s') . "\n";
    
        while ($item = mysqli_fetch_assoc($items_result)) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price']; // กำหนดตัวแปร $price ให้ตรงกับฐานข้อมูล
            $name = $item['name']; // ดึงชื่อสินค้า
        
            // แทรกข้อมูลลงใน orderdetails
            $orderdetails_query = "INSERT INTO orderdetails (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $orderdetails_query);
            mysqli_stmt_bind_param($stmt, 'iiid', $order_id, $product_id, $quantity, $price);
            if (!mysqli_stmt_execute($stmt)) {
                die("ข้อผิดพลาดในการแทรกข้อมูลการสั่งซื้อ: " . mysqli_error($conn));
            }
        
            // อัพเดตสต็อก
            $update_stock_query = "UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
            $stmt = mysqli_prepare($conn, $update_stock_query);
            mysqli_stmt_bind_param($stmt, 'ii', $quantity, $product_id);
            if (!mysqli_stmt_execute($stmt)) {
                die("ข้อผิดพลาดในการอัพเดตสต็อก: " . mysqli_error($conn));
            }
        
            // เพิ่มรายละเอียดสินค้าในข้อความ Line Notify
            $line_message .= "$name จำนวน: $quantity $price บาท\n"; // ใช้ตัวแปร $name สำหรับชื่อสินค้า
        }        

        $line_message .= "รวมทั้งสิ้น: " . number_format($grand_total, 2) . " บาท\n";
        $line_message .= "รายละเอียดผู้สั่ง:\nชื่อ: $customer_name\nที่อยู่: $address, $amphurName, $provinceName\n";

        // ส่งการแจ้งเตือนผ่าน Line Notify
        $lineToken = 'BKShK2Llhdrohu0Nwr9w5CdiAWVaBeFkG8KB4Ts0GWW'; // เปลี่ยนเป็น Token ของคุณ
        sendLineNotify($line_message, $lineToken);
    
        // ลบข้อมูลที่เกี่ยวข้องใน cart_items
        $delete_cart_items_query = "DELETE FROM cart_items WHERE cart_id = ?";
        $stmt = mysqli_prepare($conn, $delete_cart_items_query);
        mysqli_stmt_bind_param($stmt, 'i', $cart_id);
        if (!mysqli_stmt_execute($stmt)) {
            die("ข้อผิดพลาดในการลบข้อมูลใน cart_items: " . mysqli_error($conn));
        }
    
        // แสดงการแจ้งเตือนและเปลี่ยนเส้นทาง
        echo "
            <div id='confirmationModal' style='display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;'>
                <div style='background-color: white; padding: 20px; border-radius: 10px; text-align: center;'>
                    <h2>คำสั่งซื้อของคุณถูกยืนยันแล้ว!</h2>
                    <p>กรุณารอสักครู่...</p>
                </div>
            </div>

            <script>
                setTimeout(function() {
                    document.getElementById('confirmationModal').style.display = 'none';
                    window.location.href = 'order_history.php';
                }, 3000); // 3000 milliseconds = 3 seconds
            </script>";

    } else {
        die("ข้อผิดพลาดในการอัพโหลดใบเสร็จการชำระเงิน");
    }    
}

include 'topnavbar.php';

function addNotification($customer_id, $order_id, $message) {
    global $conn;
    
    // เพิ่มการแจ้งเตือน
    $notification_query = "INSERT INTO notifications (customer_id, order_id, message, status) VALUES (?, ?, ?, 'unread')";
    $stmt = mysqli_prepare($conn, $notification_query);
    mysqli_stmt_bind_param($stmt, 'iis', $customer_id, $order_id, $message);
    if (!mysqli_stmt_execute($stmt)) {
        die("ข้อผิดพลาดในการเพิ่มการแจ้งเตือน: " . mysqli_error($conn));
    }
}

// ฟังก์ชันสำหรับส่งการแจ้งเตือนผ่าน Line Notify
function sendLineNotify($message, $lineToken) {
    $line_api = 'https://notify-api.line.me/api/notify';
    $headers = array(
        'Content-Type: multipart/form-data',
        'Authorization: Bearer ' . $lineToken
    );

    $fields = array('message' => $message);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $line_api);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>ยืนยันการสั่งซื้อ - Meat Store</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa; /* Optional: Light background */
        }
        .confirm-checkout {
            margin-top: 50px;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header class="text-white text-center py-3">
        <h1>ยืนยันคำสั่งซื้อของคุณ</h1>
    </header>

    <main class="container">
        <section class="confirm-checkout mx-auto">
            <h2>ยืนยันคำสั่งซื้อ</h2>
            <form action="confirm_checkout.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_id, ENT_QUOTES, 'UTF-8'); ?>">
                <h3>รายการสินค้าในตะกร้าของคุณ:</h3>
                <?php if (mysqli_num_rows($items_result) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>รูปภาพ</th>
                            <th>สินค้า</th>
                            <th>จำนวน</th>
                            <th>ราคา</th>
                            <th>รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <tr>
                            <td><img src="./Admin/product/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" width="100"></td>
                            <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> บาท</td>
                            <td><?php echo number_format($item['total'], 2); ?> บาท</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <h4>ที่อยู่จัดส่ง:</h4>
                <p><?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></p>
                <p><?php echo htmlspecialchars($amphurName, ENT_QUOTES, 'UTF-8'); ?> จังหวัด<?php echo htmlspecialchars($provinceName, ENT_QUOTES, 'UTF-8'); ?></p>
                <h4>ยอดรวม: <?php echo number_format($grand_total, 2); ?> บาท</h4>
                <!-- เพิ่ม QR Code และเลขบัญชีธนาคาร -->
                <div class="payment-info">
                    <h3>Payment Information</h3>
                    <p>Please scan the QR code below to make a payment:</p>
                    <img src="./Admin/images/qr_code.png" alt="QR Code" width="200">
                    <p><strong>Bank Account:</strong> 407-8689387</p>
                    <p><strong>Bank Name:</strong> ประภาภรณ์ จันปุ่ม</p>
                </div>
                <div class="mb-3">
                    <label for="payment_slip" class="form-label">ใบเสร็จการชำระเงิน:</label>
                    <input type="file" class="form-control" id="payment_slip" name="payment_slip" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary">ยืนยันการสั่งซื้อ</button>
                <?php else: ?>
                <p>ไม่มีรายการสินค้าในตะกร้า</p>
                <?php endif; ?>
            </form>
        </section>
    </main>

    <?php
    mysqli_close($conn);
    include 'footer.php';
    ?>
</body>
</html>