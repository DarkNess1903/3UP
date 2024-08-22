document.addEventListener('DOMContentLoaded', function() {
    function fetchNewOrders() {
        fetch('getNewOrders.php') // URL ของ PHP script ที่ดึงข้อมูลออเดอร์ใหม่
            .then(response => response.json())
            .then(data => {
                const alertsDropdown = document.querySelector('#alertsDropdown');
                const badgeCounter = alertsDropdown.querySelector('.badge-counter');
                const dropdownList = alertsDropdown.nextElementSibling;

                // Clear previous alerts
                dropdownList.innerHTML = `
                    <h6 class="dropdown-header">Alerts Center</h6>
                `;

                // Update badge counter
                badgeCounter.textContent = data.length + '+';

                // Add new alerts
                data.forEach(order => {
                    dropdownList.innerHTML += `
                        <a class="dropdown-item d-flex align-items-center" href="orderDetails.php?order_id=${order.order_id}">
                            <div class="mr-3">
                                <div class="icon-circle bg-info">
                                    <i class="fas fa-shopping-cart text-white"></i>
                                </div>
                            </div>
                            <div>
                                <div class="small text-gray-500">${order.order_date}</div>
                                <span class="font-weight-bold">New order #${order.order_id} received!</span>
                            </div>
                        </a>
                    `;
                });

                dropdownList.innerHTML += '<a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>';
            })
            .catch(error => console.error('Error:', error));
    }

    // Fetch new orders initially
    fetchNewOrders();

    // Optionally, set up a polling interval to check for new orders periodically
    setInterval(fetchNewOrders, 60000); // Check every 60 seconds
});
