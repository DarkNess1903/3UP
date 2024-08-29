<?php
session_start();
include '../connectDB.php';

// ตรวจสอบการเข้าสู่ระบบของ Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>SB Admin 2 - Dashboard</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/alerts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand {
            flex: 1;
        }
        .nav-time {
            text-align: center;
            flex: 2;
        }
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Admin</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Nav Item - Ordering Information -->
            <li class="nav-item">
                <a class="nav-link" href="manage_orders.php">
                    <i class="fas fa-fw fa-info-circle"></i>
                    <span>Ordering Information</span></a>
            </li>

            <!-- Nav Item - Edit Product -->
            <li class="nav-item">
                <a class="nav-link" href="manage_products.php">
                    <i class="fas fa-fw fa-edit"></i>
                    <span>Edit Product</span></a>
            </li>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Time display in the center -->
                        <div class="mx-auto" id="current-time"></div>

                        <!-- JavaScript -->
                        <script>
                        function updateTime() {
                            const now = new Date();
                            const hours = now.getHours().toString().padStart(2, '0');
                            const minutes = now.getMinutes().toString().padStart(2, '0');
                            const seconds = now.getSeconds().toString().padStart(2, '0');
                            const currentTime = `${hours}:${minutes}:${seconds}`;
                            
                            document.getElementById('current-time').textContent = currentTime;
                        }

                        // Update every second
                        setInterval(updateTime, 1000);

                        // Initial call to display time immediately
                        updateTime();
                        </script>

                        <!-- CSS -->
                        <style>
                        .navbar .mx-auto {
                            position: absolute;
                            left: 50%;
                            transform: translateX(-50%);
                            font-size: 24px; /* เพิ่มขนาดตัวอักษร */
                            font-weight: bold;
                            color: #4e73df; /* สีที่โดดเด่น */
                        }

                        @media (max-width: 768px) {
                            .navbar .mx-auto {
                                font-size: 18px; /* ขนาดตัวอักษรเล็กลงสำหรับอุปกรณ์ขนาดเล็ก */
                            }
                        }
                        </style>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- (Visible Only XS) -->
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1 show">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter" id="alertCount">0</span>
                            </a>

                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <!-- New Order Alert -->
                                <div id="alertContent">
                                    <!-- Alerts will be dynamically inserted here -->
                                </div>
                                <a class="dropdown-item text-center small text-gray-500" href="orderDetails.php">Show All Alerts</a>
                            </div>
                        </li>

                        <!-- Chart.js and jQuery -->
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

                        <script>
                        $(document).ready(function() {
                            // ฟังก์ชันดึงข้อมูลออเดอร์ใหม่
                            function updateAlerts() {
                                $.getJSON('get_new_orders.php', function(data) {
                                    var newOrders = data;

                                    // อัพเดตจำนวนแจ้งเตือน
                                    $('#alertCount').text(newOrders.length);

                                    // สร้างเนื้อหาของการแจ้งเตือน
                                    var alertHtml = '';
                                    if (newOrders.length > 0) {
                                        $.each(newOrders, function(index, order) {
                                            alertHtml += 
                                                '<a class="dropdown-item d-flex align-items-center" href="view_order.php?order_id=' + order.order_id + '">' +
                                                '<div class="mr-3">' +
                                                '<div class="icon-circle bg-info">' +
                                                '<i class="fas fa-shopping-cart text-white"></i>' +
                                                '</div>' +
                                                '</div>' +
                                                '<div>' +
                                                '<div class="small text-gray-500">' + new Date(order.order_date).toLocaleDateString() + '</div>' +
                                                '<span class="font-weight-bold">New order received! Order ID: ' + order.order_id + '</span>' +
                                                '</div>' +
                                                '</a>';
                                        });
                                    } else {
                                        alertHtml = '<a class="dropdown-item text-center small text-gray-500" href="#">No new orders</a>';
                                    }

                                    $('#alertContent').html(alertHtml);
                                });
                            }

                            // เรียกใช้ฟังก์ชันเพื่ออัพเดตแจ้งเตือนเมื่อเอกสารโหลดเสร็จ
                            updateAlerts();

                            // รีเฟรชแจ้งเตือนทุกๆ 30 วินาที
                            setInterval(updateAlerts, 30000);
                        });
                        </script>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Admin</span>
                                <img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="logout.php" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Content Row -->
                    <div class="row">

                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Earnings (Monthly)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthlyEarnings">฿0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Include jQuery library -->
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        $(document).ready(function() {
                            $.ajax({
                                url: 'getMonthlyEarnings.php',
                                method: 'GET',
                                success: function(data) {
                                    $('#monthlyEarnings').text('฿' + data);
                                },
                                error: function() {
                                    $('#monthlyEarnings').text('Error retrieving data');
                                }
                            });
                        });
                    </script>

                    <!-- Earnings (Annual) Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Earnings (Annual)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="annualEarnings">฿0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-year fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Include jQuery library -->
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        $(document).ready(function() {
                            $.ajax({
                                url: 'getAnnualEarnings.php',
                                method: 'GET',
                                dataType: 'json',
                                success: function(data) {
                                    let totalEarnings = 0;
                                    if (Array.isArray(data)) {
                                        data.forEach(function(item) {
                                            totalEarnings += parseFloat(item.earnings);
                                        });
                                    } else if (data.error) {
                                        $('#annualEarnings').text('Error: ' + data.error);
                                        return;
                                    }
                                    $('#annualEarnings').text('฿' + totalEarnings.toFixed(2));
                                },
                                error: function() {
                                    $('#annualEarnings').text('Error retrieving data');
                                }
                            });
                        });
                    </script>

                    <!-- Pending Requests Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Requests
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingRequests">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Orders Card Example -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Completed Orders
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="completedOrdersCount">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Function to fetch data and update UI
                        function fetchData(url, elementId, processData) {
                            fetch(url)
                                .then(response => response.json())
                                .then(data => {
                                    if (data) {
                                        document.getElementById(elementId).innerText = processData(data);
                                    }
                                })
                                .catch(error => console.error('Error fetching data:', error));
                        }

                        // Fetch and display pending requests
                        fetchData('getPendingRequests.php', 'pendingRequests', data => {
                            return data.pending_count;
                        });

                        // Fetch and display completed orders
                        fetchData('getCompletedOrders.php', 'completedOrdersCount', data => {
                            return data.completed_orders;
                        });
                    </script>

                    <!-- Line Chart -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <!-- Card Header - Dropdown -->
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Earnings Overview</h6>
                                <div class="dropdown no-arrow">
                                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                        <div class="dropdown-header">Dropdown Header:</div>
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Something else here</a>
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

                    <!-- Chart.js Script -->
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        // เมื่อเอกสารโหลดเสร็จ
                        document.addEventListener('DOMContentLoaded', function() {
                            fetch('get_data.php')
                                .then(response => response.json())
                                .then(data => {
                                    var ctx = document.getElementById('myLineChart').getContext('2d');
                                    var myLineChart = new Chart(ctx, {
                                        type: 'line', // กราฟเส้น
                                        data: {
                                            labels: data.labels, // ข้อมูลแกน Y (ชื่อเดือน)
                                            datasets: [{
                                                label: 'Monthly Earnings', // ชื่อของกราฟ
                                                data: data.data, // ข้อมูลที่แสดงในกราฟ
                                                borderColor: 'rgba(78, 115, 223, 1)', // สีของเส้น
                                                backgroundColor: 'rgba(78, 115, 223, 0.2)', // สีพื้นหลังของกราฟ
                                                borderWidth: 2, // ขนาดของเส้น
                                                fill: false // ไม่ให้กราฟเติมสี
                                            }]
                                        },
                                        options: {
                                            scales: {
                                                x: {
                                                    title: {
                                                        display: true,
                                                        text: 'Month' // แท็กแกน X
                                                    }
                                                },
                                                y: {
                                                    title: {
                                                        display: true,
                                                        text: 'Earnings (Amount)' // แท็กแกน Y
                                                    }
                                                }
                                            }
                                        }
                                    });
                                })
                                .catch(error => console.error('Error fetching data:', error));
                        });
                    </script>

                    <!-- Pie Chart -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <!-- Card Header - Dropdown -->
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Order Status Distribution</h6>
                                <div class="dropdown no-arrow">
                                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                        <div class="dropdown-header">Dropdown Header:</div>
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                    </div>
                                </div>
                            </div>
                            <!-- Card Body -->
                            <div class="card-body">
                                <div class="chart-pie pt-4 pb-2">
                                    <canvas id="myPieChart"></canvas>
                                </div>
                                <div class="mt-4 text-center small">
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-primary"></i> Awaiting
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-success"></i> In Progress
                                    </span>
                                    <span class="mr-2">
                                        <i class="fas fa-circle text-info"></i> Completed
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart.js Script -->
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        // เมื่อเอกสารโหลดเสร็จ
                        document.addEventListener('DOMContentLoaded', function() {
                            fetch('get_order_status_data.php')
                                .then(response => response.json())
                                .then(data => {
                                    var ctx = document.getElementById('myPieChart').getContext('2d');
                                    var myPieChart = new Chart(ctx, {
                                        type: 'pie', // กราฟวงกลม
                                        data: {
                                            labels: data.labels, // ข้อมูลสถานะ
                                            datasets: [{
                                                data: data.data, // จำนวนคำสั่งซื้อในแต่ละสถานะ
                                                backgroundColor: ['rgba(78, 115, 223, 1)', 'rgba(28, 200, 138, 1)', 'rgba(54, 185, 204, 1)'], // สีของวงกลม
                                                hoverBackgroundColor: ['rgba(78, 115, 223, 0.8)', 'rgba(28, 200, 138, 0.8)', 'rgba(54, 185, 204, 0.8)'] // สีของวงกลมเมื่อเอาเมาส์ชี้
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
                                .catch(error => console.error('Error fetching data:', error));
                        });
                    </script>    
                <!-- /.container-fluid -->

                <!-- Sales Chart -->
                <div class="col-xl-12 col-lg-12">
                    <div class="card shadow mb-4">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Sales by Product</h6>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart.js -->
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                <script>
                $(document).ready(function() {
                    // ฟังก์ชันดึงข้อมูลยอดขาย
                    function fetchSalesData() {
                        $.getJSON('get_sales_data.php', function(data) {
                            var labels = [];
                            var dataSet = [];

                            data.forEach(function(item) {
                                labels.push(item.product_name);
                                dataSet.push(item.total_sold);
                            });

                            var ctx = document.getElementById('salesChart').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar', // เปลี่ยนเป็น 'line' สำหรับกราฟเส้น
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Total Sold',
                                        data: dataSet,
                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    scales: {
                                        x: {
                                            beginAtZero: true
                                        },
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        });
                    }

                    // เรียกใช้ฟังก์ชันเพื่อดึงข้อมูลยอดขายเมื่อเอกสารโหลดเสร็จ
                    fetchSalesData();
                });
                </script>
            </div>
            <!-- End of Main Content -->
        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

</body>

</html>