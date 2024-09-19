<?php
session_start();
include 'topnavbar.php';
include '../connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูลคำสั่งซื้อทั้งหมด
$query = "SELECT orders.order_id, orders.total_amount, orders.status, orders.order_date, customer.name, customer.email 
          FROM orders 
          JOIN customer ON orders.customer_id = customer.customer_id";
$result = mysqli_query($conn, $query);

// ตรวจสอบข้อผิดพลาดในการดำเนินการคำสั่ง SQL
if (!$result) {
    die("Error executing query: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>จัดการคำสั่งซื้อ</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
                    <th>วันที่/เวลา</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 2); ?> บาท</td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo date('d-m-Y H:i:s', strtotime($order['order_date'])); ?></td>
                        <td>
                            <a href="view_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-view">รายละเอียด</a>
                            <?php if ($order['status'] === 'ตรวจสอบแล้วกำลังดำเนินการ'): ?>
                                <button class="btn btn-success completeOrderBtn" data-order-id="<?php echo $order['order_id']; ?>">เสร็จสิ้น</button>
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

        $('.completeOrderBtn').on('click', function() {
            var orderId = $(this).data('order-id');
            if (confirm('คุณแน่ใจว่าต้องการทำเครื่องหมายว่าออเดอร์เสร็จสิ้น?')) {
                $.ajax({
                    url: 'update_status.php',
                    method: 'POST',
                    data: {
                        order_id: orderId,
                        status: 'Order completed'
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response); // ตรวจสอบข้อมูลที่ได้รับจากเซิร์ฟเวอร์
                        if (response.success) {
                            alert('สถานะคำสั่งซื้อลงวันที่เรียบร้อยแล้ว');
                            window.location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error details:', textStatus, errorThrown); // ตรวจสอบรายละเอียดข้อผิดพลาด
                        alert('เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>

<?php
// ปิดการเชื่อมต่อ
mysqli_close($conn);
?>
