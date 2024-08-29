<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าสินค้า - เว็บไซต์ขายเนื้อ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/scripts.js" defer></script>
</head>
<body>
    <header>
        <h1>Product</h1>
    </header>
    <main>
        <section class="product-list">
            <?php
            $query = "SELECT * FROM product";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="product">';
                    echo '<img src="../uploads/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" style="width: 150px;;height:150px;">';
                    echo '<h2>' . htmlspecialchars($row['name']) . '</h2>';
                    echo '<p>Price: ฿' . number_format($row['price'], 2) . '</p>';
                    echo '<p>Stock: ' . htmlspecialchars($row['stock_quantity']) . '</p>';
                    echo '<p>' . htmlspecialchars($row['details']) . '</p>';
                    echo '<a href="add_to_cart.php?product_id=' . $row['product_id'] . '" class="btn">Add to Cart</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No products found.</p>';
            }
            ?>
        </section>
    </main>
            
    <div class="cart-icon">
        <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
            <?php
            if (isset($_SESSION['cart_id'])) {
                $cart_id = $_SESSION['cart_id'];
                $query = "SELECT COUNT(*) AS item_count FROM cart_items WHERE cart_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'i', $cart_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
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
</body>
</html>

<?php
include 'footer.php';
mysqli_close($conn);
?>
