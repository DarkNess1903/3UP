<?php
session_start();
include 'topnavbar.php';
include 'connectDB.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// ดึงข้อมูลลูกค้าจากฐานข้อมูล
$customer_query = "SELECT c.customer_id, c.name, c.phone, c.address, p.provinceName, a.amphurName 
                   FROM customer c
                   JOIN province p ON c.province_id = p.provinceID
                   JOIN amphur a ON c.amphur_id = a.amphurID
                   WHERE c.customer_id = ?";
$stmt = mysqli_prepare($conn, $customer_query);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $customer_id, $name, $phone, $address, $province_name, $amphur_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลลูกค้า</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @media (max-width: 576px) {
            h1 {
                font-size: 24px;
            }

            table th, table td {
                font-size: 12px;
            }

            .btn {
                font-size: 12px;
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">ข้อมูลลูกค้า</h1>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Customer ID</th>
                        <th>ชื่อ</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>ที่อยู่</th>
                        <th>จังหวัด</th>
                        <th>อำเภอ</th>
                        <th>แก้ไข</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($customer_id, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($province_name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($amphur_name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <a href="edit_customer.php" class="btn btn-primary">แก้ไข</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>
