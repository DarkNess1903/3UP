<?php
session_start();
include 'connectDB.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // ตรวจสอบ username และ password ของ admin
    $login_query = "SELECT admin_id FROM admin WHERE username = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $login_query);
    mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $admin_id);
    
    if (mysqli_stmt_fetch($stmt)) {
        // ตั้งค่า session เมื่อผู้ดูแลระบบล็อกอินสำเร็จ
        $_SESSION['admin_id'] = $admin_id;
        
        header("Location: index.php");
        exit();
    } else {
        echo "Username or Password incorrect";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin Login</h1>
    </header>
    <main>
        <section>
            <form method="post" action="">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Login</button>
                <?php if (isset($error)): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </form>
        </section>
    </main>
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p>&copy; <?php echo date("Y"); ?> เนื้อหอมมาลองเซ่. สงวนลิขสิทธิ์.</p>
    </footer>
</body>
</html>
<style>
    /* styles.css */

/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Header Styles */
header {
    background-color: #18181a;
    color: white;
    padding: 20px 0;
    text-align: center;
}

header h1 {
    margin: 0;
    font-size: 24px;
}

/* Main Section Styles */
main {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

section {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
}

label {
    margin-top: 10px;
    font-weight: bold;
}

input[type="text"],
input[type="password"] {
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 16px;
}

button {
    margin-top: 20px;
    padding: 10px;
    background-color: #18181a;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #555;
}

/* Error Message Styles */
p {
    margin-top: 15px;
    color: red;
    font-weight: bold;
    text-align: center;
}

/* Footer Styles */
footer {
    background-color: #18181a;
    color: white;
    text-align: center;
    padding: 10px 0;
}
</style>