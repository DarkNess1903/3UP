<?php
include 'topnavbar.php';
include 'connectDB.php';

// ฟังก์ชันคำนวณกำไร
function calculateProfit($price, $cost) {
    return $price - $cost; // กำไรต่อชิ้น = ราคาขาย - ต้นทุน
}

if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'] ?? '';
    $price = $_POST['price'] ?? 0.0;
    $cost = $_POST['cost'] ?? 0.0;
    $stock = $_POST['stock'] ?? 0;
    $weight_per_item = $_POST['weight_per_item'] ?? 0;
    $can_be_sold_as_piece = $_POST['can_be_sold_as_piece'] ?? 0;

    if (empty($name)) {
        echo "ชื่อสินค้าต้องไม่ว่าง";
        exit; // หยุดการทำงานของสคริปต์
    }

    // คำนวณราคาแยกชิ้น ถ้าสามารถขายแยกชิ้นได้
    $piece_price = ($can_be_sold_as_piece == 1) ? (($price / 1000) * $weight_per_item + 2.50) : 0; // กำหนดค่าเป็น 0 แทน NULL

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "product/" . $image);
    }

    // ใช้ Prepared Statements
    $stmt = $conn->prepare("INSERT INTO product (name, price, cost, weight_per_item, stock_quantity, price_per_piece, can_be_sold_as_piece, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssdiddis', $name, $price, $cost, $weight_per_item, $stock, $piece_price, $can_be_sold_as_piece, $image);
    
    if ($stmt->execute()) {
        // แจ้งเตือนเมื่อเติมสต็อกสำเร็จ
        echo "<script>alert('เติมสต็อกสำเร็จ');</script>";
        // รีเฟรชหน้า
        echo "<script>window.location.href='manage_products.php';</script>";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }    
}

// แก้ไขสินค้า
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $cost = $_POST['cost'];
    $stock = $_POST['stock'];
    $weight_per_item = $_POST['weight_per_item'];
    $can_be_sold_as_piece = $_POST['can_be_sold_as_piece'] ?? 0; // ข้อมูลขายแยกชิ้น
    $piece_price = ($can_be_sold_as_piece == 1) ? (($price / 1000) * $weight_per_item + 2.50) : null; // คำนวณราคาแยกชิ้น

    // อัพโหลดรูปภาพใหม่ถ้ามี
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "product/" . $image);
        $sql = "UPDATE product SET name=?, price=?, cost=?, stock_quantity=?, weight_per_item=?, price_per_piece=?, can_be_sold_as_piece=?, image=? WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdiddisi', $name, $price, $cost, $stock, $weight_per_item, $piece_price, $can_be_sold_as_piece, $image, $product_id);
    } else {
        $sql = "UPDATE product SET name=?, price=?, cost=?, stock_quantity=?, weight_per_item=?, price_per_piece=?, can_be_sold_as_piece=? WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdiddii', $name, $price, $cost, $stock, $weight_per_item, $piece_price, $can_be_sold_as_piece, $product_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('แก้ไขสินค้าสำเร็จ');</script>";
        echo "<script>window.location.href='manage_products.php';</script>";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
}

// ลบสินค้า
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];

    $sql = "DELETE FROM product WHERE product_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);

    if ($stmt->execute()) {
        echo "<script>alert('ลบสินค้าสำเร็จ');</script>";
        echo "<script>window.location.href='manage_products.php';</script>";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
}

// เติมสต็อกสินค้า
if (isset($_POST['restock_product'])) {
    $product_id = $_POST['product_id'];
    $additional_stock = $_POST['quantity'];

    if (is_numeric($additional_stock) && $additional_stock > 0) {
        $sql = "UPDATE product SET stock_quantity = stock_quantity + ? WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $additional_stock, $product_id);

        if ($stmt->execute()) {
            echo "<script>alert('เติมสต็อกสำเร็จ');</script>";
            echo "<script>window.location.href='manage_products.php';</script>";
        } else {
            echo "เกิดข้อผิดพลาด: " . $stmt->error;
        }
    } else {
        echo "จำนวนที่เติมต้องเป็นตัวเลขที่มากกว่าศูนย์";
    }
}

// รัน SQL Query
$sql = "SELECT product_id, name, price, cost, stock_quantity, price_per_piece, image, weight_per_item, can_be_sold_as_piece FROM product";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <title>จัดการสินค้า</title>
    <style>
        .modal-body img {
            max-width: 100%;
            height: auto;
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
                            <label for="price">ราคาขาย (กก.):</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="cost">ต้นทุน:</label>
                            <input type="number" id="cost" name="cost" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="weight_per_item">น้ำหนักต่อชิ้น (กรัม):</label>
                            <input type="number" id="weight_per_item" name="weight_per_item" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">สต็อก (กก.):</label>
                            <input type="number" id="stock" name="stock" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="can_be_sold_as_piece">สามารถขายแยกชิ้นได้หรือไม่:</label>
                            <select id="can_be_sold_as_piece" name="can_be_sold_as_piece" class="form-control" required>
                                <option value="1">ใช่</option>
                                <option value="0">ไม่</option>
                            </select>
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
                        <label for="edit_price">ราคาขาย (กก.):</label>
                        <input type="number" id="edit_price" name="price" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_cost">ต้นทุน:</label>
                        <input type="number" id="edit_cost" name="cost" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_weight_per_item">น้ำหนักต่อชิ้น (กรัม):</label>
                        <input type="number" id="edit_weight_per_item" name="weight_per_item" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_stock">สต็อก (กก.):</label>
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

    <!-- Modal สำหรับเติมสต็อก -->
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
                    <form id="restockForm">
                        <input type="hidden" id="restock_product_id" name="product_id">
                        <div class="form-group">
                            <label for="restock_quantity">จำนวนที่เติม: (กก.)</label>
                            <input type="number" class="form-control" id="restock_quantity" name="quantity" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                            <button type="submit" class="btn btn-primary">เติมสต็อก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางสินค้า -->
    <div class="container mt-4">
        <h1 class="text-center mb-4">จัดการสินค้า</h1>

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
                        <th>สต็อก (กก.)</th>
                        <th>ราคาต่อชิ้น</th>
                        <th>กำไรทั้งหมด</th>
                        <th>รูปภาพสินค้า</th>
                        <th>การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { 
                        // ตรวจสอบการตั้งค่ากำไร
                        $profit_per_piece = $row['price'] - $row['cost'];
                        $total_profit = $profit_per_piece * ($row['stock_quantity'] * 1000 / $row['weight_per_item']); // คำนวณจากสต็อกเป็นชิ้น
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo number_format($row['price'], 2); ?> บาท</td>
                            <td><?php echo number_format($row['cost'], 2); ?> บาท</td>
                            <td><?php echo number_format($profit_per_piece, 2); ?> บาท</td>
                            <td><?php echo $row['stock_quantity']; ?> กก.</td>
                            <td>
                                <?php 
                                // ตรวจสอบว่าขายแยกชิ้นได้หรือไม่
                                if ($row['can_be_sold_as_piece'] == 1) {
                                    echo number_format($row['price_per_piece'], 2) . ' บาท';
                                } else {
                                    echo 'ไม่มีแยกชิ้นขาย';
                                }
                                ?>
                            </td>
                            <td><?php echo number_format($total_profit, 2); ?> บาท</td>
                            <td>
                                <img src="product/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 100px; height: auto;">
                            </td>
                            <td>
                            <div class="btn-group" role="group">
                                    <!-- ปุ่มแก้ไข -->
                                    <button class="btn btn-warning btn-sm editBtn" data-toggle="modal" data-target="#editProductModal"
                                        data-id="<?php echo $row['product_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                        data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                        data-cost="<?php echo htmlspecialchars($row['cost']); ?>"
                                        data-weight="<?php echo htmlspecialchars($row['weight_per_item']); ?>"
                                        data-stock="<?php echo htmlspecialchars($row['stock_quantity']); ?>"
                                        data-price-per-piece="<?php echo htmlspecialchars($row['can_be_sold_as_piece'] == 1 ? $row['price_per_piece'] : ''); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- ปุ่มเติมสต็อก -->
                                    <button class="btn btn-info btn-sm restockBtn" data-toggle="modal" data-target="#restockModal" data-id="<?php echo $row['product_id']; ?>">
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

    <script>
    // เมื่อคลิกปุ่มแก้ไขสินค้า
    $(document).on('click', '.editBtn', function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        const price = $(this).data('price');
        const cost = $(this).data('cost');
        const weight = $(this).data('weight');
        const stock = $(this).data('stock');
        const pricePerPiece = $(this).data('price-per-piece');

        // กำหนดค่าให้กับฟิลด์ในฟอร์มแก้ไข
        $('#edit_product_id').val(productId);
        $('#edit_product_name').val(productName);
        $('#edit_price').val(price);
        $('#edit_cost').val(cost);
        $('#edit_weight_per_item').val(weight);
        $('#edit_stock').val(stock);
        $('#edit_price_per_piece').val(pricePerPiece);

        // แสดง Modal แก้ไขสินค้า
        $('#editProductModal').modal('show');
    });

        // เมื่อคลิกปุ่มเติมสต็อก
        $(document).on('click', '.restockBtn', function() {
            const productId = $(this).data('id');
            $('#restock_product_id').val(productId); // กำหนดค่า product_id ในฟอร์มเติมสต็อก
            $('#restockModal').modal('show'); // แสดง Modal
        });

        // เมื่อส่งฟอร์มเติมสต็อก
        $('#restockForm').on('submit', function(e) {
            e.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ
            const productId = $('#restock_product_id').val();
            const quantity = $('#restock_quantity').val();

            // ส่งข้อมูลไปยังเซิร์ฟเวอร์
            $.ajax({
                url: 'manage_products.php', // เปลี่ยนให้ตรงกับ URL ที่ต้องการส่งข้อมูล
                method: 'POST',
                data: { restock_product: true, product_id: productId, quantity: quantity },
                success: function(response) {
                    alert('เติมสต็อกสำเร็จ'); // แจ้งเตือนเมื่อเติมสต็อกสำเร็จ
                    $('#restockModal').modal('hide'); // ปิด Modal หลังเติมสต็อกเสร็จ
                    location.reload(); // โหลดหน้าใหม่เพื่อดูข้อมูลที่อัปเดต
                },
                error: function(xhr, status, error) {
                    alert('เกิดข้อผิดพลาด: ' + error); // แจ้งเตือนเมื่อเกิดข้อผิดพลาด
                }
            });
        });

    // เมื่อคลิกปุ่มลบสินค้า
    $(document).on('click', '.deleteBtn', function(e) {
        e.preventDefault(); // ป้องกันการเปลี่ยนหน้า
        const productId = $(this).data('id');
        if (confirm('คุณแน่ใจว่าจะลบสินค้านี้?')) {
            $.ajax({
                url: 'manage_products.php',
                method: 'POST',
                data: { delete_product: true, product_id: productId },
                success: function(response) {
                    location.reload(); // โหลดหน้าใหม่เพื่อตรวจสอบการลบ
                },
                error: function(xhr, status, error) {
                    alert('เกิดข้อผิดพลาด: ' + error);
                }
            });
        }
    });
</script>
</body>
</html>