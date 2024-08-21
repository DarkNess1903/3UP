<?php
session_start();
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบของ Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="manage_customers.php">Manage Customers</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Welcome to Admin Dashboard</h2>
            <p>Here you can manage your store's products, orders, and customers.</p>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Your Store. All rights reserved.</p>
    </footer>
</body>
</html>
