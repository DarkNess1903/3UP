<?php
include 'topnavbar.php'; // รวมไฟล์เมนูด้านบน
include 'connectDB.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามี customer_id ในพารามิเตอร์ GET หรือไม่
if (!isset($_GET['customer_id'])) {
    echo "Customer ID is missing.";
    exit();
}

$customer_id = intval($_GET['customer_id']); // แปลง customer_id เป็นจำนวนเต็ม

// ดึงข้อมูลลูกค้าจากฐานข้อมูล
$customer_query = "SELECT c.customer_id, c.name, c.phone, c.address, c.province_id, c.amphur_id 
                   FROM customer c 
                   WHERE c.customer_id = ?";
$stmt = mysqli_prepare($conn, $customer_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $customer_id, $name, $phone, $address, $province_id, $amphur_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ดึงจังหวัดทั้งหมด
$province_query = "SELECT provinceID, provinceName FROM province";
$province_result = mysqli_query($conn, $province_query);

// ดึงอำเภอทั้งหมดที่ตรงกับ province_id
$amphur_query = "SELECT amphurID, amphurName FROM amphur WHERE provinceID = ?";
$amphur_stmt = mysqli_prepare($conn, $amphur_query);
mysqli_stmt_bind_param($amphur_stmt, 's', $province_id);
mysqli_stmt_execute($amphur_stmt);
$amphur_result = mysqli_stmt_get_result($amphur_stmt);

// เมื่อมีการส่งฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $province_id = $_POST['province_id'];
    $amphur_id = $_POST['amphur_id'];

    // อัปเดตข้อมูลลูกค้าในฐานข้อมูล
    $update_query = "UPDATE customer SET name = ?, phone = ?, address = ?, province_id = ?, amphur_id = ? WHERE customer_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'sssssi', $name, $phone, $address, $province_id, $amphur_id, $customer_id);

    if (mysqli_stmt_execute($update_stmt)) {
        echo "<script>alert('ข้อมูลลูกค้าอัปเดตเรียบร้อย'); window.location.href='correct_customer.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
    mysqli_stmt_close($update_stmt);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลลูกค้า</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @media (max-width: 576px) {
            h1 {
                font-size: 24px;
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
        <h1 class="text-center mb-4">แก้ไขข้อมูลลูกค้า</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">ชื่อ:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์:</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">ที่อยู่:</label>
                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
                <label for="province_id">จังหวัด:</label>
                <select class="form-control" id="province_id" name="province_id" required>
                    <option value="">-- เลือกจังหวัด --</option>
                    <?php while ($row = mysqli_fetch_assoc($province_result)): ?>
                        <option value="<?php echo $row['provinceID']; ?>" <?php echo ($row['provinceID'] == $province_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['provinceName'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="amphur_id">อำเภอ:</label>
                <select class="form-control" id="amphur_id" name="amphur_id" required>
                    <option value="">-- เลือกอำเภอ --</option>
                    <?php while ($row = mysqli_fetch_assoc($amphur_result)): ?>
                        <option value="<?php echo $row['amphurID']; ?>" <?php echo ($row['amphurID'] == $amphur_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['amphurName'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="correct_customer.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูลหลังจากการประมวลผลทั้งหมด
mysqli_close($conn);
?>
