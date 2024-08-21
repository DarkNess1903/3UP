<?php
session_start();
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบของ Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Save settings if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form data here
    $setting_value = mysqli_real_escape_string($conn, $_POST['setting_value']);
    // Update settings in the database
    // Example: $query = "UPDATE settings SET value = '$setting_value' WHERE id = 1";
    // mysqli_query($conn, $query);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Settings</h1>
    </header>
    <main>
        <section>
            <h2>Update Settings</h2>
            <form method="post" action="">
                <label for="setting_value">Setting Value:</label>
                <input type="text" id="setting_value" name="setting_value" required>
                <button type="submit">Save Changes</button>
            </form>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Your Store. All rights reserved.</p>
    </footer>
</body>
</html>
