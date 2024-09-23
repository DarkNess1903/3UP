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
                <div class="sidebar-brand-text mx-3">ผู้ดูแลระบบ</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Nav Item - Graph -->
            <li class="nav-item">
                <a class="nav-link" href="graph.php">
                    <i class="fas fa-fw fa-chart-line"></i>
                    <span>กราฟสรุป</span>
                </a>
            </li>

            <!-- Nav Item - Ordering Information -->
            <li class="nav-item">
                <a class="nav-link" href="manage_orders.php">
                    <i class="fas fa-fw fa-box"></i>
                    <span>ข้อมูลการสั่งซื้อ</span>
                </a>
            </li>

            <!-- Nav Item - Edit Product -->
            <li class="nav-item">
                <a class="nav-link" href="manage_products.php">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>แก้ไขสินค้า</span>
                </a>
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
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">ผู้ดูแลระบบ</span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="logout.php" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    ออกจากระบบ
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
                        <!-- Total Sales Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                ยอดขายรวม
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalSales">฿0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Monthly Earnings Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                รายได้รายเดือน
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthlyEarnings">฿0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Annual Earnings Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                รายได้ประจำปี
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="annualEarnings">฿0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-year fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Sales Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                ยอดขายประจำวัน
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="dailySales">฿0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-day fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sold Products Card Example -->
                            <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                สินค้าขายออกไปแล้ว
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalSoldProducts">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-boxes fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                คำขอที่รอดำเนินการ
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingRequests">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Status (In Progress) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                สถานะคำสั่งซื้อ (กำลังดำเนินการ)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="orderInProgress">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Completed Orders Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                คำสั่งซื้อที่เสร็จสิ้น
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="completedOrdersCount">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Daily Sales Chart Example -->
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <!-- Card Header -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-info">ยอดขายประจำวัน (7 วันล่าสุด)</h6>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <canvas id="dailySalesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- End of Page Content -->

                <!-- JavaScript to fetch and display data -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Total Sales
                        fetch('get_total_sales.php')
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('totalSales').textContent = `฿${data.totalSales || '0'}`;
                            })
                            .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูลยอดขายรวม:', error));
                        
                        // Sold Products
                        fetch('get_sold_products_data.php')
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('totalSoldProducts').textContent = data.totalSold || '0';
                            })
                            .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูลสินค้าที่ขายออกไป:', error));
                        
                        // Monthly Earnings
                        fetch('getMonthlyEarnings.php')
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('monthlyEarnings').textContent = `฿${data || '0'}`;
                            })
                            .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูลรายได้รายเดือน:', error));
                        
                        // Annual Earnings
                        fetch('getAnnualEarnings.php')
                            .then(response => response.json())
                            .then(data => {
                                let totalEarnings = 0;
                                if (Array.isArray(data)) {
                                    data.forEach(item => totalEarnings += parseFloat(item.earnings) || 0);
                                } else if (data.error) {
                                    document.getElementById('annualEarnings').textContent = `Error: ${data.error}`;
                                    return;
                                }
                                document.getElementById('annualEarnings').textContent = `฿${totalEarnings.toFixed(2)}`;
                            })
                            .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูลรายได้ประจำปี:', error));
                        
                        // Pending Requests
                        fetch('getPendingRequests.php')
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('pendingRequests').textContent = data.pending_count || '0';
                            })
                            .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูลคำขอที่รอดำเนินการ:', error));
                        
                        // Order In Progress
                        fetch('getOrdersInProgress.php')
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('orderInProgress').textContent = data.inProgress || '0';
                            })
                            .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูลสถานะคำสั่งซื้อที่กำลังดำเนินการ:', error));
                        
                        // Completed Orders
                        fetch('getCompletedOrders.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.completedOrders !== undefined) {
                                    document.getElementById('completedOrdersCount').textContent = data.completedOrders || '0';
                                } else {
                                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูลคำสั่งซื้อที่เสร็จสิ้น:', data.error);
                                    document.getElementById('completedOrdersCount').textContent = '0';
                                }
                            })
                            .catch(error => console.error('เกิดข้อผิดพลาดในการดึงข้อมูลคำสั่งซื้อที่เสร็จสิ้น:', error));

                        // Daily Sales
                        fetch('getDailySales.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.dailySales !== undefined) {
                                    document.getElementById('dailySales').textContent = `฿${data.dailySales.toFixed(2)}`;
                                } else {
                                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูลยอดขายประจำวัน:', data.error);
                                    document.getElementById('dailySales').textContent = '฿0';
                                }
                            })
                            .catch(error => {
                                console.error('เกิดข้อผิดพลาดในการดึงข้อมูลยอดขายประจำวัน:', error);
                                document.getElementById('dailySales').textContent = '฿0';
                            });
                        
                        // Daily Sales Chart
                        fetch('getDailySales.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.labels && data.dailySales) {
                                    const ctx = document.getElementById('dailySalesChart').getContext('2d');
                                    const dailySalesChart = new Chart(ctx, {
                                        type: 'line',
                                        data: {
                                            labels: data.labels,
                                            datasets: [{
                                                label: 'ยอดขาย',
                                                data: data.dailySales,
                                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                                borderColor: 'rgba(75, 192, 192, 1)',
                                                borderWidth: 1,
                                                fill: true,
                                                tension: 0.4
                                            }]
                                        },
                                        options: {
                                            scales: {
                                                y: {
                                                    beginAtZero: true,
                                                    title: {
                                                        display: true,
                                                        text: 'ยอดขาย (฿)'
                                                    }
                                                },
                                                x: {
                                                    title: {
                                                        display: true,
                                                        text: 'วันที่'
                                                    }
                                                }
                                            },
                                            plugins: {
                                                legend: {
                                                    display: false
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูลยอดขายประจำวัน:', data.error);
                                }
                            })
                            .catch(error => {
                                console.error('เกิดข้อผิดพลาดในการดึงข้อมูลยอดขายประจำวัน:', error);
                            });
                    });
                </script>
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