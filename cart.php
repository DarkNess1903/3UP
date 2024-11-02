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
    $items_query = "SELECT ci.cart_item_id, p.name, p.image, ci.quantity, ci.price, p.price_per_piece, (ci.quantity * ci.price) AS total, p.stock_quantity, p.weight_per_item
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
        // คำนวณยอดรวมที่ถูกต้อง
        if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
            // คำนวณจากราคาเป็นกิโลกรัม
            $item_total = ($item['price'] * ($item['quantity'] * $item['weight_per_item'] / 1000));
        } else {
            // คำนวณจากราคาเป็นชิ้น
            $item_total = ($item['price_per_piece'] * $item['quantity']);
        }
        $grand_total += $item_total;
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
    <title>ตะกร้าสินค้า</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header class="bg-dark text-white text-center py-3">
        <h1>ตะกร้าสินค้าของคุณ</h1>
    </header>
    <main class="container mt-4">
        <section class="cart">
            <h2>รายการสินค้าในตะกร้า</h2>
            <?php if ($cart): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>รูปภาพ</th>
                            <th>สินค้า</th>
                            <th>จำนวน</th> <!-- แก้ไขหัวข้อที่นี่ -->
                            <th>ราคา</th>
                            <th>รวมทั้งหมด</th>
                            <th>สต็อก</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <tr>
                            <td>
                                <img src="./Admin/product/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    width="100">
                            </td>
                            <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <form action="update_cart.php" method="post" class="d-flex align-items-center">
                                    <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($item['cart_item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" name="action" value="decrease" class="btn btn-outline-secondary">-</button>
                                    <input type="text" name="quantity" 
                                        value="<?php 
                                            // แสดงจำนวนตามเงื่อนไข
                                            if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
                                                echo number_format($item['quantity'] * $item['weight_per_item'] / 1000) . ' กก.'; // แสดงเป็นกิโลกรัม
                                            } else {
                                                echo number_format($item['quantity'], 0) . ' ชิ้น'; // แสดงเป็นจำนวนชิ้น
                                            }
                                        ?>" 
                                        class="form-control mx-2" style="width: 80px; text-align: center;" readonly>
                                    <button type="submit" name="action" value="increase" class="btn btn-outline-secondary">+</button>
                                </form>
                            </td>
                            <td>
                                <?php
                                // แสดงราคาให้ถูกต้อง
                                if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
                                    echo number_format($item['price'], 2); // แสดงราคาเป็นกิโลกรัม
                                } else {
                                    echo number_format($item['price_per_piece'], 2); // แสดงราคาเป็นชิ้น
                                }                                
                                ?>
                            </td>
                            <td>
                                <?php
                                // คำนวณยอดรวมที่ถูกต้อง
                                if ($item['quantity'] * $item['weight_per_item'] >= 1000) {
                                    // คำนวณยอดรวมสำหรับกิโลกรัม
                                    echo number_format(($item['price'] * ($item['quantity'] * $item['weight_per_item'] / 1000)), 2); // ยอดรวมเป็นกิโลกรัม
                                } else {
                                    // คำนวณยอดรวมสำหรับชิ้น
                                    echo number_format(($item['price_per_piece'] * $item['quantity']), 2); // ยอดรวมเป็นชิ้น
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['stock_quantity'], ENT_QUOTES, 'UTF-8'); ?></td> 
                            <td>
                                <a href="remove_from_cart.php?cart_item_id=<?php echo htmlspecialchars($item['cart_item_id'], ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?')">
                                    <i class="fas fa-trash-alt" title="ลบ"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <p><strong>รวมทั้งหมด: <?php echo number_format($grand_total, 2); ?></strong></p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                    ชำระเงิน
                </button>
            <?php else: ?>
                <p>ตะกร้าสินค้าของคุณว่างเปล่า</p>
            <?php endif; ?>
        </section>
    </main>

    <!-- Modal ยืนยันการสั่งซื้อ -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">ยืนยันการสั่งซื้อ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    คุณแน่ใจหรือไม่ว่าต้องการสั่งซื้อรายการนี้?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <form action="confirm_checkout.php" method="post">
                        <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_id, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-primary">ยืนยัน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    include 'footer.php';
    mysqli_close($conn);
    ?>
</body>
</html>

