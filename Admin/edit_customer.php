<?php
include 'topnavbar.php'; // รวมไฟล์เมนูด้านบน
include 'connectDB.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าได้ส่ง customer_id มาหรือไม่
if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];

    // ดึงข้อมูลลูกค้าจากฐานข้อมูล
    $customer_query = "SELECT c.customer_id, c.name, c.phone, c.address, 
                              c.province_id, c.amphur_id, c.district_id, p.PROVINCE_NAME, 
                              a.AMPHUR_NAME, d.DISTRICT_NAME, a.POSTCODE 
                       FROM customer c
                       JOIN province p ON c.province_id = p.PROVINCE_ID
                       JOIN amphur a ON c.amphur_id = a.AMPHUR_ID
                       JOIN district d ON c.district_id = d.DISTRICT_ID
                       WHERE c.customer_id = ?";

    $stmt = mysqli_prepare($conn, $customer_query);
    mysqli_stmt_bind_param($stmt, 'i', $customer_id);

    // ตรวจสอบการเตรียมคำสั่ง SQL
    if (!$stmt) {
        die("Failed to prepare the SQL statement: " . mysqli_error($conn));
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $customer_id, $name, $phone, $address, $province_id, $amphur_id, $district_id, $province_name, $amphur_name, $district_name, $postcode);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
} else {
    die("No customer ID provided.");
}

// จัดการการอัพเดตข้อมูลลูกค้า
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $province_id = $_POST['province_id'];
    $amphur_id = $_POST['amphur_id'];
    $district_id = $_POST['district_id'];
    $postcode = $_POST['postcode']; // เพิ่มรหัสไปรษณีย์

    // ลบฟิลด์ postcode ออกจากคำสั่งอัปเดต
    $update_query = "UPDATE customer SET name = ?, phone = ?, address = ?, province_id = ?, amphur_id = ?, district_id = ? WHERE customer_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'sssssii', $name, $phone, $address, $province_id, $amphur_id, $district_id, $customer_id);

    if (mysqli_stmt_execute($stmt)) {
        // ถ้าอัพเดตสำเร็จ
        echo "<script>alert('ข้อมูลลูกค้าอัพเดตเรียบร้อย'); window.location.href='correct_customer.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>แก้ไขข้อมูลลูกค้า</title>
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">แก้ไขข้อมูลลูกค้า</h1>
        <form method="POST">
            <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="mb-3">
                <label for="name" class="form-label">ชื่อ</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">ที่อยู่</label>
                <textarea class="form-control" id="address" name="address" required><?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="province_id" class="form-label">จังหวัด</label>
                <select class="form-select" id="province_id" name="province_id" required>
                    <option value="<?php echo $province_id; ?>"><?php echo htmlspecialchars($province_name, ENT_QUOTES, 'UTF-8'); ?></option>
                    <!-- Option สำหรับจังหวัดอื่น ๆ -->
                    <?php
                    $province_query = "SELECT PROVINCE_ID, PROVINCE_NAME FROM province";
                    $province_result = mysqli_query($conn, $province_query);
                    while ($row = mysqli_fetch_assoc($province_result)) {
                        echo '<option value="' . $row['PROVINCE_ID'] . '">' . htmlspecialchars($row['PROVINCE_NAME'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="amphur_id" class="form-label">อำเภอ</label>
                <select class="form-select" id="amphur_id" name="amphur_id" required>
                    <option value="<?php echo $amphur_id; ?>"><?php echo htmlspecialchars($amphur_name, ENT_QUOTES, 'UTF-8'); ?></option>
                    <!-- Option สำหรับอำเภออื่น ๆ -->
                    <?php
                    $amphur_query = "SELECT AMPHUR_ID, AMPHUR_NAME FROM amphur WHERE PROVINCE_ID = ?";
                    $stmt = mysqli_prepare($conn, $amphur_query);
                    mysqli_stmt_bind_param($stmt, 'i', $province_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $amphur_id, $amphur_name);
                    while (mysqli_stmt_fetch($stmt)) {
                        echo '<option value="' . $amphur_id . '">' . htmlspecialchars($amphur_name, ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                    mysqli_stmt_close($stmt);
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="district_id" class="form-label">เขต/ตำบล</label>
                <select class="form-select" id="district_id" name="district_id" required>
                    <option value="<?php echo $district_id; ?>"><?php echo htmlspecialchars($district_name, ENT_QUOTES, 'UTF-8'); ?></option>
                    <!-- Option สำหรับเขต/ตำบลอื่น ๆ -->
                    <?php
                    $district_query = "SELECT DISTRICT_ID, DISTRICT_NAME FROM district WHERE AMPHUR_ID = ?";
                    $stmt = mysqli_prepare($conn, $district_query);
                    mysqli_stmt_bind_param($stmt, 'i', $amphur_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $district_id, $district_name);
                    while (mysqli_stmt_fetch($stmt)) {
                        echo '<option value="' . $district_id . '">' . htmlspecialchars($district_name, ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                    mysqli_stmt_close($stmt);
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="postcode" class="form-label">รหัสไปรษณีย์</label>
                <input type="text" class="form-control" id="postcode" name="postcode" value="<?php echo htmlspecialchars($postcode, ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="correct_customer.php" class="btn btn-secondary">ย้อนกลับ</a>
        </form>
    </div>
</body>
</html>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#province_id').change(function() {
            var id_province = $(this).val();
            $.ajax({
                type: "POST",
                url: "../select_Amphur.php",
                data: { id: id_province },
                success: function(data) {
                    $('#amphur_id').html(data);
                    $('#district_id').html('<option value="">เลือกตำบล</option>');
                    $('#postcode').val('');
                },
                error: function() {
                    $('#amphur_id').html('<option value="">ไม่สามารถดึงข้อมูลอำเภอได้</option>');
                }
            });
        });

        $('#amphur_id').change(function() {
            var id_amphur = $(this).val();
            $.ajax({
                type: "POST",
                url: "../select_Tambol.php",
                data: { id: id_amphur },
                success: function(data) {
                    $('#district_id').html(data);
                    $('#postcode').val('');
                },
                error: function() {
                    $('#district_id').html('<option value="">ไม่สามารถดึงข้อมูลตำบลได้</option>');
                }
            });
        });

        $('#district_id').change(function() {
            var id_district = $(this).val();
            $.ajax({
                type: "POST",
                url: "../get_zip_code.php",
                data: { id: id_district },
                success: function(data) {
                    $('#postcode').val(data); // Fix postcode ID
                },
                error: function() {
                    $('#postcode').val(''); // Fix postcode ID
                }
            });
        });
    });
</script>

<?php
mysqli_close($conn);
?>
