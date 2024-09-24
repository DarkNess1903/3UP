<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลลูกค้าจากฐานข้อมูล
$profile_query = "SELECT name, email, phone, address FROM customer WHERE customer_id = ?";
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
    <title>Profile</title>
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
    <header>
        <h1>โปรไฟล์</h1>
    </header>

    <main>
        <div class="container mt-4">
            <?php
            // แสดงผลการอัปเดตโปรไฟล์
            if (isset($_GET['update'])) {
                if ($_GET['update'] == 'success') {
                    echo '<div class="alert alert-success">อัปเดตโปรไฟล์สำเร็จ!</div>';
                }
            }

            // แสดงข้อผิดพลาดในการอัปเดตโปรไฟล์
            if (isset($_GET['update_error'])) {
                switch ($_GET['update_error']) {
                    case '1':
                        echo '<div class="alert alert-danger">กรุณากรอกข้อมูลให้ครบทุกช่อง.</div>';
                        break;
                    case '2':
                        echo '<div class="alert alert-danger">รูปแบบอีเมลไม่ถูกต้อง.</div>';
                        break;
                    case '3':
                        echo '<div class="alert alert-danger">ไม่สามารถเตรียมคำสั่ง SQL ได้.</div>';
                        break;
                    case '4':
                        echo '<div class="alert alert-danger">เกิดข้อผิดพลาดในการอัปเดตโปรไฟล์.</div>';
                        break;
                    default:
                        echo '<div class="alert alert-danger">เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ.</div>';
                        break;
                }
            }
            ?>

            <section class="profile-info text-center">
                <div class="profile-icon mb-3">
                    <i class="fas fa-user fa-1x"></i>
                </div>
                <h2>ข้อมูลส่วนตัว</h2>
                <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($profile['name']); ?></p>
                <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
                <p><strong>เบอร์โทรศัพท์:</strong> <?php echo htmlspecialchars($profile['phone']); ?></p>
                <p><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($profile['address']); ?></p>
                <button id="editBtn" class="btn btn-primary">แก้ไขโปรไฟล์</button>
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
                        <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อ:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">เบอร์โทรศัพท์:</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">ที่อยู่:</label>
                            <textarea id="address" name="address" rows="4" class="form-control" required><?php echo htmlspecialchars($profile['address']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript สำหรับเปิดและปิด Modal -->
    <script>
    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
    var btn = document.getElementById("editBtn");

    btn.onclick = function() {
        editModal.show();
    }

    // ปิด Modal เมื่อคลิกที่ปุ่ม Close
    var closeBtn = document.querySelector(".btn-close");
    closeBtn.onclick = function() {
        editModal.hide();
    }

    // ปิด Modal เมื่อคลิกนอก Modal
    window.onclick = function(event) {
        var modal = document.getElementById("editModal");
        if (event.target == modal) {
            editModal.hide();
        }
    }
    </script>
</body>
</html>

<?php
include 'footer.php';
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
