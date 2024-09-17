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

    $query = "INSERT INTO product (name, price, stock_quantity, image) VALUES (?, ?, ?, ?)";
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
        $query = "UPDATE product SET name = ?, price = ?, stock_quantity = ?, image = ? WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sdisi', $product_name, $price, $stock, $image, $product_id);
    } else {
        $query = "UPDATE product SET name = ?, price = ?, stock_quantity = ? WHERE product_id = ?";
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

    $query = "UPDATE product SET stock_quantity = stock_quantity + ? WHERE product_id = ?";
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
    $query = "SELECT image FROM product WHERE product_id = ?";
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
    $query = "DELETE FROM product WHERE product_id = ?";
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
$query = "SELECT * FROM product";
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
                                <label for="edit_image">รูปภาพสินค้า:</label>
                                <input type="file" id="edit_image" name="image" class="form-control">
                            </div>
                            <button type="submit" name="edit_product" class="btn btn-primary">อัพเดทสินค้า</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- โมดัลฟอร์มเติมสต็อก -->
        <div class="modal fade" id="restockModal" tabindex="-1" role="dialog" aria-labelledby="restockModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="restockModalLabel">เติมสต็อกสินค้า</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="restockForm" action="manage_products.php" method="post">
                            <input type="hidden" id="restock_product_id" name="product_id">
                            <div class="form-group">
                                <label for="additional_stock">จำนวนที่ต้องการเติม:</label>
                                <input type="number" id="additional_stock" name="additional_stock" class="form-control" required>
                            </div>
                            <button type="submit" name="restock" class="btn btn-primary">เติมสต็อก</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ตารางสินค้า -->
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา</th>
                    <th>จำนวนในสต็อก</th>
                    <th>รูปภาพ</th>
                    <th>การกระทำ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock_quantity']); ?></td>
                    <td>
                        <?php if ($row['image']) { ?>
                        <img src="../admin/uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image" style="max-width: 100px;">
                        <?php } ?>
                    </td>
                    <td>
                        <!-- ปุ่มแก้ไข -->
                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editProductModal"
                                data-id="<?php echo $row['product_id']; ?>"
                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                data-stock="<?php echo htmlspecialchars($row['stock_quantity']); ?>"
                                data-image="<?php echo htmlspecialchars($row['image']); ?>">
                            แก้ไข
                        </button>
                        <!-- ปุ่มเติมสต็อก -->
                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#restockModal"
                                data-id="<?php echo $row['product_id']; ?>">
                            เติมสต็อก
                        </button>
                        <!-- ปุ่มลบ -->
                        <a href="manage_products.php?delete=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้า?');">ลบ</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#editProductModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var productId = button.data('id');
            var productName = button.data('name');
            var price = button.data('price');
            var stock = button.data('stock');
            var image = button.data('image');

            var modal = $(this);
            modal.find('#edit_product_id').val(productId);
            modal.find('#edit_product_name').val(productName);
            modal.find('#edit_price').val(price);
            modal.find('#edit_stock').val(stock);
            if (image) {
                modal.find('#edit_image').val(image);
            }
        });

        $('#restockModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var productId = button.data('id');

            var modal = $(this);
            modal.find('#restock_product_id').val(productId);
        });
    </script>
</body>
</html>
