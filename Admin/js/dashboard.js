// dashboard.js
document.addEventListener('DOMContentLoaded', function () {
    // ดึงข้อมูลจากไฟล์ PHP
    fetch('fetch_summary_data.php')
        .then(response => response.json())
        .then(data => {
            // เตรียมข้อมูลสำหรับรายวัน
            const dailyLabels = data.daily.map(item => item.date);
            const dailySalesData = data.daily.map(item => item.total_sales);
            const dailyOrdersData = data.daily.map(item => item.total_orders);

            // สร้างกราฟรายวัน
            const ctxDaily = document.getElementById('dailySalesChart').getContext('2d');
            const dailySalesChart = new Chart(ctxDaily, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [
                        {
                            label: 'ยอดขายรายวัน',
                            data: dailySalesData,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            yAxisID: 'y1',
                        },
                        {
                            label: 'จำนวนออเดอร์รายวัน',
                            data: dailyOrdersData,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            yAxisID: 'y2',
                        }
                    ]
                },
                options: {
                    scales: {
                        y1: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'left',
                        },
                        y2: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'right',
                        }
                    }
                }
            });

            // เตรียมข้อมูลสำหรับรายเดือน
            const monthlyLabels = data.monthly.map(item => item.month);
            const monthlySalesData = data.monthly.map(item => item.total_sales);
            const monthlyOrdersData = data.monthly.map(item => item.total_orders);

            // สร้างกราฟรายเดือน
            const ctxMonthly = document.getElementById('monthlySalesChart').getContext('2d');
            const monthlySalesChart = new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [
                        {
                            label: 'ยอดขายรายเดือน',
                            data: monthlySalesData,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            yAxisID: 'y1',
                        },
                        {
                            label: 'จำนวนออเดอร์รายเดือน',
                            data: monthlyOrdersData,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            yAxisID: 'y2',
                        }
                    ]
                },
                options: {
                    scales: {
                        y1: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'left',
                        },
                        y2: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'right',
                        }
                    }
                }
            });

            // เตรียมข้อมูลสำหรับรายปี
            const yearlyLabels = data.yearly.map(item => item.year);
            const yearlySalesData = data.yearly.map(item => item.total_sales);
            const yearlyOrdersData = data.yearly.map(item => item.total_orders);

            // สร้างกราฟรายปี
            const ctxYearly = document.getElementById('yearlySalesChart').getContext('2d');
            const yearlySalesChart = new Chart(ctxYearly, {
                type: 'bar',
                data: {
                    labels: yearlyLabels,
                    datasets: [
                        {
                            label: 'ยอดขายรายปี',
                            data: yearlySalesData,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            yAxisID: 'y1',
                        },
                        {
                            label: 'จำนวนออเดอร์รายปี',
                            data: yearlyOrdersData,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            yAxisID: 'y2',
                        }
                    ]
                },
                options: {
                    scales: {
                        y1: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'left',
                        },
                        y2: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'right',
                        }
                    }
                }
            });

        })
        .catch(error => console.error('Error:', error));
});
