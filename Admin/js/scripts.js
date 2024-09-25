document.addEventListener('DOMContentLoaded', function () {
    const addProductBtn = document.getElementById('addProductBtn');
    const productForm = document.getElementById('productForm');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'), { keyboard: false });

    addProductBtn.addEventListener('click', function () {
        document.getElementById('productForm').reset();
        document.getElementById('product_id').value = '';
        document.getElementById('image').removeAttribute('required');
        document.querySelector('.modal-title').textContent = 'Add Product';
        productModal.show();
    });

    productForm.addEventListener('submit', function (e) {
        e.preventDefault(); // หยุดการรีเฟรชหน้า
        const formData = new FormData(productForm);

        // ส่งฟอร์มด้วย AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'save_product.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('Product ' + (response.isEdit ? 'updated' : 'added') + ' successfully.');
                    window.location.reload(); // รีเฟรชเพื่ออัปเดตตาราง
                } else {
                    alert('Error: ' + response.message);
                }
                productModal.hide(); // ปิด Modal หลังจากส่งข้อมูล
            }
        };
        xhr.send(formData);
    });

    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const productId = button.getAttribute('data-product-id');
            fetch('get_product.php?id=' + productId)
                .then(response => response.json())
                .then(product => {
                    document.getElementById('product_id').value = product.product_id;
                    document.getElementById('name').value = product.name;
                    document.getElementById('price').value = product.price;
                    document.getElementById('stock_quantity').value = product.stock_quantity;
                    document.getElementById('details').value = product.details;
                    document.getElementById('image').removeAttribute('required');
                    document.querySelector('.modal-title').textContent = 'Edit Product';
                    productModal.show();
                });
        });
    });
});

document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function(event) {
        event.preventDefault(); // ป้องกันการ reload หน้าเมื่อคลิก
        const period = this.getAttribute('data-period');

        // อัปเดตกราฟ
        fetchEarnings(period);

        // เปลี่ยนชื่อรายการที่ถูกเลือกใน dropdown
        document.querySelector('#dropdownMenuLink').innerText = `เลือกช่วงเวลา: ${this.innerText}`;
    });
});
