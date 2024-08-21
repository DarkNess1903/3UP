<?php
session_start();
include 'connectDB.php';

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meat Store</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Login</h1>
    </header>

    <main>
        <section class="login">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <input type="submit" value="Login">
            </form>
            <p>Don't have account? <a href="register.php">register here</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>
