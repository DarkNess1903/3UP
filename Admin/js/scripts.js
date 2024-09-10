document.addEventListener('DOMContentLoaded', function () {
    const addProductBtn = document.getElementById('addProductBtn');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'), { keyboard: false });
    const productForm = document.getElementById('productForm');

    addProductBtn.addEventListener('click', function () {
        document.getElementById('product_id').value = ''; // รีเซ็ตฟอร์ม
        document.getElementById('name').value = '';
        document.getElementById('price').value = '';
        document.getElementById('stock_quantity').value = '';
        document.getElementById('details').value = '';
        document.getElementById('image').removeAttribute('required'); // ไม่ต้องการรูปใหม่เมื่อแก้ไข
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
                    // อัปเดตข้อมูลในตารางสินค้าหลังจากเพิ่ม/แก้ไขสินค้า
                    window.location.reload(); // รีเฟรชเพื่ออัปเดตตาราง
                } else {
                    alert('Error: ' + response.message);
                }
                productModal.hide(); // ปิด Modal หลังจากส่งข้อมูล
            }
        };
        xhr.send(formData);
    });
});

function editProduct(productId) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_product.php?id=' + productId, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const product = JSON.parse(xhr.responseText);
            document.getElementById('product_id').value = product.product_id;
            document.getElementById('name').value = product.name;
            document.getElementById('price').value = product.price;
            document.getElementById('stock_quantity').value = product.stock_quantity;
            document.getElementById('details').value = product.details;
            document.getElementById('image').removeAttribute('required'); // ไม่ต้องการรูปใหม่เมื่อแก้ไข
            document.querySelector('.modal-title').textContent = 'Edit Product';
            const productModal = new bootstrap.Modal(document.getElementById('productModal'), { keyboard: false });
            productModal.show();
        }
    };
    xhr.send();
}

document.addEventListener('DOMContentLoaded', function () {
    const addProductBtn = document.getElementById('addProductBtn');
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));

    addProductBtn.addEventListener('click', function () {
        document.getElementById('productForm').reset();
        document.getElementById('product_id').value = '';
        document.getElementById('image').removeAttribute('required');
        document.getElementById('productModalLabel').innerText = 'Add Product';
        productModal.show();
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
                    document.getElementById('productModalLabel').innerText = 'Edit Product';
                    productModal.show();
                });
        });
    });
});
    
