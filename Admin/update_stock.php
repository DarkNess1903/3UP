<?php
include '../connectDB.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $new_stock = intval($_POST['new_stock']);

    $query = "UPDATE product SET stock_quantity = ? WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $new_stock, $product_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage_products.php");
        exit();
    } else {
        die("Error updating stock.");
    }
}
?>
