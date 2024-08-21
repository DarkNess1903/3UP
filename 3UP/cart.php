<?php
session_start();
include 'connectDB.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    echo "Please log in to add items to your cart.";
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลตะกร้าสินค้า
$cart_query = "SELECT * FROM cart WHERE customer_id = $customer_id ORDER BY created_at DESC LIMIT 1";
$cart_result = mysqli_query($conn, $cart_query);

if (!$cart_result) {
    echo "Error fetching cart: " . mysqli_error($conn);
    exit();
}

$cart = mysqli_fetch_assoc($cart_result);

if ($cart) {
    $cart_id = $cart['cart_id'];

    // ดึงข้อมูลสินค้าจากตะกร้า
    $items_query = "SELECT ci.cart_item_id, p.name, p.image, ci.quantity, ci.price, (ci.quantity * ci.price) AS total
                    FROM cart_items ci
                    JOIN product p ON ci.product_id = p.product_id
                    WHERE ci.cart_id = $cart_id";
    $items_result = mysqli_query($conn, $items_query);

    if (!$items_result) {
        echo "Error fetching items: " . mysqli_error($conn);
        exit();
    }
} else {
    $items_result = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Meat Store</title>
    <link rel="stylesheet" href="styles.css">
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <tr>
                            <td><img src="path/to/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="100"></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>
                                <form action="update_cart.php" method="post">
                                    <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($item['cart_item_id']); ?>">
                                    <button type="submit" name="action" value="decrease">-</button>
                                    <?php echo htmlspecialchars($item['quantity']); ?>
                                    <button type="submit" name="action" value="increase">+</button>
                                </form>
                            </td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo number_format($item['total'], 2); ?></td>
                            <td>
                                <a href="remove_from_cart.php?cart_item_id=<?php echo $item['cart_item_id']; ?>" onclick="return confirm('Are you sure you want to remove this item?')">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>Your cart is empty.</p>
            <?php endif; ?>
            <form action="checkout.php" method="post">
                <input type="submit" value="Checkout">
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
mysqli_close($conn);
?>
