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
$address_query = "
    SELECT 
        customer.name, 
        customer.phone AS customer_phone, 
        customer.address, 
        amphur.AMPHUR_NAME AS amphurName, 
        province.PROVINCE_NAME AS provinceName,
        CASE 
            WHEN province.PROVINCE_NAME = 'กรุงเทพมหานคร' THEN district.DISTRICT_CODE
            ELSE amphur.POSTCODE 
        END AS postal_code,
        district.DISTRICT_NAME AS districtName
    FROM customer 
    JOIN amphur ON customer.amphur_id = amphur.AMPHUR_ID 
    JOIN province ON amphur.PROVINCE_ID = province.PROVINCE_ID 
    LEFT JOIN district ON customer.district_id = district.DISTRICT_ID
    WHERE customer.customer_id = ?";

$stmt = mysqli_prepare($conn, $address_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $customer_name, $customer_phone, $address, $amphurName, $provinceName, $postal_code, $districtName);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); 

// ดึงข้อมูลตะกร้าสินค้า
$cart_query = "SELECT * FROM cart WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$cart_result = mysqli_stmt_get_result($stmt);

if (!$cart_result) {
    echo "Error fetching cart: " . mysqli_error($conn);
    exit();
}

$cart = mysqli_fetch_assoc($cart_result);

if ($cart) {
    $cart_id = $cart['cart_id'];

    // ดึงข้อมูลสินค้าจากตะกร้า
    $items_query = "SELECT ci.cart_item_id, p.name, p.image, ci.quantity, ci.price, p.price_per_piece, (ci.quantity * ci.price) AS total, p.stock_quantity, p.weight_per_item
    FROM cart_items ci
    JOIN product p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?";
    
    $stmt = mysqli_prepare($conn, $items_query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_id);
    mysqli_stmt_execute($stmt);
    $items_result = mysqli_stmt_get_result($stmt);

    if (!$items_result) {
        echo "Error fetching items: " . mysqli_error($conn);
        exit();
    }

    // คำนวณยอดรวม
    $grand_total = 0;
    while ($item = mysqli_fetch_assoc($items_result)) {
        // คำนวณยอดรวมที่ถูกต้อง
        if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
            // คำนวณจากราคาเป็นกิโลกรัม
            $item_total = ($item['price'] * ($item['quantity'] * $item['weight_per_item'] / 1000));
        } else {
            // คำนวณจากราคาเป็นชิ้น
            $item_total = ($item['price_per_piece'] * $item['quantity']);
        }
        $grand_total += $item_total;
    }
    // Reset the result pointer to fetch items again
    mysqli_data_seek($items_result, 0);

} else {
    $items_result = [];
    $grand_total = 0;
}

// รีเซ็ตตัวชี้ผลลัพธ์เพื่อดึงข้อมูลสินค้าซ้ำ
mysqli_data_seek($items_result, 0);

// การแทรกข้อมูลการสั่งซื้อและอัพเดตสต็อก
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_slip'])) {
    $payment_slip = $_FILES['payment_slip'];
    $upload_dir = realpath(__DIR__ . '/./Admin/uploads/');
    $file_name = basename($payment_slip['name']);
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
        mysqli_stmt_bind_param($stmt, 'idsss', $customer_id, $grand_total, $file_name, $status, $address);
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
        date_default_timezone_set('Asia/Bangkok'); 

        // สร้างข้อความสำหรับ Line Notify
        $line_message = "มีออเดอร์ใหม่เข้ามา\n";
        $line_message .= "เลขออเดอร์: $order_id\n";
        $line_message .= "เวลาที่สั่ง: " . date('Y-m-d H:i:s') . "\n";
    
        while ($item = mysqli_fetch_assoc($items_result)) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price']; 
            $name = $item['name']; 
        
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
            $line_message .= "$name จำนวน: $quantity $price บาท\n";
        }        

        $line_message .= "รวมทั้งสิ้น: " . number_format($grand_total, 2) . " บาท\n";
        $line_message .= "รายละเอียดผู้สั่ง:\nชื่อ: $customer_name\nที่อยู่: $address, $amphurName, $provinceName\n";

        // ส่งการแจ้งเตือนผ่าน Line Notify
        $lineToken = 'BKShK2Llhdrohu0Nwr9w5CdiAWVaBeFkG8KB4Ts0GWW'; 
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

function sendLineNotify($message, $lineToken) {
    $line_api = 'https://notify-api.line.me/api/notify';
    $headers = array(
        'Content-Type: multipart/form-data',
        'Authorization: Bearer ' . $lineToken
    );

    $data = array(
        'message' => $message,
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $line_api);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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
                            <td>
                                <?php 
                                    // แสดงจำนวนตามเงื่อนไข
                                    if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
                                        echo number_format($item['quantity'] * $item['weight_per_item'] / 1000, 2) . ' กก.'; // แสดงเป็นกิโลกรัม
                                    } else {
                                        echo number_format($item['quantity'], 0) . ' ชิ้น'; // แสดงเป็นจำนวนชิ้น
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                // แสดงราคาให้ถูกต้อง
                                if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
                                    echo number_format($item['price'], 2); // แสดงราคาเป็นกิโลกรัม
                                } else {
                                    echo number_format($item['price_per_piece'], 2); // แสดงราคาเป็นชิ้น
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                // คำนวณยอดรวมที่ถูกต้อง
                                if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
                                    echo number_format($item['price'] * ($item['quantity'] * $item['weight_per_item'] / 1000), 2); // ยอดรวมเป็นกิโลกรัม
                                } else {
                                    echo number_format($item['price_per_piece'] * $item['quantity'], 2); // ยอดรวมเป็นชิ้น
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php
                    function calculateShippingCost($totalWeight, $isCentralRegion) {
                        // กำหนดค่าจัดส่งตามน้ำหนักและพื้นที่
                        $shippingRates = [
                            5 => [190, 270],
                            10 => [230, 290],
                            15 => [260, 330],
                            20 => [290, 370],
                            25 => [330, 430],
                            30 => [390, 490],
                        ];

                        // ค้นหาค่าจัดส่งตามน้ำหนัก
                        foreach ($shippingRates as $weight => $rates) {
                            if ($totalWeight <= $weight) {
                                return $isCentralRegion ? $rates[0] : $rates[1];
                            }
                        }

                        // ถ้าหากน้ำหนักมากกว่า 30 กิโลกรัม จะกำหนดค่าจัดส่งเป็น 490 บาท (ค่าจัดส่งสูงสุดที่กำหนด)
                        return $isCentralRegion ? 390 : 490;
                    }

                    // การใช้ฟังก์ชัน
                    $totalWeight = 10; // น้ำหนักรวม
                    $isCentralRegion = true; // ตั้งค่าเป็น true ถ้าส่งในภาคกลาง

                    $shippingCost = calculateShippingCost($totalWeight, $isCentralRegion);
                    $grandTotal = $orderTotal + $shippingCost; // $orderTotal เป็นยอดรวมของสินค้า

                    echo "<h4>ยอดรวม: " . number_format($grandTotal, 2) . " บาท</h4>";
                    echo "<h4>ค่าจัดส่ง: " . number_format($shippingCost, 2) . " บาท</h4>";
                ?>

                <h4>ยอดรวม: <?php echo number_format($grand_total, 2); ?> บาท</h4>
                <p><h4>ข้อมูลสำหรับจัดส่ง:</h4></p>
                    <p><strong><?php echo htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?></strong> | <strong><?php echo htmlspecialchars($customer_phone, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <p>ที่อยู่: <?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($districtName, ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($amphurName, ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($provinceName, ENT_QUOTES, 'UTF-8') . ', รหัสไปรษณีย์: ' . htmlspecialchars($postal_code, ENT_QUOTES, 'UTF-8'); ?></p>

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