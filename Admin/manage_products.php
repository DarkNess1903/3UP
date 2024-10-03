<?php
session_start();
include 'topnavbar.php';
include 'connectDB.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// การจัดการการเพิ่มสินค้า
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $cost = $_POST['cost'];
    $stock = $_POST['stock'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];

    if ($image) {
        move_uploaded_file($image_tmp, '../admin/product/' . $image);
    }

    $query = "INSERT INTO product (name, price, cost, stock_quantity, image) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sdids', $product_name, $price, $cost, $stock, $image);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">เพิ่มสินค้าสำเร็จ</div>';
    } else {
        echo '<div class="alert alert-danger">เกิดข้อผิดพลาดในการเพิ่มสินค้า: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// การจัดการการแก้ไขสินค้า
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $cost = $_POST['cost'];
    $stock = $_POST['stock'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];

    if ($image) {
        move_uploaded_file($image_tmp, '../admin/product/' . $image);
        $query = "UPDATE product SET name = ?, price = ?, cost = ?, stock_quantity = ?, image = ? WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sdidsi', $product_name, $price, $cost, $stock, $image, $product_id);
    } else {
        $query = "UPDATE product SET name = ?, price = ?, cost = ?, stock_quantity = ? WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sdidi', $product_name, $price, $cost, $stock, $product_id);
    }

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">อัพเดทสินค้าสำเร็จ</div>';
    } else {
        echo '<div class="alert alert-danger">เกิดข้อผิดพลาดในการอัพเดทสินค้า: ' . $stmt->error . '</div>';
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

    // ตรวจสอบว่ามีรูปภาพและไม่ว่างเปล่า
    if (!empty($product['image'])) {
        $file_path = '../admin/product/' . $product['image'];
        
        // ลบรูปภาพจากโฟลเดอร์ (ถ้าไฟล์มีอยู่จริง)
        if (file_exists($file_path) && is_file($file_path)) {
            unlink($file_path);
        } else {
            echo "ไม่พบไฟล์ที่ต้องการลบ";
        }
    }

    // ลบข้อมูลสินค้า
    $query = "DELETE FROM product WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">ลบสินค้าสำเร็จ</div>';
    } else {
        echo '<div class="alert alert-danger">เกิดข้อผิดพลาดในการลบสินค้า: ' . $stmt->error . '</div>';
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
        .modal-body img {
            max-width: 100%;
            height: auto;
        }

        h1 {
            font-size: 28px;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .table-responsive {
            overflow-x: auto;
        }
        
    </style>
</head>
<body>

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
                            <label for="cost">ต้นทุน:</label>
                            <input type="number" id="cost" name="cost" class="form-control" step="0.01" required>
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
                            <label for="edit_cost">ต้นทุน:</label>
                            <input type="number" id="edit_cost" name="cost" class="form-control" step="0.01" required>
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
    <div class="container mt-4">
        <h1 class="text-center">จัดการสินค้า</h1>

        <!-- ปุ่มเพิ่มสินค้า -->
        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addProductModal">
            เพิ่มสินค้า
        </button>

        <!-- ตารางสินค้า -->
        <div class="table-responsive mt-4">
            <table class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>ราคา</th>
                        <th>ต้นทุน</th>
                        <th>กำไรต่อชิ้น</th>
                        <th>จำนวนในสต็อก</th>
                        <th>กำไรทั้งหมด</th>
                        <th>รูปภาพสินค้า</th>
                        <th>การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { 
                        // คำนวณกำไรต่อชิ้นและกำไรทั้งหมด
                        $profit_per_item = $row['price'] - $row['cost'];
                        $total_profit = $profit_per_item * $row['stock_quantity'];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo number_format($row['price'], 2); ?> บาท</td>
                            <td><?php echo number_format($row['cost'], 2); ?> บาท</td>
                            <td><?php echo number_format($profit_per_item, 2); ?> บาท</td>
                            <td><?php echo htmlspecialchars($row['stock_quantity']); ?></td>
                            <td><?php echo number_format($total_profit, 2); ?> บาท</td>
                            <td>
                                <?php if ($row['image']) { ?>
                                    <img src="../admin/product/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image" class="img-fluid" style="max-width: 100px; height: auto;">
                                <?php } ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <!-- ปุ่มแก้ไข -->
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editProductModal"
                                            data-id="<?php echo $row['product_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                            data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                            data-cost="<?php echo htmlspecialchars($row['cost']); ?>"
                                            data-stock="<?php echo htmlspecialchars($row['stock_quantity']); ?>"
                                            data-image="<?php echo htmlspecialchars($row['image']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- ปุ่มเติมสต็อก -->
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#restockModal"
                                            data-id="<?php echo $row['product_id']; ?>">
                                        <i class="fas fa-box-open"></i>
                                    </button>

                                    <!-- ปุ่มลบ -->
                                    <a href="manage_products.php?delete=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้า?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#editProductModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            modal.find('#edit_product_id').val(button.data('id'));
            modal.find('#edit_product_name').val(button.data('name'));
            modal.find('#edit_price').val(button.data('price'));
            modal.find('#edit_cost').val(button.data('cost'));
            modal.find('#edit_stock').val(button.data('stock'));
            if (button.data('image')) {
                modal.find('#edit_image').val(button.data('image'));
            }
        });

        $('#restockModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            $(this).find('#restock_product_id').val(button.data('id'));
        });
    </script>
</body>
</html>
