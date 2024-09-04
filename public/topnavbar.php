<?php
include '../connectDB.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Top Navbar</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-md bg-dark-custom">
        <div class="container">
            <a href="index.php" class="navbar-brand d-flex align-items-center">
                <img src="../public/images/logo.jpg" alt="Logo" width="100" height="100" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php">สินค้า</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="cart.php">ตะกร้าสินค้า</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="order_history.php">ประวัติการสั่งซื้อ</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="profile.php">ตั้งค่าบัญชี</a></li>
                </ul>
                <div class="auth-links d-flex ms-auto align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger" id="notificationCount">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="notificationDropdown">
                            <h6 class="dropdown-header">Alerts Center</h6>
                            <ul class="list-unstyled" id="notificationList">
                                <!-- Notifications will be dynamically inserted here -->
                            </ul>
                            <a class="dropdown-item text-center small text-gray-500" href="orderDetails.php">Show All Alerts</a>
                        </div>
                    </div>
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
                        echo "<a class='btn btn-outline-danger ms-2' href='logout.php'>Logout</a>";
                    } else {
                        echo "<a class='btn btn-outline-primary me-2' href='login.php'>Login</a>";
                        echo "<a class='btn btn-outline-success' href='register.php'>Register</a>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Bootstrap Bundle with Popper (for Bootstrap 5) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function fetchNotifications() {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const notificationList = document.getElementById('notificationList');
                    const notificationCount = document.getElementById('notificationCount');

                    // ลบการแจ้งเตือนทั้งหมดก่อน
                    notificationList.innerHTML = '';

                    // อัปเดตจำนวนการแจ้งเตือนที่ยังไม่ได้อ่าน
                    notificationCount.textContent = data.unread_count;

                    // เพิ่มการแจ้งเตือนใหม่
                    data.notifications.forEach(notification => {
                        const li = document.createElement('li');
                        li.className = 'dropdown-item d-flex align-items-center';
                        li.innerHTML = `
                            <div class="me-3">
                                <div class="bg-primary icon-circle">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                            </div>
                            <div>
                                <div class="small text-gray-500">${new Date(notification.order_date).toLocaleDateString()}</div>
                                <span class="font-weight-bold">${notification.status}</span>
                            </div>
                        `;
                        notificationList.appendChild(li);
                    });
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        // เรียกใช้ฟังก์ชันเพื่อดึงข้อมูลการแจ้งเตือนเมื่อโหลดหน้าเว็บ
        fetchNotifications();

        // รีเฟรชข้อมูลการแจ้งเตือนทุกๆ 60 วินาที
        setInterval(fetchNotifications, 60000);
    });
</script>
