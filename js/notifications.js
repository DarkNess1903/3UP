function fetchNotifications() {
    $.ajax({
        url: 'get_notifications.php',
        method: 'GET',
        success: function(data) {
            $('#notification-list').html(data);
        }
    });
}

// เรียกใช้ฟังก์ชันทุกๆ 30 วินาที
setInterval(fetchNotifications, 30000);

// เรียกใช้ฟังก์ชันเมื่อเริ่มต้นหน้า
fetchNotifications();
