<?php
session_start();
include 'topnavbar.php';
include '../connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>แดชบอร์ดผู้ดูแลระบบ - กราฟสรุป</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        .chart-pie {
    position: relative;
    height: 400px; /* ปรับขนาดตามความต้องการ */
    width: 100%; /* ให้เต็มความกว้างของบล็อก */
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
            <!-- Line Chart for Monthly Earnings -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <!-- Card Header -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">ภาพรวมรายได้</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">เลือกช่วงเวลา:</div>
                                <a class="dropdown-item" href="#" data-period="daily">รายวัน</a>
                                <a class="dropdown-item" href="#" data-period="monthly">รายเดือน</a>
                                <a class="dropdown-item" href="#" data-period="yearly">รายปี</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="myLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

           <!-- Pie Chart for Order Status -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">การกระจายสถานะคำสั่งซื้อ</h6>
                    </div>
                    <!-- Card Body -->
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
                                <i class="fas fa-circle text-info"></i> เสร็จสิ้น
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart for Sales by Product -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <!-- Card Header -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">ยอดขายตามสินค้า</h6>
                    </div>
                    <!-- Card Body -->
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
                    <!-- Card Header -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">ระดับสินค้าคงคลัง</h6>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="inventoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Chart.js Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Line Chart for Earnings Overview
        const ctxLine = document.getElementById('myLineChart').getContext('2d');
        let myLineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: [], // Placeholder for labels
                datasets: [{
                    label: 'รายได้',
                    data: [], // Placeholder for data
                    borderColor: 'rgba(78, 115, 223, 1)',
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'ช่วงเวลา'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'รายได้'
                        }
                    }
                }
            }
        });

        function updateChart(period) {
            fetch(`get_data.php?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    myLineChart.data.labels = data.labels;
                    myLineChart.data.datasets[0].data = data.data;
                    myLineChart.update();
                })
                .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error));
        }

        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function() {
                const period = this.getAttribute('data-period');
                updateChart(period);
            });
        });

        // Load default data
        updateChart('monthly');

        // Pie Chart for Order Status
        fetch('get_order_status_data.php')
            .then(response => response.json())
            .then(data => {
                var ctxPie = document.getElementById('myPieChart').getContext('2d');
                new Chart(ctxPie, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.data,
                            backgroundColor: ['rgba(78, 115, 223, 1)', 'rgba(28, 200, 138, 1)', 'rgba(54, 185, 204, 1)'],
                            hoverBackgroundColor: ['rgba(78, 115, 223, 0.8)', 'rgba(28, 200, 138, 0.8)', 'rgba(54, 185, 204, 0.8)']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // เพิ่มการตั้งค่านี้เพื่อให้ขนาดของกราฟปรับตัวตามขนาดของ parent container
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
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'สินค้า'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'ยอดขาย'
                            }
                        }
                    }
                }
            });
        });

        // Bar Chart for Inventory Levels
        $.getJSON('get_inventory_data.php', function(data) {
            var labels = [];
            var dataSet = [];

            data.forEach(function(item) {
                labels.push(item.product_name);
                dataSet.push(item.stock_quantity);
            });

            var ctxBar = document.getElementById('inventoryChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'ระดับสินค้าคงคลัง',
                        data: dataSet,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'สินค้า'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'จำนวน'
                            }
                        }
                    }
                }
            });
        });
    });
    </script>
</body>
</html>
