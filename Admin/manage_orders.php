<?php
session_start();
include 'topnavbar.php';
include '../connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูลคำสั่งซื้อทั้งหมด
$query = "SELECT * FROM orders";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำสั่งซื้อ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css"> <!-- ลิงก์ไปยังไฟล์ CSS ของคุณ -->
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f4f4f4;
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-view {
            background-color: #007bff;
        }
        .btn-update {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <header>
        <!-- ใส่ Navbar ของคุณที่นี่ -->
    </header>

    <div class="container">
        <h1>จัดการคำสั่งซื้อ</h1>
        <table>
            <thead>
                <tr>
                    <th>หมายเลขคำสั่งซื้อ</th>
                    <th>ชื่อผู้สั่งซื้อ</th>
                    <th>อีเมล</th>
                    <th>ยอดรวม</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 2); ?> บาท</td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td>
                            <a href="view_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-view">ดูรายละเอียด</a>
                            <?php if ($order['status'] === 'Completed checking of slip'): ?>
                                <button class="btn btn-update" data-order-id="<?php echo $order['order_id']; ?>" onclick="updateOrderStatus(this)">อัปเดตสถานะ</button>
                            <?php endif; ?>
                            <!-- ปุ่มลบออเดอร์ -->
                        <button class="btn btn-danger deleteOrderBtn" data-order-id="<?php echo $order['order_id']; ?>">ลบ</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <!-- ใส่ Footer ของคุณที่นี่ -->
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function updateOrderStatus(button) {
        var orderId = $(button).data('order-id');
        $.ajax({
            url: 'update_order_status1.php',
            method: 'POST',
            data: {
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    alert('สถานะคำสั่งซื้อได้รับการอัปเดต');
                    location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
                } else {
                    alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์');
            }
        });
    }
    </script>
</body>
</html>

<script>
$(document).ready(function() {
    $('.deleteOrderBtn').on('click', function() {
        var orderId = $(this).data('order-id');
        if (confirm('คุณแน่ใจว่าต้องการลบคำสั่งซื้อนี้?')) {
            $.ajax({
                url: 'delete_order.php',
                method: 'POST',
                data: { order_id: orderId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('คำสั่งซื้อลบเรียบร้อยแล้ว');
                        window.location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + response.message);
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์');
                }
            });
        }
    });
});
</script>

<?php
// ปิดการเชื่อมต่อ
mysqli_close($conn);
?>


