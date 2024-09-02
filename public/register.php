<?php
session_start();
include '../connectDB.php';

// ตรวจสอบว่ามีการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ตรวจสอบว่ามีการส่งข้อมูลการสมัครสมาชิกหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // แฮชรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // เพิ่มข้อมูลลงในตาราง customer
    $query = "INSERT INTO customer (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $phone, $address, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        echo "Registration successful.";
        header("Location: login.php"); // เปลี่ยนเส้นทางไปยังหน้าเข้าสู่ระบบ
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Meat Store</title>
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
        <h1>Register</h1>
    </header>

    <main>
        <section class="register">
            <h2>Create Your Account</h2>
            <form action="register.php" method="post">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" required>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <input type="submit" value="Register">
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Meat Store. All rights reserved.</p>
    </footer>
</body>
</html>


<?php
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
