<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

$customer_id = intval($_GET['customer_id'] ?? 0);

if ($customer_id <= 0) {
    die("Invalid customer ID.");
}

// ดึงข้อมูลลูกค้า
$customer_query = "
    SELECT name, email, phone, address 
    FROM customer 
    WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $customer_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$customer_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($customer_result) === 0) {
    die("Customer not found.");
}

$customer = mysqli_fetch_assoc($customer_result);

// อัปเดตข้อมูลลูกค้าเมื่อฟอร์มถูกส่ง
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($name) || empty($email) || empty($phone)) {
        $error = "กรุณากรอกข้อมูลในทุกช่องที่จำเป็น.";
    } else {
        // อัปเดตข้อมูลลูกค้าในฐานข้อมูล
        $update_query = "
            UPDATE customer 
            SET name = ?, email = ?, phone = ?, address = ? 
            WHERE customer_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $phone, $address, $customer_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "อัปเดตข้อมูลลูกค้าสำเร็จ.";
            // ดึงข้อมูลใหม่
            $customer = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address
            ];
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล.";
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
</head>
<body>
    <header>
        <h1>Edit Customer</h1>
    </header>
    <main>
        <?php if (isset($error)): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (isset($success)): ?>
            <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>"><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>"><br>

            <label for="phone">Phone:</label><br>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>"><br>

            <label for="address">Address:</label><br>
            <textarea id="address" name="address"><?php echo htmlspecialchars($customer['address']); ?></textarea><br>

            <input type="submit" value="Update Customer">
        </form>
    </main>
</body>
</html>
