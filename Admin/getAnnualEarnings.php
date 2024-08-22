<?php
include '../connectDB.php';

// ดึงข้อมูลรายได้รายปีจากฐานข้อมูล
$query = "SELECT DATE_FORMAT(order_date, '%Y') AS year, SUM(total_amount) AS earnings FROM orders GROUP BY DATE_FORMAT(order_date, '%Y')";
$result = $conn->query($query);

$annualEarnings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $annualEarnings[] = $row;
    }
} else {
    echo "ไม่พบข้อมูลรายได้รายปี";
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!-- HTML สำหรับแสดงข้อมูลรายได้รายปี -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Earnings (Annual)</h6>
    </div>
    <div class="card-body">
        <?php if (!empty($annualEarnings)) : ?>
            <ul>
                <?php foreach ($annualEarnings as $earning) : ?>
                    <li><?php echo $earning['year'] . ': $' . number_format($earning['earnings'], 2); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>No earnings data available.</p>
        <?php endif; ?>
    </div>
</div>
