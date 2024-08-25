<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

// ตรวจสอบการลบคำสั่งซื้อ
if (isset($_GET['delete_order_id'])) {
    $delete_order_id = intval($_GET['delete_order_id']);

    // ลบคำสั่งซื้อจากฐานข้อมูล
    $delete_query = "DELETE FROM orders WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $delete_order_id);

    if (mysqli_stmt_execute($stmt)) {
        // ลบสำเร็จ
        header("Location: manage_orders.php");
        exit();
    } else {
        echo "Error deleting order: " . mysqli_error($conn);
    }
}

// ดึงข้อมูลคำสั่งซื้อทั้งหมดจากฐานข้อมูล
$query = "SELECT * FROM orders";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="styles.css">    
</head>
<body>
    <header>
        <h1>Manage Orders</h1>
    </header>
    <main>
        <section>
            <h2>Order List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer ID</th>
                        <th>Total Amount</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
                            <td><?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <a href="view_order.php?order_id=<?php echo htmlspecialchars($row['order_id']); ?>">View</a> |
                                <a href="manage_orders.php?delete_order_id=<?php echo htmlspecialchars($row['order_id']); ?>" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Your Store. All rights reserved.</p>
    </footer>
</body>
</html>
