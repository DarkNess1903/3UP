document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("img01");
    var captionText = document.getElementById("caption");
    var closeBtn = document.getElementsByClassName("close")[0];

    // เปิดโมดัลเมื่อคลิกที่ลิงก์
    document.querySelectorAll('.view-payment-slip').forEach(function (link) {
        link.onclick = function (event) {
            event.preventDefault(); // ป้องกันการรีเฟรชหน้า
            var imageUrl = this.getAttribute('data-image');
            modal.style.display = "block";
            modalImg.src = imageUrl;
            captionText.innerHTML = "Payment Slip";
        };
    });

    // ปิดโมดัลเมื่อคลิกที่ปุ่มปิด
    closeBtn.onclick = function () {
        modal.style.display = "none";
    };

    // ปิดโมดัลเมื่อคลิกที่พื้นหลัง
    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
});

