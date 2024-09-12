<?php
session_start();
include 'topnavbar.php';
include '../connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// การจัดการการเพิ่มสินค้า
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    
    // อัปโหลดรูปภาพ
    if ($image) {
        move_uploaded_file($image_tmp, '../admin/uploads/' . $image);
    }

    $query = "INSERT INTO Products (product_name, price, stock, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sdis', $product_name, $price, $stock, $image);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Product added successfully</div>';
    } else {
        echo '<div class="alert alert-danger">Error adding product: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// การจัดการการแก้ไขสินค้า
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];

    // อัปโหลดรูปภาพถ้ามีการเปลี่ยนแปลง
    if ($image) {
        move_uploaded_file($image_tmp, '../admin/uploads/' . $image);
        $query = "UPDATE Products SET product_name = ?, price = ?, stock = ?, image = ? WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sdisi', $product_name, $price, $stock, $image, $product_id);
    } else {
        $query = "UPDATE Products SET product_name = ?, price = ?, stock = ? WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sidi', $product_name, $price, $stock, $product_id);
    }

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Product updated successfully</div>';
    } else {
        echo '<div class="alert alert-danger">Error updating product: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// การจัดการการเติมสต็อก
if (isset($_POST['restock'])) {
    $product_id = $_POST['product_id'];
    $additional_stock = $_POST['additional_stock'];

    $query = "UPDATE Products SET stock = stock + ? WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $additional_stock, $product_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Stock updated successfully</div>';
    } else {
        echo '<div class="alert alert-danger">Error updating stock: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// การจัดการการลบสินค้า
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    // ดึงข้อมูลสินค้าเพื่อลบรูปภาพ
    $query = "SELECT image FROM Products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    // ลบรูปภาพจากโฟลเดอร์
    if ($product['image']) {
        unlink('../admin/uploads/' . $product['image']);
    }

    // ลบข้อมูลสินค้า
    $query = "DELETE FROM Products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Product deleted successfully</div>';
    } else {
        echo '<div class="alert alert-danger">Error deleting product: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// ดึงข้อมูลสินค้า
$query = "SELECT * FROM Products";
$result = mysqli_query($conn, $query);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css"> <!-- ลิงก์ไปยังไฟล์ CSS ของคุณ -->
    <style>
        .modal-body img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <header>
        <!-- ใส่ Navbar ของคุณที่นี่ -->
    </header>

    <div class="container mt-4">
        <h1>จัดการสินค้า</h1>

        <!-- ปุ่มเพิ่มสินค้า -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
            เพิ่มสินค้า
        </button>

        <!-- โมดัลฟอร์มเพิ่มสินค้า -->
        <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">เพิ่มสินค้า</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="manage_products.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="product_name">ชื่อสินค้า:</label>
                                <input type="text" id="product_name" name="product_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="price">ราคา:</label>
                                <input type="number" id="price" name="price" class="form-control" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="stock">จำนวนในสต็อก:</label>
                                <input type="number" id="stock" name="stock" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="image">รูปภาพสินค้า:</label>
                                <input type="file" id="image" name="image" class="form-control">
                            </div>
                            <button type="submit" name="add_product" class="btn btn-primary">เพิ่มสินค้า</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- โมดัลฟอร์มแก้ไขสินค้า -->
        <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">แก้ไขสินค้า</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editProductForm" action="manage_products.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" id="edit_product_id" name="product_id">
                            <div class="form-group">
                                <label for="edit_product_name">ชื่อสินค้า:</label>
                                <input type="text" id="edit_product_name" name="product_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_price">ราคา:</label>
                                <input type="number" id="edit_price" name="price" class="form-control" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_stock">จำนวนในสต็อก:</label>
                                <input type="number" id="edit_stock" name="stock" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_image">รูปภาพสินค้า (ถ้ามีการเปลี่ยนแปลง):</label>
                                <input type="file" id="edit_image" name="image" class="form-control">
                            </div>
                            <button type="submit" name="edit_product" class="btn btn-primary">อัปเดตสินค้า</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- โมดัลฟอร์มเติมสต็อก -->
        <div class="modal fade" id="restockProductModal" tabindex="-1" role="dialog" aria-labelledby="restockProductModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="restockProductModalLabel">เติมสต็อกสินค้า</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="restockProductForm" action="manage_products.php" method="post">
                            <input type="hidden" id="restock_product_id" name="product_id">
                            <div class="form-group">
                                <label for="additional_stock">จำนวนที่เติม:</label>
                                <input type="number" id="additional_stock" name="additional_stock" class="form-control" required>
                            </div>
                            <button type="submit" name="restock" class="btn btn-primary">เติมสต็อก</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ตารางแสดงสินค้า -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>รหัสสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา</th>
                    <th>จำนวนในสต็อก</th>
                    <th>รูปภาพ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td>
                            <?php if ($product['image']): ?>
                                <img src="../admin/uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" width="100">
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editProductModal" data-id="<?php echo htmlspecialchars($product['product_id']); ?>" data-name="<?php echo htmlspecialchars($product['product_name']); ?>" data-price="<?php echo htmlspecialchars($product['price']); ?>" data-stock="<?php echo htmlspecialchars($product['stock']); ?>" data-image="<?php echo htmlspecialchars($product['image']); ?>">แก้ไข</button>
                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#restockProductModal" data-id="<?php echo htmlspecialchars($product['product_id']); ?>">เติมสต็อก</button>
                            <a href="manage_products.php?delete=<?php echo htmlspecialchars($product['product_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้า?')">ลบ</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <!-- ใส่ Footer ของคุณที่นี่ -->
    </footer>

    <!-- รวม JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // ตั้งค่าโมดัลแก้ไข
            $('#editProductModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var productId = button.data('id');
                var productName = button.data('name');
                var productPrice = button.data('price');
                var productStock = button.data('stock');
                var productImage = button.data('image');

                var modal = $(this);
                modal.find('#edit_product_id').val(productId);
                modal.find('#edit_product_name').val(productName);
                modal.find('#edit_price').val(productPrice);
                modal.find('#edit_stock').val(productStock);
                modal.find('#edit_image').val('');
                modal.find('.modal-body img').attr('src', productImage ? '../admin/uploads/' + productImage : '');
            });

            // ตั้งค่าโมดัลเติมสต็อก
            $('#restockProductModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var productId = button.data('id');
                
                var modal = $(this);
                modal.find('#restock_product_id').val(productId);
            });
        });
    </script>
</body>
</html>
