document.addEventListener('DOMContentLoaded', function () {
    // ส่วนของการจัดการโมดัลสำหรับดูสลิปการชำระเงิน
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("img01");
    var captionText = document.getElementById("caption");
    var closeBtn = document.getElementsByClassName("close")[0];

    document.querySelectorAll('.view-payment-slip').forEach(function (link) {
        link.onclick = function (event) {
            event.preventDefault(); // ป้องกันการรีเฟรชหน้า
            var imageUrl = this.getAttribute('data-image');
            modal.style.display = "block";
            modalImg.src = imageUrl;
            captionText.innerHTML = "Payment Slip";
        };
    });

    closeBtn.onclick = function () {
        modal.style.display = "none";
    };

    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    // ส่วนของการจัดการเอฟเฟคเคลื่อนที่ของสินค้าไปยังไอคอนตะกร้า
    const addToCartButtons = document.querySelectorAll('.btn');
    const cartIcon = document.querySelector('.cart-icon');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            // คัดลอกภาพสินค้า
            const product = event.target.closest('.product');
            const imgToFly = product.querySelector('img').cloneNode();
            imgToFly.classList.add('fly-to-cart');

            // ใส่ภาพลงใน product แล้วเริ่มอนิเมชั่น
            product.appendChild(imgToFly);

            // ลบภาพหลังจากอนิเมชั่นเสร็จสิ้น
            imgToFly.addEventListener('animationend', function() {
                imgToFly.remove();
            });

            // เพิ่มสินค้าไปที่ตะกร้า
            // ตัวอย่าง: คุณสามารถใช้ XMLHttpRequest หรือ Fetch API เพื่อส่งข้อมูลไปยังเซิร์ฟเวอร์ได้ที่นี่
        });
    });
});