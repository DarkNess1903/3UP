<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลลูกค้าจากฐานข้อมูล
$profile_query = "SELECT name, email, phone, address FROM customer WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $profile_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$profile_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($profile_result) === 0) {
    die("Profile not found.");
}

$profile = mysqli_fetch_assoc($profile_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Profile</h1>
    </header>

    <main>
        <section class="profile-info">
            <div class="profile-icon">
                <i class="fas fa-user"></i>
            </div>
            <h2>Personal Information</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($profile['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($profile['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($profile['address']); ?></p>
            <button id="editBtn" class="btn btn-primary">Edit Profile</button>
        </section>
    </main>

    <!-- Modal สำหรับฟอร์มแก้ไขข้อมูล -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Profile</h2>
            <form id="editProfileForm" action="update_profile.php" method="POST">
                <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">
                <div class="mb-3">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone']); ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" rows="4" class="form-control" required><?php echo htmlspecialchars($profile['address']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("editModal");
        var btn = document.getElementById("editBtn");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>

<?php
include 'footer.php';
// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
