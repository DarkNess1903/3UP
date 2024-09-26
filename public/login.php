<?php
session_start();
include '../connectDB.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // ตรวจสอบข้อมูลผู้ใช้ในฐานข้อมูล
    $query = "SELECT * FROM customer WHERE phone = '$phone'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        // ข้อมูลผู้ใช้ถูกต้อง
        $_SESSION['customer_id'] = $user['customer_id'];

        // ใส่โค้ดสำหรับการแจ้งเตือนหรือการเปลี่ยนหน้าเมื่อเข้าสู่ระบบสำเร็จ
        echo "<script>window.location.href='index.php';</script>";
        exit();
    } else {
        $error = "เบอร์มือถือหรือรหัสผ่านไม่ถูกต้องกรุณาลองอีกครั้ง";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Meat Store</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
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
    <header>
        <h1>Login</h1>
    </header>

    <main>
        <section class="login">
            <h2>เข้าสู่ระบบ</h2>
            <form action="login.php" method="post">
                <label for="phone">เบอร์มือถือ:</label>
                <input type="text" id="phone" name="phone" required>

                <label for="password">รหัสผ่าน:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <i class="fas fa-eye toggle-password" id="toggle-password" onclick="togglePasswordVisibility('password')"></i>
                </div>

                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?><br>

                <input type="submit" class="btn btn-primary" value="Login">
            </form>
            <p>ยังไม่มีสมาชิก? <a href="register.php">สมัครสมาชิก</a></p>
        </section>
    </main>
</body>
</html>

<script>
function togglePasswordVisibility(inputId) {
    var input = document.getElementById(inputId);
    var toggleIcon = document.getElementById('toggle-password');
    if (input.type === "password") {
        input.type = "text";
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

<?php
include 'footer.php';
?>
