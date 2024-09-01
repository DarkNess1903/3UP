<?php
include '../connectDB.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการเข้าสู่ระบบ
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/styles.css">
    <script src="../public/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-md navbar-light bg-light border-bottom">
        <div class="container">
            <a href="index.php" class="navbar-brand d-flex align-items-center">
                <img src="../public/images/logo.jpg" alt="Logo" width="100" height="100" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">สินค้า</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">ตะกร้าสินค้า</a></li>
                    <li class="nav-item"><a class="nav-link" href="order_history.php">ประวัติการสั่งซื้อ</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">ตั้งค่าบัญชี</a></li>
                </ul>
            </div>
            <div class="auth-links ms-auto">
                <?php
                if (isset($_SESSION['customer_id'])) {
                    $customer_id = $_SESSION['customer_id'];
                    $query = "SELECT name FROM customer WHERE customer_id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($row = mysqli_fetch_assoc($result)) {
                        echo "<span class='navbar-text me-3'>User " . htmlspecialchars($row['name']) . "</span>";
                    }
                    echo "<a class='btn btn-outline-danger' href='logout.php'>Logout</a>";
                } else {
                    echo "<a class='btn btn-outline-primary me-2' href='login.php'>Login</a>";
                    echo "<a class='btn btn-outline-success' href='register.php'>Register</a>";
                }
                ?>
            </div>
        </div>
    </nav>
</header>