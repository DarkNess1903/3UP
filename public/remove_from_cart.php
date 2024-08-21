<?php
include '../connectDB.php';

session_start();
if (!isset($_SESSION['customer_id'])) {
    echo "Please log in to remove items from your cart.";
    header("Location: login.php");
    exit();
}

if (isset($_GET['cart_item_id'])) {
    $cart_item_id = intval($_GET['cart_item_id']);

    // ดึงข้อมูลสินค้าจาก cart_items ก่อนลบ
    $cart_item_query = "SELECT product_id, quantity
                        FROM cart_items
                        WHERE cart_item_id = ?";
    $stmt = mysqli_prepare($conn, $cart_item_query);
    mysqli_stmt_bind_param($stmt, 'i', $cart_item_id);
    mysqli_stmt_execute($stmt);
    $cart_item_result = mysqli_stmt_get_result($stmt);

    if (!$cart_item_result) {
        die("Error fetching cart item: " . mysqli_error($conn));
    }

    $cart_item = mysqli_fetch_assoc($cart_item_result);

    if ($cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];

        // เพิ่มปริมาณสต็อกกลับไปหลังจากลบสินค้า
        $update_stock_query = "UPDATE product
                               SET stock_quantity = stock_quantity + ?
                               WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $update_stock_query);
        mysqli_stmt_bind_param($stmt, 'ii', $quantity, $product_id);
        mysqli_stmt_execute($stmt);

        // ลบรายการสินค้าออกจากตะกร้า
        $delete_query = "DELETE FROM cart_items WHERE cart_item_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $cart_item_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            header("Location: cart.php"); // กลับไปที่หน้าตะกร้า
            exit();
        } else {
            die("Error removing item from cart.");
        }
    } else {
        echo "Cart item not found.";
        exit();
    }
} else {
    echo "No cart item ID specified.";
    exit();
}

mysqli_close($conn);
?>
