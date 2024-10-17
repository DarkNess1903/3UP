<?php
include 'topnavbar.php'; // รวมไฟล์เมนูด้านบน
include 'connectDB.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลลูกค้าทั้งหมดจากฐานข้อมูล
$customer_query = "
    SELECT c.customer_id, c.name, c.phone, c.address, 
           p.PROVINCE_NAME, a.AMPHUR_NAME, d.DISTRICT_NAME, a.POSTCODE, c.created_at
    FROM customer c
    JOIN province p ON c.province_id = p.PROVINCE_ID
    JOIN amphur a ON c.amphur_id = a.AMPHUR_ID
    JOIN district d ON c.district_id = d.DISTRICT_ID
";

$stmt = mysqli_prepare($conn, $customer_query);

// ตรวจสอบการเตรียมคำสั่ง SQL
if (!$stmt) {
    die("Failed to prepare the SQL statement: " . mysqli_error($conn));
}

mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $customer_id, $name, $phone, $address, $province_name, $amphur_name, $district_name, $postcode, $created_at);
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
                        <th>เลข ID</th>
                        <th>ชื่อ</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>ที่อยู่</th>
                        <th>จังหวัด</th>
                        <th>อำเภอ</th>
                        <th>เขต/ตำบล</th>
                        <th>รหัสไปรษณีย์</th>
                        <th>วันที่สร้าง</th>
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
                        <td><?php echo htmlspecialchars($district_name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($postcode, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(date("d/m/Y H:i:s", strtotime($created_at)), ENT_QUOTES, 'UTF-8'); ?></td>
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
