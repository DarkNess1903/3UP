<?php
include '../connectDB.php';

// ดึงข้อมูลรายได้รายเดือนจากฐานข้อมูล
$query = "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month, SUM(total_amount) AS earnings FROM orders GROUP BY DATE_FORMAT(order_date, '%Y-%m')";
$result = $conn->query($query);

$monthlyEarnings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $monthlyEarnings[] = $row;
    }
} else {
    echo "ไม่พบข้อมูลรายได้รายเดือน";
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!-- HTML สำหรับแสดงข้อมูลรายได้รายเดือน -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Earnings (Monthly)</h6>
    </div>
    <div class="card-body">
        <?php if (!empty($monthlyEarnings)) : ?>
            <ul>
                <?php foreach ($monthlyEarnings as $earning) : ?>
                    <li><?php echo $earning['month'] . ': $' . number_format($earning['earnings'], 2); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>No earnings data available.</p>
        <?php endif; ?>
    </div>
</div>
