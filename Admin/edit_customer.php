<?php
session_start();
include 'topnavbar.php';
include '../connectDB.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลลูกค้าจากฐานข้อมูล
$customer_query = "SELECT name, phone, address, province_id, amphur_id FROM customer WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $customer_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $name, $phone, $address, $province_id, $amphur_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ดึงข้อมูลจังหวัดและอำเภอสำหรับแสดงในฟอร์ม
$provinces_query = "SELECT provinceID, provinceName FROM province";
$provinces_result = mysqli_query($conn, $provinces_query);

$amphurs_query = "SELECT amphurID, amphurName FROM amphur WHERE provinceID = ?";
$amphurs_stmt = mysqli_prepare($conn, $amphurs_query);
mysqli_stmt_bind_param($amphurs_stmt, 's', $province_id);
mysqli_stmt_execute($amphurs_stmt);
$amphurs_result = mysqli_stmt_get_result($amphurs_stmt);

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $province_id = $_POST['province_id'];
    $amphur_id = $_POST['amphur_id'];

    // อัปเดตข้อมูลลูกค้าในฐานข้อมูล
    $update_query = "UPDATE customer SET name = ?, phone = ?, address = ?, province_id = ?, amphur_id = ? WHERE customer_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'ssssii', $name, $phone, $address, $province_id, $amphur_id, $customer_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('อัปเดตข้อมูลลูกค้าเรียบร้อยแล้ว'); window.location.href='customer_info.php';</script>";
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลลูกค้า</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>แก้ไขข้อมูลลูกค้า</h1>
        <form action="edit_customer.php" method="post">
            <div class="mb-3">
                <label for="name" class="form-label">ชื่อ:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">เบอร์โทรศัพท์:</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">ที่อยู่:</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="amphur_id" class="form-label">อำเภอ:</label>
                <select class="form-control" id="amphur_id" name="amphur_id" required>
                    <option value="">เลือกอำเภอ</option>
                    <?php
                    while ($amphur = mysqli_fetch_assoc($amphurs_result)) {
                        $selected = ($amphur['amphurID'] == $amphur_id) ? 'selected' : '';
                        echo "<option value=\"{$amphur['amphurID']}\" $selected>{$amphur['amphurName']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="province_id" class="form-label">จังหวัด:</label>
                <select class="form-control" id="province_id" name="province_id" required>
                    <option value="">เลือกจังหวัด</option>
                    <?php
                    while ($province = mysqli_fetch_assoc($provinces_result)) {
                        $selected = ($province['provinceID'] == $province_id) ? 'selected' : '';
                        echo "<option value=\"{$province['provinceID']}\" $selected>{$province['provinceName']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
            <a href="customer_info.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
