<?php
include 'topnavbar.php'; // รวมไฟล์เมนูด้านบน
include 'connectDB.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลลูกค้าทั้งหมดจากฐานข้อมูล
$customer_query = "SELECT c.customer_id, c.name, c.phone, c.address, p.provinceName, a.amphurName 
                   FROM customer c
                   JOIN province p ON c.province_id = p.provinceID
                   JOIN amphur a ON c.amphur_id = a.amphurID";
$stmt = mysqli_prepare($conn, $customer_query);

// ตรวจสอบการเตรียมคำสั่ง SQL
if (!$stmt) {
    die("Failed to prepare the SQL statement: " . mysqli_error($conn));
}

mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $customer_id, $name, $phone, $address, $province_name, $amphur_name);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>ข้อมูลลูกค้า</title>
    <style>
        @media (max-width: 576px) {
            h1 {
                font-size: 24px;
            }

            table th, table td {
                font-size: 12px;
            }

            .btn {
                font-size: 12px;
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">ข้อมูลลูกค้า</h1>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Customer ID</th>
                        <th>ชื่อ</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>ที่อยู่</th>
                        <th>จังหวัด</th>
                        <th>อำเภอ</th>
                        <th>แก้ไข</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while (mysqli_stmt_fetch($stmt)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer_id, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($province_name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($amphur_name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <a href="edit_customer.php?customer_id=<?php echo $customer_id; ?>" class="btn btn-primary">แก้ไข</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูลหลังจากการประมวลผลทั้งหมด
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
