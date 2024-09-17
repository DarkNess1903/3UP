<?php
session_start();
include '../connectDB.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$cart_id = $_POST['cart_id'] ?? null;

if (!$cart_id) {
    die("ไม่พบ Cart ID");
}

// ดึงข้อมูลที่อยู่ของลูกค้า
$address_query = "SELECT address FROM customer WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $address_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $address);
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
    $upload_dir = realpath(__DIR__ . '/../Admin/uploads/');
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
        $items_query = "SELECT product_id, quantity, price FROM cart_items WHERE cart_id = ?";
        $stmt = mysqli_prepare($conn, $items_query);
        mysqli_stmt_bind_param($stmt, 'i', $cart_id);
        mysqli_stmt_execute($stmt);
        $items_result = mysqli_stmt_get_result($stmt);

        while ($item = mysqli_fetch_assoc($items_result)) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            // แทรกข้อมูลลงใน orderdetails
            $orderdetails_query = "INSERT INTO orderdetails (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $orderdetails_query);
            mysqli_stmt_bind_param($stmt, 'iiid', $order_id, $product_id, $quantity, $item['price']);
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
        }

        // ลบข้อมูลที่เกี่ยวข้องใน cart_items
        $delete_cart_items_query = "DELETE FROM cart_items WHERE cart_id = ?";
        $stmt = mysqli_prepare($conn, $delete_cart_items_query);
        mysqli_stmt_bind_param($stmt, 'i', $cart_id);
        if (!mysqli_stmt_execute($stmt)) {
            die("ข้อผิดพลาดในการลบข้อมูลใน cart_items: " . mysqli_error($conn));
        }

        header("Location: thank_you.php"); // เปลี่ยนเส้นทางไปที่หน้าขอบคุณ
        exit();
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

include 'footer.php';
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Confirm Checkout - Meat Store</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header>
        <h1>Confirm Your Order</h1>
    </header>

    <main>
        <section class="confirm-checkout">
            <h2>Confirm Order</h2>
            <form action="confirm_checkout.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_id, ENT_QUOTES, 'UTF-8'); ?>">
                <h3>Your Cart Items:</h3>
                <?php if (mysqli_num_rows($items_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                            <tr>
                                <td><img src="../Admin/product/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" width="100"></td>
                                <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo number_format($item['total'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <p><strong>Grand Total: <?php echo number_format($grand_total, 2); ?></strong></p>
                <?php else: ?>
                <p>Your cart is empty.</p>
                <?php endif; ?>

                <!-- เพิ่ม QR Code และเลขบัญชีธนาคาร -->
                <div class="payment-info">
                    <h3>Payment Information</h3>
                    <p>Please scan the QR code below to make a payment:</p>
                    <img src="../Admin/images/qr_code.png" alt="QR Code" width="200">
                    <p><strong>Bank Account:</strong> 123-456-7890</p>
                    <p><strong>Bank Name:</strong> Example Bank</p>
                </div>

                <label for="payment_slip">Upload Payment Slip:</label>
                <input type="file" name="payment_slip" id="payment_slip" required>
                
                <button type="submit">Submit</button>
            </form>
            <p><a href="cart.php" class="return-to-cart">Return to Cart <i class="fas fa-arrow-left"></i></a></p>
        </section>
    </main>
</body>
</html>

<?php
include 'footer.php';
?>
