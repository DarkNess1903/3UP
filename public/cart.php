<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

$customer_id = $_SESSION['customer_id'];

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
    $items_query = "SELECT ci.cart_item_id, p.name, p.image, ci.quantity, ci.price, (ci.quantity * ci.price) AS total, p.stock_quantity
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
        $grand_total += $item['total'];
    }

    // Reset the result pointer to fetch items again
    mysqli_data_seek($items_result, 0);
} else {
    $items_result = [];
    $grand_total = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <script src="js/scripts.js"></script>
</head>
<body>
    <header>
        <h1>Your Cart</h1>
    </header>
    <main>
        <section class="cart">
            <h2>Cart Items</h2>
            <?php if ($cart): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Stock</th> <!-- เพิ่มคอลัมน์สต็อก -->
                        <th>Action</th>
                    </tr>
                </thead>
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <tr>
                            <td><img src="../product/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" width="100"></td>
                            <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                            <style>
                            .quantity-controls {
                                text-align: center; /* จัดตำแหน่งให้อยู่กลาง */
                            }
                            .quantity-controls button,
                            .quantity-controls input {
                                display: inline-block;
                                margin: 0 5px;
                                vertical-align: middle;
                            }
                            .quantity-controls input {
                                width: 40px;
                                text-align: center;
                                border: none;
                            }
                        </style>
                                <form action="update_cart.php" method="post">
                                    <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($item['cart_item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="quantity-container">
                                        <button type="submit" name="action" value="decrease">-</button>
                                        <?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?>
                                        <button type="submit" name="action" value="increase">+</button>
                                    </div>
                                </form>
                            </td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['total'], 2); ?></td>
                            <td><?php echo htmlspecialchars($item['stock_quantity'], ENT_QUOTES, 'UTF-8'); ?></td> <!-- แสดงจำนวนสต็อก -->
                            <td>
                                <a href="remove_from_cart.php?cart_item_id=<?php echo htmlspecialchars($item['cart_item_id'], ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Are you sure you want to remove this item?')">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
            </table>
            <p><strong>Grand Total: <?php echo number_format($grand_total, 2); ?></strong></p>
            <form action="confirm_checkout.php" method="post">
                <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_id, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="submit" value="Checkout" class="checkout-btn">
            </form>
            <?php else: ?>
            <p>Your cart is empty.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>

<?php
include 'footer.php';
mysqli_close($conn);
?>
