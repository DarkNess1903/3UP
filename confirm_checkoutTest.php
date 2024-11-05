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

// ตรวจสอบว่ามีการส่ง cart_id มาหรือไม่
if (!isset($_POST['cart_id'])) {
    echo "ไม่มีข้อมูลตะกร้า";
    exit();
}

$cart_id = $_POST['cart_id'];

// ดึงข้อมูลลูกค้า
$customer_query = "SELECT name, phone, address, province_id, amphur_id FROM customer WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $customer_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$customer_result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($customer_result);

// ดึงข้อมูลสินค้าจากตะกร้า
$items_query = "SELECT ci.cart_item_id, p.name, p.image, ci.quantity, ci.price, p.price_per_piece, p.weight_per_item
                FROM cart_items ci
                JOIN product p ON ci.product_id = p.product_id
                WHERE ci.cart_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, 'i', $cart_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);

// คำนวณยอดรวมและน้ำหนักรวม
$grand_total = 0;
$total_weight = 0;

while ($item = mysqli_fetch_assoc($items_result)) {
    if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
        // คำนวณจากราคาเป็นกิโลกรัม
        $item_total = ($item['price'] * ($item['quantity'] * $item['weight_per_item'] / 1000));
    } else {
        // คำนวณจากราคาเป็นชิ้น
        $item_total = ($item['price_per_piece'] * $item['quantity']);
    }
    $grand_total += $item_total;
    $total_weight += ($item['weight_per_item'] * $item['quantity']);
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
    $base_fee = 0;
    $additional_fee = 0;

    if ($weight > 30) {
        // คำนวณค่าจัดส่งสำหรับน้ำหนักไม่เกิน 30 กิโลกรัม
        $base_fee = calculateBaseFee(30, $geo_id);
        // คำนวณค่าจัดส่งสำหรับน้ำหนักที่เกิน 30 กิโลกรัม
        $remaining_weight = $weight - 30; // หัก 30 ออก
        $additional_fee = calculateAdditionalFee($remaining_weight, $geo_id); // คำนวณค่าจัดส่งเพิ่มเติม
    } else {
        // น้ำหนักไม่เกิน 30 กิโลกรัม
        $base_fee = calculateBaseFee($weight, $geo_id);
    }

    // ตรวจสอบค่าฐาน ค่าจัดส่งห้ามเป็น 0
    if ($base_fee === 0 && $additional_fee === 0) {
        return 0; // ถ้าไม่ตรงตามเงื่อนไขจะคืนค่าจัดส่ง 0
    }

    return $base_fee + $additional_fee; 
}

function calculateBaseFee($weight, $geo_id) {
    if ($weight >= 0 && $weight <= 5) {
        return ($geo_id == 2) ? 190 : 270;
    } elseif ($weight >= 6 && $weight <= 10) {
        return ($geo_id == 2) ? 230 : 290;
    } elseif ($weight >= 11 && $weight <= 15) {
        return ($geo_id == 2) ? 260 : 330;
    } elseif ($weight >= 16 && $weight <= 20) {
        return ($geo_id == 2) ? 290 : 370;
    } elseif ($weight >= 21 && $weight <= 25) {
        return ($geo_id == 2) ? 330 : 430;
    } elseif ($weight >= 26 && $weight <= 30) {
        return ($geo_id == 2) ? 390 : 490;
    } elseif ($weight > 30) { // เพิ่มเงื่อนไขสำหรับน้ำหนักเกิน 30 กิโลกรัม
        return ($geo_id == 2) ? 490 : 590; // กำหนดค่าจัดส่งสำหรับน้ำหนักมากกว่า 30 กิโลกรัม
    }
    return 0; // ส่งค่าผิดพลาดเมื่อไม่ตรงตามเงื่อนไข
}

function calculateAdditionalFee($weight, $geo_id) {
    $additional_fee = 0;

    // คำนวณค่าจัดส่งเพิ่มเติมสำหรับน้ำหนักที่เกิน 30 กิโลกรัม
    while ($weight > 0) {
        if ($weight >= 0 && $weight <= 5) {
            $additional_fee += ($geo_id == 2) ? 190 : 270;
            $weight -= 5;
        } elseif ($weight >= 6 && $weight <= 10) {
            $additional_fee += ($geo_id == 2) ? 230 : 290;
            $weight -= 10;
        } elseif ($weight >= 11 && $weight <= 15) {
            $additional_fee += ($geo_id == 2) ? 260 : 330;
            $weight -= 15;
        } elseif ($weight >= 16 && $weight <= 20) {
            $additional_fee += ($geo_id == 2) ? 290 : 370;
            $weight -= 20;
        } elseif ($weight >= 21 && $weight <= 25) {
            $additional_fee += ($geo_id == 2) ? 330 : 430;
            $weight -= 25;
        } elseif ($weight >= 26 && $weight <= 30) {
            $additional_fee += ($geo_id == 2) ? 390 : 490;
            $weight -= 30;
        } else {
            // หยุดการคำนวณเมื่อไม่ตรงตามเงื่อนไข
            break;
        }
    }
    return $additional_fee; 
}

function sendLineNotify($message, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "message=" . $message);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// ตัวอย่างการใช้งานฟังก์ชันส่งข้อความ
$order_id = 96; // เลขออเดอร์
$order_time = date("Y-m-d H:i:s"); // เวลาที่สั่ง
$customer_name = htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); // ชื่อผู้สั่ง
$address = htmlspecialchars($customer['address'], ENT_QUOTES, 'UTF-8'); // ที่อยู่
$shippingFee = calculateShippingFeeForCart($customer_id, $conn); // ค่าจัดส่ง
$total_order_amount = $grand_total + $shippingFee; // ยอดรวมทั้งสิ้น

$message = "Order: 🔔 แจ้งเตือนออเดอร์ใหม่\n" .
           "เลขออเดอร์: $order_id\n" .
           "เวลาที่สั่ง: $order_time\n" .
           "📋 รายการสั่งซื้อ:\n";

while ($item = mysqli_fetch_assoc($items_result)) {
    $item_weight = $item['weight_per_item'] * $item['quantity'];
    $item_price = ($item_weight >= 1000) ? ($item['price'] * ($item_weight / 1000)) : ($item['price_per_piece'] * $item['quantity']);
    $message .= "- {$item['name']} จำนวน: " . number_format($item_weight / 1000, 2) . " กก., ราคา: " . number_format($item_price, 2) . " บาท\n";
}

$message .= "💰 ยอดสั่งซื้อ: " . number_format($grand_total, 2) . " บาท\n" .
            "🚚 ค่าส่ง: " . number_format($shippingFee, 2) . " บาท\n" .
            "💵 ยอดรวมทั้งสิ้น: " . number_format($total_order_amount, 2) . " บาท\n" .
            "📍 ที่อยู่ผู้สั่ง:\n" .
            "ชื่อ: $customer_name\n" .
            "ที่อยู่: $address";

$token = 'BKShK2Llhdrohu0Nwr9w5CdiAWVaBeFkG8KB4Ts0GWW'; // เปลี่ยนเป็น Token ที่ได้จาก LINE Notify
sendLineNotify($message, $token);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>ยืนยันการสั่งซื้อ</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="bg-dark text-white text-center py-3">
        <h1>ยืนยันการสั่งซื้อ</h1>
    </header>
    <main class="container mt-4">
        <section class="order-details">
            <h2>รายละเอียดการสั่งซื้อ</h2>
            <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>โทรศัพท์:</strong> <?php echo htmlspecialchars($customer['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($customer['address'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>น้ำหนักรวม:</strong> <?php echo number_format($total_weight, 2) . ' กรัม'; ?></p>
            <p><strong>ยอดรวม:</strong> <?php echo number_format($grand_total, 2); ?> บาท</p>
            <?php
            $shipping_fee = calculateShippingFeeForCart($customer_id, $conn);
            if ($shipping_fee == -1) {
                // จัดการข้อผิดพลาดเมื่อไม่พบข้อมูล
                echo "ไม่สามารถคำนวณค่าจัดส่งได้";
            } else {
                echo "ค่าจัดส่งคือ: " . number_format($shipping_fee, 2) . " บาท";
            }
            ?>

            <h3>รายการสินค้า</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>สินค้า</th>
                        <th>จำนวน</th>
                        <th>ราคา</th>
                        <th>รวม</th>
                        <th>รูปภาพ</th> <!-- เพิ่มคอลัมน์รูปภาพที่นี่ -->
                    </tr>
                </thead>
                <tbody>
                <?php
                // Reset the items_result pointer to fetch items again
                mysqli_data_seek($items_result, 0);
                while ($item = mysqli_fetch_assoc($items_result)):
                    // คำนวณยอดรวมที่ถูกต้อง
                    if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
                        $item_total = ($item['price'] * ($item['quantity'] * $item['weight_per_item'] / 1000));
                    } else {
                        $item_total = ($item['price_per_piece'] * $item['quantity']);
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo number_format($item_total, 2); ?></td>
                        <td>
                            <img src="./Admin/product/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 width="100">
                        </td> <!-- แสดงรูปภาพที่นี่ -->
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <h3>อัปโหลดสลิปการโอนเงิน</h3>
            <form action="upload_slip.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_id, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="file" name="slip" accept="image/*" required>
                <button type="submit" class="btn btn-success mt-2">อัปโหลด</button>
            </form>
        </section>
    </main>

    <?php
    include 'footer.php';
    mysqli_close($conn);
    ?>
</body>
</html>
