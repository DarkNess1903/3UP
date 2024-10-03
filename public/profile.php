<?php
session_start();
include 'connectDB.php';
include 'topnavbar.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลลูกค้าพร้อมกับชื่อจังหวัดและชื่ออำเภอจากฐานข้อมูล
$profile_query = "
    SELECT c.name, c.phone, c.address, p.provinceName AS province_name, a.amphurName AS amphur_name 
    FROM customer c 
    JOIN province p ON c.province_id = p.provinceID 
    JOIN amphur a ON c.amphur_id = a.amphurID 
    WHERE c.customer_id = ?
";
$stmt = mysqli_prepare($conn, $profile_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$profile_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($profile_result) === 0) {
    die("Profile not found.");
}

$profile = mysqli_fetch_assoc($profile_result);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>โปรไฟล์</title>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS Links -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- JavaScript Links -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="js/script.js"></script>
</head>
<body>
    <header class="bg-dark text-white text-center py-3">
        <h1>โปรไฟล์</h1>
    </header>

    <main class="container mt-4">
        <?php
        // แสดงผลการอัปเดตโปรไฟล์
        if (isset($_GET['update']) && $_GET['update'] == 'success') {
            echo '<div class="alert alert-success">อัปเดตโปรไฟล์สำเร็จ!</div>';
        }

        // แสดงข้อผิดพลาดในการอัปเดตโปรไฟล์
        if (isset($_GET['update_error'])) {
            $error_messages = [
                1 => 'กรุณากรอกข้อมูลให้ครบทุกช่อง.',
                2 => 'รูปแบบอีเมลไม่ถูกต้อง.',
                3 => 'ไม่สามารถเตรียมคำสั่ง SQL ได้.',
                4 => 'เกิดข้อผิดพลาดในการอัปเดตโปรไฟล์.'
            ];
            $error_code = intval($_GET['update_error']);
            echo '<div class="alert alert-danger">' . ($error_messages[$error_code] ?? 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ.') . '</div>';
        }
        ?>

        <section class="profile-info text-center">
            <div class="profile-icon mb-3">
                <i class="fas fa-user fa-3x"></i> <!-- ปรับขนาดไอคอน -->
            </div>
            <h2>ข้อมูลส่วนตัว</h2>
            <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>เบอร์โทรศัพท์:</strong> <?php echo htmlspecialchars($profile['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>ที่อยู่:</strong> <?php echo nl2br(htmlspecialchars($profile['address'], ENT_QUOTES, 'UTF-8')); ?></p>
            <p><strong>จังหวัด:</strong> <?php echo htmlspecialchars($profile['province_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>อำเภอ:</strong> <?php echo htmlspecialchars($profile['amphur_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <button id="editBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal">แก้ไขโปรไฟล์</button>
        </section>
    </div>
    </main>

    <!-- Modal สำหรับฟอร์มแก้ไขข้อมูล -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">แก้ไขโปรไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" action="update_profile.php" method="POST">
                        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อ:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">เบอร์โทรศัพท์:</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">ที่อยู่:</label>
                            <textarea id="address" name="address" rows="4" class="form-control" required><?php echo htmlspecialchars($profile['address'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="province_id" class="form-label">จังหวัด:</label>
                            <select id="province_id" name="province_id" class="form-control" required>
                                <?php
                                $province_query = "SELECT provinceID, provinceName FROM province";
                                $province_result = mysqli_query($conn, $province_query);
                                while ($province = mysqli_fetch_assoc($province_result)) {
                                    $selected = ($province['provinceID'] == $profile['province_id']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($province['provinceID'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($province['provinceName'], ENT_QUOTES, 'UTF-8') . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amphur_id" class="form-label">อำเภอ:</label>
                            <select id="amphur_id" name="amphur_id" class="form-control" required>
                                <?php
                                $amphur_query = "SELECT amphurID, amphurName FROM amphur";
                                $amphur_result = mysqli_query($conn, $amphur_query);
                                while ($amphur = mysqli_fetch_assoc($amphur_result)) {
                                    $selected = ($amphur['amphurID'] == $profile['amphur_id']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($amphur['amphurID'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($amphur['amphurName'], ENT_QUOTES, 'UTF-8') . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- ปิดการเชื่อมต่อฐานข้อมูล -->
    <?php mysqli_close($conn); ?>
</body>
</html>
