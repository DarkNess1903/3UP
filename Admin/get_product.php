<?php
session_start();
include 'connectDB.php';

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    die("Invalid product ID.");
}

$query = "SELECT * FROM product WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Product not found']);
}

mysqli_stmt_close($stmt);
?>
