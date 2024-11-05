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
    $items_query = "SELECT ci.cart_item_id, p.product_id, p.name, p.image, ci.quantity, ci.price, p.price_per_piece, (ci.quantity * ci.price) AS total, p.stock_quantity, p.weight_per_item
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
$shipping_fee = calculateShippingFeeForCart($customer_id, $conn); // ค่าจัดส่ง

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
        $order_query = "INSERT INTO orders (customer_id, total_amount, payment_slip, order_date, status, address, shipping_fee) VALUES (?, ?, ?, NOW(), ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $order_query);
        $status = 'รอตรวจสอบ';
        mysqli_stmt_bind_param($stmt, 'idsssd', $customer_id, $grand_total, $file_name, $status, $address, $shipping_fee);
        if (!mysqli_stmt_execute($stmt)) {
            die("ข้อผิดพลาดในการแทรกคำสั่งซื้อ: " . mysqli_error($conn));
        }
        $order_id = mysqli_insert_id($conn);
        
        // ดึงข้อมูลสินค้าจากตะกร้า
        $items_query = "SELECT ci.cart_item_id, p.product_id, p.name, p.image, ci.quantity, ci.price, p.price_per_piece, p.weight_per_item, (ci.quantity * ci.price) AS total, p.stock_quantity
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
        $line_message = "🔔 แจ้งเตือนออเดอร์ใหม่\n";
        $line_message .= "เลขออเดอร์: $order_id\n";
        $line_message .= "เวลาที่สั่ง: " . date('Y-m-d H:i:s') . "\n";
        $line_message .= "📋 รายการสั่งซื้อ:\n";
    
        $total_weight = 0;  // น้ำหนักรวม
    
        // คำนวณน้ำหนักรวมและอัพเดตสต็อก
        while ($item = mysqli_fetch_assoc($items_result)) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $weight_per_item = $item['weight_per_item']; // น้ำหนักต่อชิ้นของสินค้า
            
            // คำนวณน้ำหนักรวมเป็นกรัม
            $weight_in_grams = $quantity * $weight_per_item;
            $total_weight += $weight_in_grams; // เพิ่มน้ำหนักเข้าสู่น้ำหนักรวม
            
            // แทรกข้อมูลลงใน orderdetails
            $orderdetails_query = "INSERT INTO orderdetails (order_id, product_id, quantity, price, weight_in_grams) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $orderdetails_query);
            mysqli_stmt_bind_param($stmt, 'iiidi', $order_id, $product_id, $quantity, $item['price'], $weight_in_grams);
            if (!mysqli_stmt_execute($stmt)) {
                die("ข้อผิดพลาดในการแทรกข้อมูลการสั่งซื้อ: " . mysqli_error($conn));
            }
    
            // คำนวณน้ำหนักที่จะหักจากสต็อก
            $stock_to_deduct = ($weight_per_item >= 1000) ? $quantity * $weight_per_item / 1000 : $quantity; // แปลงน้ำหนักเป็นกิโลกรัมถ้าจำเป็น
    
            // อัพเดตสต็อก
            $update_stock_query = "UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
            $stmt = mysqli_prepare($conn, $update_stock_query);
            mysqli_stmt_bind_param($stmt, 'di', $stock_to_deduct, $product_id);
            if (!mysqli_stmt_execute($stmt)) {
                die("ข้อผิดพลาดในการอัพเดตสต็อก: " . mysqli_error($conn));
            }
    
            // สร้างข้อความสำหรับส่งไปยัง Line Notify
            if ($weight_in_grams >= 1000) {
                $quantity_display = number_format($weight_in_grams / 1000, 2) . ' กก.'; // แสดงน้ำหนักในหน่วยกิโลกรัม
                $item_total = $item['price'] * ($weight_in_grams / 1000); // คำนวณยอดรวมสำหรับน้ำหนัก
            } else {
                $quantity_display = number_format($quantity, 0) . ' ชิ้น'; // แสดงจำนวนเป็นชิ้น
                $item_total = $item['price_per_piece'] * $quantity; // คำนวณยอดรวมสำหรับชิ้น
            }
            
            // เพิ่มข้อมูลลงในข้อความ Line Notify
            $line_message .= "- {$item['name']} จำนวน: {$quantity_display}, ราคา: " . number_format($item_total, 2) . " บาท\n";
        }
        $shippingFee = calculateShippingFeeForCart($customer_id, $conn); // ค่าจัดส่ง
        $total_order_amount = $grand_total + $shippingFee; // ยอดรวมทั้งสิ้น

        // บันทึกยอดรวมและค่าส่งลงในตาราง orders
        $update_order_query = "UPDATE orders SET total_amount = ? WHERE order_id = ?";
        $stmt = mysqli_prepare($conn, $update_order_query);
        mysqli_stmt_bind_param($stmt, 'di', $total_order_amount, $order_id);
        if (!mysqli_stmt_execute($stmt)) {
            die("ข้อผิดพลาดในการอัปเดตยอดรวมในคำสั่งซื้อ: " . mysqli_error($conn));
        }

        // เพิ่มค่าส่งในข้อความ Line Notify
        $line_message .= "💰 ยอดสั่งซื้อ: " . number_format($grand_total, 2) . " บาท\n";
        $line_message .= "🚚 ค่าส่ง: " . number_format($shippingFee, 2) . " บาท\n";
        $line_message .= "💵 ยอดรวมทั้งสิ้น: " . number_format($total_order_amount, 2) . " บาท\n";
        $line_message .= "📍 ที่อยู่ผู้สั่ง:\n";
        $line_message .= "ชื่อ: $customer_name ,$customer_phone\n";
        $line_message .= "ที่อยู่: $address, $amphurName, $provinceName \n";

        // ส่งข้อความไปยัง Line Notify
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
                    window.location.href = 'order_history.php';
                }, 3000);
            </script>
        ";
    } else {
        die("การอัปโหลดไฟล์ไม่สำเร็จ");
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

function calculateShippingFeeForCart($customer_id, $conn) {
    // ดึงน้ำหนักรวมจากตาราง cart_items โดยรวม weight_per_item จาก product
    $query = "SELECT SUM(ci.quantity * p.weight_per_item) AS total_weight
              FROM cart_items ci
              JOIN cart c ON ci.cart_id = c.cart_id
              JOIN product p ON ci.product_id = p.product_id
              WHERE c.customer_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return -1; // ส่งค่าผิดพลาดเมื่อไม่พบข้อมูลในตะกร้า
    }

    $row = $result->fetch_assoc();
    $total_weight_in_grams = $row['total_weight'];

    // แปลงน้ำหนักเป็นกิโลกรัม
    $total_weight_in_kilograms = $total_weight_in_grams / 1000;

    // คำนวณค่าจัดส่ง
    return calculateShippingFee($total_weight_in_kilograms, $customer_id, $conn);
}

function calculateShippingFee($weight, $customer_id, $conn) {
    // ดึงข้อมูลภูมิภาคของลูกค้าจาก customer_id
    $query = "SELECT p.GEO_ID FROM customer c
              JOIN province p ON c.province_id = p.PROVINCE_ID
              WHERE c.customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return -1; // ส่งค่าผิดพลาดเมื่อไม่พบข้อมูลลูกค้า
    }
    
    $row = $result->fetch_assoc();
    $geo_id = $row['GEO_ID'];

    // คำนวณค่าจัดส่ง
    $base_fee = calculateBaseFee($weight, $geo_id);
    $additional_fee = 0;

    // ตรวจสอบน้ำหนักมากกว่า 30 กก.
    if ($weight > 30) {
        $remaining_weight = $weight - 30; // หัก 30 ออก
        $additional_fee = calculateAdditionalFee($remaining_weight, $geo_id); // คำนวณค่าจัดส่งเพิ่มเติม
    }

    // ตรวจสอบค่าฐาน ค่าจัดส่งห้ามเป็น 0
    if ($base_fee === 0) {
        return 0; // หาก base_fee เป็น 0 ให้คืน 0
    }

    return $base_fee + $additional_fee; 
}

function calculateBaseFee($weight, $geo_id) {
    // คำนวณค่าจัดส่งโดยไม่ใช้การปัดน้ำหนัก
    if ($weight >= 0 && $weight <= 5) {
        return ($geo_id == 2) ? 190 : 270;
    } elseif ($weight > 5 && $weight <= 10) {
        return ($geo_id == 2) ? 230 : 290;
    } elseif ($weight > 10 && $weight <= 15) {
        return ($geo_id == 2) ? 260 : 330;
    } elseif ($weight > 15 && $weight <= 20) {
        return ($geo_id == 2) ? 290 : 370;
    } elseif ($weight > 20 && $weight <= 25) {
        return ($geo_id == 2) ? 330 : 430;
    } elseif ($weight > 25 && $weight <= 30) {
        return ($geo_id == 2) ? 390 : 490;
    } elseif ($weight > 30) {
        return ($geo_id == 2) ? 390 : 490;
    }
    return 0; // ส่งค่าผิดพลาดเมื่อไม่ตรงตามเงื่อนไข
}

function calculateAdditionalFee($weight, $geo_id) {
    $additional_fee = 0;

    // ลบการใช้ floor() เพื่อให้น้ำหนักเป็นทศนิยม
    while ($weight > 0) {
        if ($weight > 0 && $weight <= 5) {
            $additional_fee += ($geo_id == 2) ? 190 : 270;
            $weight -= 5;
        } elseif ($weight > 5 && $weight <= 10) {
            $additional_fee += ($geo_id == 2) ? 230 : 290;
            $weight -= 10;
        } elseif ($weight > 10 && $weight <= 15) {
            $additional_fee += ($geo_id == 2) ? 260 : 330;
            $weight -= 15;
        } elseif ($weight > 15 && $weight <= 20) {
            $additional_fee += ($geo_id == 2) ? 290 : 370;
            $weight -= 20;
        } elseif ($weight > 20 && $weight <= 25) {
            $additional_fee += ($geo_id == 2) ? 330 : 430;
            $weight -= 25;
        } elseif ($weight > 25 && $weight <= 30) {
            $additional_fee += ($geo_id == 2) ? 390 : 490;
            $weight -= 30;
        } else {
            // หยุดการคำนวณเมื่อไม่ตรงตามเงื่อนไข
            break;
        }
    }
    return $additional_fee; 
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
    <style>
        body {
            background-color: #f8f9fa;
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
                            <?php 
                            $total_weight = 0; // น้ำหนักรวม
                            $grand_total = 0; // ยอดรวมทั้งหมด
                            while ($item = mysqli_fetch_assoc($items_result)): 
                                $item_weight = $item['quantity'] * $item['weight_per_item'];
                                $total_weight += $item_weight;

                                if ($item_weight >= 1000) {
                                    $quantity_display = number_format($item_weight / 1000, 2) . ' กก.';
                                    $price = $item['price'];
                                    $item_total = $price * ($item_weight / 1000);
                                } else {
                                    $quantity_display = number_format($item['quantity'], 0) . ' ชิ้น';
                                    $price = $item['price_per_piece'];
                                    $item_total = $price * $item['quantity'];
                                }
                                $grand_total += $item_total;
                            ?>
                            <tr>
                                <td><img src="./Admin/product/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" width="100"></td>
                                <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $quantity_display; ?></td>
                                <td><?php echo number_format($price, 2); ?></td>
                                <td><?php echo number_format($item_total, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <?php
                    $shippingFee = calculateShippingFee($total_weight/1000, $customer_id, $conn);
                    $shipping_info = "น้ำหนักรวม: " . number_format($total_weight / 1000, 2) . " กก. ค่าจัดส่ง: " . number_format($shippingFee, 2) . " บาท";
                    ?>

                    <div class="order-summary">
                        <h4>ยอดคำสั่งซื้อ: <span class="text-success"><?php echo number_format($grand_total, 2); ?> บาท</span></h4>
                        <h4><?php echo $shipping_info; ?></h4>
                        <h4>ยอดรวมทั้งหมด: <span class="text-danger"><?php echo number_format($grand_total + $shippingFee, 2); ?> บาท</span></h4>
                    </div>

                <!-- ข้อมูลสำหรับจัดส่ง -->
                <h4 class="mt-4">ข้อมูลสำหรับจัดส่ง:</h4>
                <div class="shipping-info">
                    <p><strong><?php echo htmlspecialchars($customer_name, ENT_QUOTES, 'UTF-8'); ?></strong> | <strong><?php echo htmlspecialchars($customer_phone, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <p>ที่อยู่: <?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($districtName, ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($amphurName, ENT_QUOTES, 'UTF-8') . ', ' . htmlspecialchars($provinceName, ENT_QUOTES, 'UTF-8') . ', รหัสไปรษณีย์: ' . htmlspecialchars($postal_code, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <!-- เพิ่ม QR Code และเลขบัญชีธนาคาร -->
                <div class="payment-info mt-4">
                    <h3>ข้อมูลการชำระเงิน</h3>
                    <p>กรุณาสแกน QR Code ด้านล่างเพื่อทำการชำระเงิน:</p>
                    <img src="./Admin/images/qr_code.png" alt="QR Code" width="200" class="img-fluid mb-3">
                    <p><strong>บัญชีธนาคาร:</strong> 407-8689387</p>
                    <p><strong>ชื่อบัญชี:</strong> ประภาภรณ์ จันปุ่ม</p>
                </div>

                <!-- อัปโหลดใบเสร็จ -->
                <div class="mb-3 mt-4">
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