<?php
session_start();
include '../connectDB.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query for provinces
$result3 = mysqli_query($conn, "SELECT provinceID, provinceName FROM province");
if (!$result3) {
    die("Error fetching provinces: " . mysqli_error($conn));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $province_id = mysqli_real_escape_string($conn, $_POST['province_id']);
    $amphur_id = mysqli_real_escape_string($conn, $_POST['amphur_id']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "รหัสผ่านไม่ตรงกัน กรุณาลองอีกครั้ง";
    } else {
        // Check for duplicate phone number
        $check_query = "SELECT * FROM customer WHERE phone = '$phone'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "หมายเลขโทรศัพท์นี้มีอยู่ในระบบแล้ว กรุณาใช้หมายเลขอื่น";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Encrypt the password

            // Insert new customer
            $query = "INSERT INTO customer (name, phone, address, province_id, amphur_id, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssssss', $name, $phone, $address, $province_id, $amphur_id, $hashed_password);
            
            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php");
                exit();
            } else {
                echo "Error: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Meat Store</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
        }
        .position-relative {
            position: relative;
        }
    </style>
</head>
<body>
    <header>
        <h1>สมัครสมาชิก</h1>
    </header>

    <main>
        <section class="register">
            <h2>สร้างบัญชีของคุณ</h2>
            <form action="register.php" method="post">
                <label for="name">ชื่อ:</label>
                <input type="text" id="name" name="name" required>

                <label for="phone">เบอร์โทรศัพท์:</label>
                <input type="text" id="phone" name="phone" required>

                <label for="address">ที่อยู่:</label>
                <input type="text" id="address" name="address" required>

                <label for="province_id">จังหวัด:</label>
                <select class="form-select" name="province_id" id="province_id" required>
                    <option value="">เลือกจังหวัด</option>
                    <?php 
                        while ($row3 = mysqli_fetch_assoc($result3)) {
                            echo "<option value=\"{$row3['provinceID']}\">{$row3['provinceName']}</option>";
                        }
                    ?>
                </select>

                <label for="amphur_id">อำเภอ:</label>
                <select class="form-select" name="amphur_id" id="amphur_id" required>
                    <option value="">เลือกอำเภอ</option>
                </select>

                <label for="password">รหัสผ่าน:</label>
                <div class="position-relative">
                    <input type="password" id="password" name="password" required>
                    <i class="fas fa-eye toggle-password" id="toggle-password" onclick="togglePasswordVisibility('password')"></i>
                </div>

                <label for="confirm_password">ยืนยันรหัสผ่าน:</label>
                <div class="position-relative">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <i class="fas fa-eye toggle-password" id="toggle-confirm-password" onclick="togglePasswordVisibility('confirm_password')"></i>
                </div>

                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?><br>

                <input type="submit" value="สมัครสมาชิก" class="btn btn-primary">
            </form>
            <p> <a href="login.php">เข้าสู่ระบบ</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 ร้านขายเนื้อ. สงวนลิขสิทธิ์.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function togglePasswordVisibility(inputId) {
            var input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }

        $(document).ready(function() {
            $('#province_id').change(function() {
                var id_province = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "select_Amphur.php", // Ensure this file exists and returns amphurs based on province
                    data: {id: id_province},
                    success: function(data) {
                        $('#amphur_id').html(data);
                    },
                    error: function() {
                        $('#amphur_id').html('<option value="">ไม่สามารถดึงข้อมูลอำเภอได้</option>');
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>
