<?php

include 'topnavbar.php';
include 'connectDB.php';

// ดึงข้อมูลคำสั่งซื้อทั้งหมด
$query = "SELECT orders.order_id, orders.total_amount, orders.status, orders.order_date, customer.name 
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

        @media (max-width: 768px) {
            h1 {
                font-size: 24px;
            }

            table th, table td {
                font-size: 12px;
            }

            .btn {
                padding: 5px 10px;
                font-size: 12px;
            }
        }

        @media (max-width: 576px) {
            h1 {
                font-size: 20px;
            }

            table th, table td {
                font-size: 10px;
            }

            .btn {
                padding: 4px 8px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <h1 class="text-center mb-4">จัดการคำสั่งซื้อ</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>เลขคำสั่งซื้อ</th>
                        <th>ชื่อผู้สั่งซื้อ</th>
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
                        <td><?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo date('d-m-Y H:i:s', strtotime($order['order_date'])); ?></td>
                        <td>
                            <a href="view_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-info">
                                <i class="fas fa-eye"></i> <!-- ไอคอนรายละเอียด -->
                            </a>
                            <?php if ($order['status'] === 'กำลังจัดส่ง'): ?>
                                <button class="btn btn-success completeOrderBtn" data-order-id="<?php echo $order['order_id']; ?>">
                                    <i class="fas fa-check"></i> <!-- ไอคอนเสร็จสิ้น -->
                                </button>
                            <?php endif; ?>
                            <?php if ($order['status'] === 'รอตรวจสอบ'): // ตรวจสอบสถานะก่อนแสดงปุ่มลบ ?>
                                <button class="btn btn-danger deleteOrderBtn" data-order-id="<?php echo $order['order_id']; ?>">
                                    <i class="fas fa-trash-alt"></i> <!-- ไอคอนลบ -->
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

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
                        if (response.success) {
                            alert('สถานะคำสั่งซื้อลงวันที่เรียบร้อยแล้ว');
                            window.location.reload(); // รีเฟรชหน้าเพื่ออัปเดตข้อมูล
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
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
