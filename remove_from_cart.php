<?php
include 'connectDB.php';

session_start();
if (!isset($_SESSION['customer_id'])) {
    echo "Please log in to remove items from your cart.";
    header("Location: login.php");
    exit();
}

if (isset($_GET['cart_item_id'])) {
    $cart_item_id = intval($_GET['cart_item_id']);
    
    $query = "DELETE FROM cart_items WHERE cart_item_id = $cart_item_id";

    if (mysqli_query($conn, $query)) {
        header("Location: cart.php"); // กลับไปที่หน้าตะกร้า
        exit();
    } else {
        die("Error removing item from cart: " . mysqli_error($conn));
    }
}

mysqli_close($conn);
?>
