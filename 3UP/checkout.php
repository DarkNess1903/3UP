<?php
session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลตะกร้าสินค้า
$cart_query = "SELECT * FROM cart WHERE customer_id = $customer_id ORDER BY created_at DESC LIMIT 1";
$cart_result = mysqli_query($conn, $cart_query);

if (!$cart_result) {
    die("Error fetching cart: " . mysqli_error($conn));
}

$cart = mysqli_fetch_assoc($cart_result);

if (!$cart) {
    die("No cart found.");
}

$cart_id = $cart['cart_id'];

// ดึงข้อมูลสินค้าจากตะกร้า
$items_query = "SELECT * FROM cart_items WHERE cart_id = $cart_id";
$items_result = mysqli_query($conn, $items_query);

if (!$items_result) {
    die("Error fetching items: " . mysqli_error($conn));
}

// ทำการชำระเงิน
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // สร้างคำสั่งซื้อใหม่
    $order_date = date("Y-m-d H:i:s");
    $total_amount_query = "SELECT SUM(quantity * price) AS total_amount FROM cart_items WHERE cart_id = $cart_id";
    $total_amount_result = mysqli_query($conn, $total_amount_query);
    $total_amount_row = mysqli_fetch_assoc($total_amount_result);
    $total_amount = $total_amount_row['total_amount'];

    $insert_order_query = "INSERT INTO orders (customer_id, order_date, total_amount, status, customer_address, payment_method)
                           VALUES ($customer_id, '$order_date', $total_amount, 'Pending', 'Address Here', 'Payment Method Here')";
    if (!mysqli_query($conn, $insert_order_query)) {
        die("Error inserting order: " . mysqli_error($conn));
    }
    $order_id = mysqli_insert_id($conn);

    // คัดลอกข้อมูลจาก cart_items ไปยัง orderdetails
    while ($item = mysqli_fetch_assoc($items_result)) {
        $insert_orderdetails_query = "INSERT INTO orderdetails (order_id, product_id, quantity, price)
                                      VALUES ($order_id, {$item['product_id']}, {$item['quantity']}, {$item['price']})";
        if (!mysqli_query($conn, $insert_orderdetails_query)) {
            die("Error inserting order details: " . mysqli_error($conn));
        }
    }

    // ลบข้อมูลในตะกร้า
    $delete_cart_items_query = "DELETE FROM cart_items WHERE cart_id = $cart_id";
    if (!mysqli_query($conn, $delete_cart_items_query)) {
        die("Error deleting cart items: " . mysqli_error($conn));
    }
    $delete_cart_query = "DELETE FROM cart WHERE cart_id = $cart_id";
    if (!mysqli_query($conn, $delete_cart_query)) {
        die("Error deleting cart: " . mysqli_error($conn));
    }

    mysqli_close($conn);
    header("Location: confirmation.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Meat Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Order Confirmation</h1>
    </header>

    <main>
        <section class="confirmation">
            <h2>Thank you for your purchase!</h2>
            <p>Your order has been placed successfully. Below are the details of your order:</p>
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
                            <td><?php echo htmlspecialchars($item['product_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <p><a href="index.php">Return to Home</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>
