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
        echo "<script>alert('Login successful!'); window.location.href='index.php';</script>";
        exit();
    } else {
        $error = "Invalid phone number or password.";
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
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

                <label for="password">รหัสเข้าสู่ระบบ:</label>
                <input type="password" id="password" name="password" required>

                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <input type="submit" value="Login">
            </form>
            <p>ยังไม่มีสมาชิกใช่มั้ย? <a href="register.php">สมัครสมาชิก</a></p>
        </section>
    </main>
</body>
</html>

<?php
include 'footer.php';
?>


