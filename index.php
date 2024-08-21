<?php
session_start();
include 'connectDB.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าสินค้า - เว็บไซต์ขายเนื้อ</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- เนื้อหาของหน้าเว็บไซต์ -->
    <header>
        <h1>Welcome to Our Meat Store</h1>
        <nav>
            <ul>
                <li><a href="index.php">หน้าแรก</a></li>
                <li><a href="contact.php">ติดต่อเรา</a></li>
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <li><a href="order_history.php">ประวัติการสั่งซื้อ</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="auth-links">
            <?php
            if (isset($_SESSION['customer_id'])) {
                // ดึงชื่อผู้ใช้จากฐานข้อมูลเพื่อแสดงผล
                $customer_id = $_SESSION['customer_id'];
                $query = "SELECT name FROM customer WHERE customer_id = $customer_id";
                $result = mysqli_query($conn, $query);
                if ($row = mysqli_fetch_assoc($result)) {
                    echo "Welcome, " . htmlspecialchars($row['name']);
                }
                echo " <a href='logout.php'>Logout</a>";
            } else {
                echo "<a href='login.php'>Login</a> or <a href='register.php'>Register</a>";
            }
            ?>
        </div>
    </header>

    <main>
        <section class="product-list">
            <?php
            // Fetch products from the database
            $query = "SELECT * FROM product";
            $result = mysqli_query($conn, $query);

            // Check if there are products
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="product">';
                    echo '<img src="uploads/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                    echo '<h2>' . htmlspecialchars($row['name']) . '</h2>';
                    echo '<p>Price: ฿' . number_format($row['price'], 2) . '</p>';
                    echo '<p>' . htmlspecialchars($row['details']) . '</p>';
                    echo '<a href="add_to_cart.php?product_id=' . $row['product_id'] . '">Add to Cart</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No products found.</p>';
            }
            ?>
        </section>
    </main>

    <!-- ไอคอนตะกร้า -->
    <div class="cart-icon">
        <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
            <?php
            if (isset($_SESSION['cart_id'])) {
                $cart_id = $_SESSION['cart_id'];
                $query = "SELECT COUNT(*) AS item_count FROM cart_items WHERE cart_id = $cart_id";
                $result = mysqli_query($conn, $query);
                if ($row = mysqli_fetch_assoc($result)) {
                    echo '<span class="cart-count">' . $row['item_count'] . '</span>';
                } else {
                    echo '<span class="cart-count">0</span>';
                }
            } else {
                echo '<span class="cart-count">0</span>';
            }
            ?>
        </a>
    </div>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูลหลังจากจบการใช้งาน
mysqli_close($conn);
?>
