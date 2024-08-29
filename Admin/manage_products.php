<?php
session_start();
include '../connectDB.php';
include 'topnavbar.php';


// การจัดการการเพิ่มผลิตภัณฑ์
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock_quantity = mysqli_real_escape_string($conn, $_POST['stock_quantity']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);

    // ตรวจสอบว่ามีการอัพโหลดรูปหรือไม่
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $upload_dir = '../Admin/product/';
        $upload_file = $upload_dir . basename($image['name']);

        // ย้ายไฟล์รูปไปยังโฟลเดอร์ที่กำหนด
        if (move_uploaded_file($image['tmp_name'], $upload_file)) {
            $image_path = $upload_file; // เก็บพาธของรูปภาพ
        } else {
            die("Error uploading image.");
        }
    } else {
        $image_path = ''; // ถ้าไม่มีรูปให้เก็บค่าว่าง
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // ตรวจสอบว่ากำลังแก้ไขหรือเพิ่มผลิตภัณฑ์ใหม่
        if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
            $product_id = intval($_POST['product_id']);
            $query = "UPDATE product SET name = ?, price = ?, stock_quantity = ?, details = ?, image = ? WHERE product_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssissi', $name, $price, $stock_quantity, $details, $image_path, $product_id);
        } else {
            $query = "INSERT INTO product (name, price, stock_quantity, details, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssiss', $name, $price, $stock_quantity, $details, $image_path);
        }

        if (mysqli_stmt_execute($stmt)) {
            header("Location: manage_products.php");
            exit();
        } else {
            die("Error executing query.");
        }
    }
}

// การจัดการการลบผลิตภัณฑ์
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $query = "DELETE FROM product WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    header("Location: manage_products.php");
    exit();
}

// การแสดงรายการผลิตภัณฑ์
$query = "SELECT * FROM product";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/scripts.js" defer></script>
</head>
<body>
    <header class="header">
        <h1>Manage Products</h1>
    </header>
    <main class="main-content">
        <section class="product-management">
            <div class="form-container">
                <button id="addProductBtn" class="btn">Add Product</button>
                <form id="addProductForm" class="hidden" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="product_id">
                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" required>
                    
                    <label for="price">Price:</label>
                    <input type="text" id="price" name="price" required>
                    
                    <label for="stock_quantity">Stock Quantity:</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" required>
                    
                    <label for="details">Details:</label>
                    <textarea id="details" name="details" rows="4" required></textarea>
                    
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" <?php echo isset($_GET['edit_id']) ? '' : 'required'; ?>>
                    
                    <button type="submit" class="btn">Save Product</button>
                </form>
            </div>
            <h2>Product List</h2>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Details</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo number_format($row['price'], 2); ?>฿</td>
                        <td>
                        <form action="update_stock.php" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <input type="number" name="new_stock" class="stock-input" value="<?php echo htmlspecialchars($row['stock_quantity']); ?>" min="0">
                            <button type="submit"class="btn btn-edi">Update Stock</button>
                        </form>
                        </td>
                        <td><?php echo htmlspecialchars($row['details']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image"></td>
                        <td>
                            <a href="manage_products.php?edit_id=<?php echo $row['product_id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="manage_products.php?delete_id=<?php echo $row['product_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            </table>
        </section>
    </main>
</body>
</html>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const addProductBtn = document.getElementById('addProductBtn');
    const addProductForm = document.getElementById('addProductForm');

    addProductBtn.addEventListener('click', function() {
        addProductForm.classList.toggle('hidden');
    });

    <?php if (isset($_GET['edit_id'])): ?>
        const productId = <?php echo intval($_GET['edit_id']); ?>;
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
                addProductForm.classList.remove('hidden');
            }
        };
        xhr.send();
    <?php endif; ?>
});

</script>