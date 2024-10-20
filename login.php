<?php
session_start();
include 'connectDB.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_or_username = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // ตรวจสอบในตาราง customer ก่อน
    $query_customer = "SELECT * FROM customer WHERE phone = '$phone_or_username'";
    $result_customer = mysqli_query($conn, $query_customer);
    $user_customer = mysqli_fetch_assoc($result_customer);

    // ตรวจสอบในตาราง admin
    $query_admin = "SELECT * FROM admin WHERE username = '$phone_or_username'";
    $result_admin = mysqli_query($conn, $query_admin);
    $user_admin = mysqli_fetch_assoc($result_admin);

    if ($user_customer && password_verify($password, $user_customer['password'])) {
        // ล็อกอินสำเร็จสำหรับลูกค้า
        $_SESSION['customer_id'] = $user_customer['customer_id'];
        echo "<script>window.location.href='index.php';</script>";
        exit();
    } elseif ($user_admin && $password === $user_admin['password']) {
        // ล็อกอินสำเร็จสำหรับแอดมิน
        $_SESSION['admin_id'] = $user_admin['admin_id'];
        echo "<script>window.location.href='Admin/index.php';</script>";
        exit();
    } else {
        // กรณีที่ข้อมูลไม่ถูกต้อง
        $error = "เบอร์มือถือหรือรหัสผ่านไม่ถูกต้องกรุณาลองอีกครั้ง";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>เข้าสู่ระบบ - Meat Store</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <style>
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="text-center py-4 bg-dark text-white">
        <h1>เข้าสู่ระบบ</h1>
    </header>

    <main class="container mt-5">
        <section class="login row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <h2 class="text-center mb-4">เข้าสู่ระบบ</h2>
                <form action="login.php" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="phone" class="form-label">เบอร์มือถือ:</label>
                        <input type="text" id="phone" name="phone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">รหัสผ่าน:</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" class="form-control" required>
                            <i class="fas fa-eye toggle-password" id="toggle-password" onclick="togglePasswordVisibility('password')"></i>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <p class="text-danger text-center"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <input type="submit" class="btn btn-primary" value="เข้าสู่ระบบ">
                    </div>
                </form>
                <p class="text-center mt-3">ยังไม่มีสมาชิก? <a href="register.php">สมัครสมาชิก</a></p>
            </div>
        </section>
    </main>

    <script>
        function togglePasswordVisibility(passwordId) {
            const passwordField = document.getElementById(passwordId);
            const toggleIcon = document.getElementById('toggle-password');
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>


<?php
include 'footer.php';
?>
