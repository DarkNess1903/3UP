<?php
include '../connectDB.php';

session_start();
if (!isset($_SESSION['customer_id'])) {
    echo "Please log in to update your cart.";
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cart_item_id = intval($_POST['cart_item_id']);
    $action = $_POST['action'];

    // ดึงข้อมูลสินค้าจาก cart_items
    $cart_item_query = "SELECT ci.cart_id, ci.product_id, ci.quantity, p.stock_quantity
                        FROM cart_items ci
                        JOIN product p ON ci.product_id = p.product_id
                        WHERE ci.cart_item_id = ?";
    $stmt = mysqli_prepare($conn, $cart_item_query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_item_id);
    mysqli_stmt_execute($stmt);
    $cart_item_result = mysqli_stmt_get_result($stmt);

    if (!$cart_item_result) {
        die("Error fetching cart item: " . mysqli_error($conn));
    }

    $cart_item = mysqli_fetch_assoc($cart_item_result);

    if ($cart_item) {
        $cart_id = $cart_item['cart_id'];
        $product_id = $cart_item['product_id'];
        $current_quantity = $cart_item['quantity'];
        $stock_quantity = $cart_item['stock_quantity'];

        if ($action == 'increase') {
            if ($current_quantity < $stock_quantity) {
                $query = "UPDATE cart_items SET quantity = quantity + 1 WHERE cart_item_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'i', $cart_item_id);
                mysqli_stmt_execute($stmt);
            } else {
                echo "Cannot increase quantity. Not enough stock.";
                exit();
            }
        } elseif ($action == 'decrease') {
            if ($current_quantity > 1) {
                $query = "UPDATE cart_items SET quantity = quantity - 1 WHERE cart_item_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'i', $cart_item_id);
                mysqli_stmt_execute($stmt);
            } elseif ($current_quantity == 1) {
                // หากจำนวนสินค้าลดลงเหลือ 1 ให้ลบรายการออกจากตะกร้า
                $query = "DELETE FROM cart_items WHERE cart_item_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'i', $cart_item_id);
                mysqli_stmt_execute($stmt);
            } else {
                echo "Cannot decrease quantity. Minimum quantity is 1.";
                exit();
            }
        }

        // Redirect back to cart page
        header("Location: cart.php");
        exit();
    } else {
        echo "Cart item not found.";
        exit();
    }
}

mysqli_close($conn);
?>
