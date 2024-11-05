<?php
session_start();
include 'connectDB.php';
include 'topnavbar.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>หน้าสินค้า - เว็บไซต์ขายเนื้อ</title>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS Links -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- JavaScript Links -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</head>
<body>
    <header class="bg-dark text-white text-center py-3">
        <h1>สินค้า</h1>
    </header>
    
    <main class="container mt-4">
    <section class="row">
    <?php
        // Query to retrieve product data including weight_per_item
        $query = "SELECT * FROM product";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $product_id = $row['product_id'];
                $stock_grams = $row['stock_quantity']; // Stock in grams
                $stock_kg = $row['stock_quantity'];
                $weight_per_piece = $row['weight_per_item']; // Retrieve weight per piece from the database

                echo '<div class="col-lg-3 col-md-4 col-sm-6 mb-4">';
                echo '<div class="card h-100 text-center">';
                echo '<img src="./Admin/product/' . htmlspecialchars($row['image']) . '" class="card-img-top" alt="' . htmlspecialchars($row['name']) . '" style="height: 200px; object-fit: cover;">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>';
                echo '<p class="card-text">ราคา: ' . number_format($row['price'], 2) . '฿ (1 กก.)</p>';

                // เงื่อนไขแสดงราคาต่อชิ้น
                if ($row['can_be_sold_as_piece'] == 1) {
                    echo '<p class="card-text">แยกชิ้น : ' . number_format($row['price_per_piece'], 2) . '฿ (1 ชิ้น)</p>';
                }

                echo '<p class="card-text">สต็อก: ' . number_format($stock_kg, 2) . ' (กก.)</p>';  // Show stock in kilograms
                echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal' . $product_id . '">เพิ่มในตะกร้า</button>';
                echo '</div>';
                echo '</div>';
                echo '</div>';

                // Modal for quantity selection
                echo '
                <div class="modal fade" id="productModal' . $product_id . '" tabindex="-1" aria-labelledby="productModalLabel' . $product_id . '" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="productModalLabel' . $product_id . '">เลือกปริมาณ - ' . htmlspecialchars($row['name']) . '</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="add_to_cart.php" method="GET" id="addToCartForm' . $product_id . '">
                                    <input type="hidden" name="product_id" value="' . $product_id . '">
                                    <input type="hidden" name="price" id="price_' . $product_id . '" value="' . $row['price'] . '"> <!-- ส่งราคาสินค้า -->
                                    <div class="mb-3">
                                        <label for="unit_' . $product_id . '">เลือกปริมาณ:</label>
                                        <select id="unit_' . $product_id . '" name="unit" class="form-select" onchange="updatePrice(' . $product_id . ', ' . ($row['can_be_sold_as_piece'] == 1 ? $row['price_per_piece'] : 0) . ', ' . $row['price'] . ')">
                                            <option value="1kg">1 กิโลกรัม</option>';
                if ($row['can_be_sold_as_piece'] == 1) {
                    echo '<option value="1piece">1 ชิ้น (' . number_format($weight_per_piece, 0) . ' กรัม)</option>';
                }
                echo '        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity">จำนวน:</label>
                                        <input type="number" id="quantity" name="quantity" min="1" value="1" class="form-control" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                        <button type="submit" class="btn btn-primary">ยืนยันการเพิ่มในตะกร้า</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                ';
            }
        } else {
            echo '<p class="text-center">ไม่พบสินค้า</p>';
        }
        ?>
<script>
    function updatePrice(productId, pricePerPiece, pricePerKg) {
        const unitSelect = document.getElementById('unit_' + productId);
        const priceInput = document.getElementById('price_' + productId);
        
        if (unitSelect.value === '1piece') {
            priceInput.value = pricePerPiece; // เปลี่ยนเป็นราคาชิ้น
        } else {
            priceInput.value = pricePerKg; // เปลี่ยนเป็นราคากิโลกรัม
        }
    }
</script>
    </section>
</main>
            
    <div class="cart-icon fixed-bottom mb-4 ms-4">
        <a href="cart.php" class="btn">
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
                    echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' . $row['item_count'] . '</span>';
                }
            }
            ?>
        </a>
    </div>
<?php
mysqli_close($conn);
include 'footer.php';
?>
