<?php
include 'topnavbar.php';
include 'connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>แดชบอร์ดผู้ดูแลระบบ - กราฟสรุป</title>
    <style>
        .chart-pie {
            position: relative;
            height: 400px;
            width: 100%;
        }
        .chart-pie canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    <main class="container mt-4">
        <div class="row">
            <!-- Line Chart for Weekly Earnings -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">ภาพรวมรายได้</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">เลือกช่วงเวลา:</div>
                                <a class="dropdown-item" href="#" data-period="weekly">รายสัปดาห์</a>
                                <a class="dropdown-item" href="#" data-period="monthly">รายเดือน</a>
                                <a class="dropdown-item" href="#" data-period="yearly">รายปี</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="myLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // ฟังก์ชันเริ่มต้นเมื่อโหลดหน้า
                document.addEventListener('DOMContentLoaded', () => {
                    // เรียกข้อมูลรายสัปดาห์เป็นค่าเริ่มต้น
                    fetchEarnings('weekly');

                    // ตั้งค่า Event Listener สำหรับตัวเลือกใน Dropdown
                    document.querySelectorAll('.dropdown-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const period = this.getAttribute('data-period');
                            fetchEarnings(period);
                        });
                    });
                });

                // กำหนดตัวแปรสำหรับกราฟ
                let myLineChart;

                function fetchEarnings(period) {
                    fetch(`get_data.php?period=${period}`)
                        .then(response => response.json())
                        .then(data => {
                            updateChart(data.labels, data.data);
                        })
                        .catch(error => console.error('Error fetching data:', error));
                }

                function updateChart(labels, data) {
                    if (myLineChart) {
                        myLineChart.destroy();
                    }

                    const ctx = document.getElementById('myLineChart').getContext('2d');
                    myLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'รายได้',
                                data: data,
                                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                borderColor: 'rgba(78, 115, 223, 1)',
                                borderWidth: 2,
                                fill: true,
                            }],
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                },
                            },
                        },
                    });
                }

                document.addEventListener('DOMContentLoaded', () => {
                    fetchEarnings('monthly');

                    document.querySelectorAll('.dropdown-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const period = this.getAttribute('data-period');
                            fetchEarnings(period);
                        });
                    });
                });
            </script>

            <!-- Pie Chart for Order Status -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">สถานะคำสั่งซื้อ</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="myPieChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <span class="mr-2">
                                <i class="fas fa-circle text-primary"></i> รอดำเนินการ
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-success"></i> กำลังดำเนินการ
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-info"></i> กำลังจัดส่ง
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-secondary"></i> เสร็จสิ้น
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart for Sales by Product -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">ยอดขายตามสินค้า</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart for Inventory -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">ระดับสินค้าคงคลัง</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="inventoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

            <!-- JavaScript to fetch and display data -->
            <script>
               // Fetch order status data and render pie chart
               fetch('get_order_status_data.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var ctxPie = document.getElementById('myPieChart').getContext('2d');
                        new Chart(ctxPie, {
                            type: 'pie',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    data: data.data,
                                    backgroundColor: [
                                        'rgba(78, 115, 223, 1)',  // รอดำเนินการ
                                        'rgba(28, 200, 138, 1)',  // กำลังดำเนินการ
                                        'rgba(54, 185, 204, 1)',   // กำลังจัดส่ง
                                        'rgba(231, 74, 59, 1)'     // เสร็จสิ้น
                                    ],
                                    hoverBackgroundColor: [
                                        'rgba(78, 115, 223, 0.8)',
                                        'rgba(28, 200, 138, 0.8)',
                                        'rgba(54, 185, 204, 0.8)',
                                        'rgba(231, 74, 59, 0.8)'
                                    ],
                                    borderWidth: 1,
                                    borderColor: 'rgba(255, 255, 255, 1)',
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                return tooltipItem.label + ': ' + tooltipItem.raw;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error));

        // Bar Chart for Sales by Product
        $.getJSON('get_sales_data.php', function(data) {
            var labels = [];
            var dataSet = [];

            data.forEach(function(item) {
                labels.push(item.product_name);
                dataSet.push(item.total_sold);
            });

            var ctxBar = document.getElementById('salesChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'ยอดขายรวม',
                        data: dataSet,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

        // Bar Chart for Inventory
        $.getJSON('get_inventory_data.php', function(data) {
            var labels = [];
            var dataSet = [];

            data.forEach(function(item) {
                labels.push(item.product_name);
                dataSet.push(item.stock_quantity);
            });

            var ctxInventory = document.getElementById('inventoryChart').getContext('2d');
            new Chart(ctxInventory, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'จำนวนสินค้าคงคลัง',
                        data: dataSet,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
