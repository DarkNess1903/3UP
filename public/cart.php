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
    <title>Cart</title>
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
                        <th>Stock</th>
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
                                text-align: center; 
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
                            <td><?php echo htmlspecialchars($item['stock_quantity'], ENT_QUOTES, 'UTF-8'); ?></td> 
                            <td>
                                <a href="remove_from_cart.php?cart_item_id=<?php echo htmlspecialchars($item['cart_item_id'], ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('Are you sure you want to remove this item?')">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
            </table>
            <p><strong>Grand Total: <?php echo number_format($grand_total, 2); ?></strong></p>
            <!-- ปุ่ม Checkout ที่เปิดโมดัลยืนยัน -->
            <button type="button" class="checkout-btn btn btn-primary" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                Checkout
            </button>
            <?php else: ?>
            <p>Your cart is empty.</p>
            <?php endif; ?>
        </section>
    </main>

    <!-- Modal ยืนยันการสั่งซื้อ -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Changed to modal-lg for a larger size -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Confirm Checkout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to place this order?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="confirm_checkout.php" method="post">
                        <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_id, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
include 'footer.php';
mysqli_close($conn);
?>
