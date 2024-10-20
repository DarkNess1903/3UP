<?php
include 'connectDB.php'; // เชื่อมต่อฐานข้อมูล
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS Links -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- JavaScript Links -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark-custom">
        <div class="container-fluid">
            <a href="index.php" class="navbar-brand d-flex align-items-center">
                <img src="images/logo.jpg" alt="Logo" width="100" height="100" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php">สินค้า</a></li>
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="order_history.php">ประวัติการสั่งซื้อ</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="profile.php">ตั้งค่าบัญชี</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link text-white" href="contact_us.php">ติดต่อเรา</a></li>
                </ul>
                <?php
                if (isset($_SESSION['customer_id'])) {
                    $customer_id = $_SESSION['customer_id'];
                    $query = "SELECT name FROM customer WHERE customer_id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($row = mysqli_fetch_assoc($result)) {
                        echo "<span class='navbar-text me-3 user-name text-white'>" . htmlspecialchars($row['name']) . "</span>";
                    }
                    echo "<button class='btn btn-outline-danger ms-2' data-bs-toggle='modal' data-bs-target='#logoutModal'>ออกจากระบบ</button>";
                } else {
                    echo "<a class='btn btn-outline-primary me-2' href='login.php'>เข้าสู่ระบบ</a>";
                    echo "<a class='btn btn-outline-success' href='register.php'>สมัครสมาชิก</a>";
                }
                ?>
            </div>
        </div>
    </nav>
</header>

<!-- Modal สำหรับยืนยันการออกจากระบบ -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">ยืนยันการออกจากระบบ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        คุณแน่ใจหรือไม่ว่าต้องการออกจากระบบ?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <a href="logout.php" class="btn btn-danger">ออกจากระบบ</a>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.navbar-nav .nav-link').forEach(item => {
    item.addEventListener('click', () => {
        const navbarCollapse = new bootstrap.Collapse(document.getElementById('navbarNav'));
        navbarCollapse.hide();
    });
});
</script>

</body>
</html>
