<?php
session_start();
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$cart_id = $_POST['cart_id'] ?? null;

if (!$cart_id) {
    die("No cart ID provided.");
}

// ตรวจสอบว่ามีข้อมูลในตะกร้าหรือไม่
$cart_query = "SELECT * FROM cart WHERE cart_id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, 'ii', $cart_id, $customer_id);
mysqli_stmt_execute($stmt);
$cart_result = mysqli_stmt_get_result($stmt);

if (!$cart_result || mysqli_num_rows($cart_result) == 0) {
    die("No cart found.");
}

// ดึงข้อมูลตะกร้า
$items_query = "SELECT ci.cart_item_id, p.name, p.image, ci.quantity, ci.price, (ci.quantity * ci.price) AS total
                FROM cart_items ci
                JOIN product p ON ci.product_id = p.product_id
                WHERE ci.cart_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, 'i', $cart_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);

if (!$items_result) {
    die("Error fetching items: " . mysqli_error($conn));
}

// หากส่งข้อมูลแบบ POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_date = date("Y-m-d H:i:s");
    $total_amount_query = "SELECT SUM(quantity * price) AS total_amount FROM cart_items WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $total_amount_query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_id);
    mysqli_stmt_execute($stmt);
    $total_amount_result = mysqli_stmt_get_result($stmt);
    $total_amount_row = mysqli_fetch_assoc($total_amount_result);
    $total_amount = $total_amount_row['total_amount'];

    // สร้างคำสั่งซื้อ
    $insert_order_query = "INSERT INTO orders (customer_id, order_date, total_amount, status, customer_address, payment_method)
                           VALUES (?, ?, ?, 'Pending', 'Address Here', 'Bank Transfer')";
    $stmt = mysqli_prepare($conn, $insert_order_query);
    mysqli_stmt_bind_param($stmt, 'isd', $customer_id, $order_date, $total_amount);
    if (!mysqli_stmt_execute($stmt)) {
        die("Error inserting order: " . mysqli_error($conn));
    }
    $order_id = mysqli_insert_id($conn);

    // คัดลอกข้อมูลจาก cart_items ไปยัง orderdetails
    $stmt = mysqli_prepare($conn, "INSERT INTO orderdetails (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    while ($item = mysqli_fetch_assoc($items_result)) {
        mysqli_stmt_bind_param($stmt, 'iiid', $order_id, $item['product_id'], $item['quantity'], $item['price']);
        if (!mysqli_stmt_execute($stmt)) {
            die("Error inserting order details: " . mysqli_error($conn));
        }
    }

    // ลบข้อมูลในตะกร้า
    $delete_cart_items_query = "DELETE FROM cart_items WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $delete_cart_items_query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_id);
    if (!mysqli_stmt_execute($stmt)) {
        die("Error deleting cart items: " . mysqli_error($conn));
    }
    $delete_cart_query = "DELETE FROM cart WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $delete_cart_query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_id);
    if (!mysqli_stmt_execute($stmt)) {
        die("Error deleting cart: " . mysqli_error($conn));
    }

    // ตรวจสอบการอัปโหลดสลิปโอนเงิน
    if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['payment_slip']['tmp_name'];
        $file_name = $_FILES['payment_slip']['name'];
        $upload_dir = 'uploads/';  // Directory to store uploaded files
        
        // ตรวจสอบว่ามีโฟลเดอร์ 'uploads' หรือไม่
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_path = $upload_dir . basename($file_name);
        
        if (move_uploaded_file($file_tmp, $file_path)) {
            $update_order_query = "UPDATE orders SET payment_slip = ? WHERE order_id = ?";
            $stmt = mysqli_prepare($conn, $update_order_query);
            mysqli_stmt_bind_param($stmt, 'si', $file_name, $order_id);
            if (!mysqli_stmt_execute($stmt)) {
                die("Error updating payment slip: " . mysqli_error($conn));
            }

            // แสดงข้อความยืนยันการสั่งซื้อ
            echo "<p>Order placed successfully. Your payment slip has been uploaded.</p>";

            // เปลี่ยนเส้นทางไปยังหน้าแรกหลังจาก 3 วินาที
            header("Refresh: 3; url=index.php");
            exit();
        } else {
            die("Error uploading payment slip.");
        }
    } else {
        die("No payment slip uploaded or upload error.");
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Payment Slip - Meat Store</title>
</head>
<body>
    <header>
        <h1>Upload Payment Slip</h1>
    </header>

    <main>
        <section class="upload-slip">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_id, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="payment_slip">Upload Payment Slip:</label>
                <input type="file" name="payment_slip" id="payment_slip" required>
                <button type="submit">Submit</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>
