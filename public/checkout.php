<?php
session_start();
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$cart_id = $_SESSION['cart_id'] ?? null;

if (!$cart_id) {
    die("No cart found for this customer.");
}

// ดึงข้อมูลตะกร้าสินค้า
$cart_query = "SELECT * FROM cart WHERE cart_id = ? AND customer_id = ?";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, 'ii', $cart_id, $customer_id);
mysqli_stmt_execute($stmt);
$cart_result = mysqli_stmt_get_result($stmt);

if (!$cart_result || mysqli_num_rows($cart_result) == 0) {
    die("No cart found for this customer.");
}

// ดึงข้อมูลสินค้าจากตะกร้า
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

// ทำการชำระเงิน
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_date = date("Y-m-d H:i:s");
    $total_amount_query = "SELECT SUM(quantity * price) AS total_amount FROM cart_items WHERE cart_id = ?";
    $stmt = mysqli_prepare($conn, $total_amount_query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_id);
    mysqli_stmt_execute($stmt);
    $total_amount_result = mysqli_stmt_get_result($stmt);
    $total_amount_row = mysqli_fetch_assoc($total_amount_result);
    $total_amount = $total_amount_row['total_amount'];

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

    // จัดเก็บข้อมูลสลิปโอนเงิน
    if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['payment_slip']['tmp_name'];
        $file_name = $_FILES['payment_slip']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = "slip_$order_id.$file_ext";
        $upload_dir = 'uploads/';
        $upload_file = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $upload_file)) {
            $update_order_query = "UPDATE orders SET payment_slip = ? WHERE order_id = ?";
            $stmt = mysqli_prepare($conn, $update_order_query);
            mysqli_stmt_bind_param($stmt, 'si', $new_file_name, $order_id);
            if (!mysqli_stmt_execute($stmt)) {
                die("Error updating order with payment slip: " . mysqli_error($conn));
            }
            mysqli_close($conn);
            header("Location: confirmation.php");
            exit();
        } else {
            die("Error uploading payment slip.");
        }
    } else {
        die("No payment slip uploaded.");
    }
}
?>
<body>
    <header>
        <h1>Checkout</h1>
    </header>

    <main>
        <section class="checkout">
            <h2>Review Your Order</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <form action="checkout.php" method="post" enctype="multipart/form-data">
                <label for="payment_slip">Upload Payment Slip:</label>
                <input type="file" name="payment_slip" id="payment_slip" required>
                <button type="submit">Place Order</button>
            </form>
            <p><a href="cart.php">Return to Cart</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>
