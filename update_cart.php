<?php
include 'connectDB.php';

session_start();
if (!isset($_SESSION['customer_id'])) {
    echo "Please log in to update your cart.";
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cart_item_id = intval($_POST['cart_item_id']);
    $action = $_POST['action'];

    if ($action == 'increase') {
        $query = "UPDATE cart_items SET quantity = quantity + 1 WHERE cart_item_id = $cart_item_id";
    } elseif ($action == 'decrease') {
        $query = "UPDATE cart_items SET quantity = quantity - 1 WHERE cart_item_id = $cart_item_id AND quantity > 1";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: cart.php"); // กลับไปที่หน้าตะกร้า
        exit();
    } else {
        die("Error updating cart: " . mysqli_error($conn));
    }
}

mysqli_close($conn);
?>
