<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Admin';

// Handle add/edit/delete
$upload_dir = __DIR__ . '/../uploads/products/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

function saveUploadedImage($fileKey, $uploadDir, $allowedTypes) {
    if (empty($_FILES[$fileKey]['name']) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return null;
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES[$fileKey]['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedTypes)) return null;
    $mimeToExt = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $ext = $mimeToExt[$mime] ?? strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION) ?: 'jpg');
    $filename = uniqid('prod_') . '.' . $ext;
    $path = $uploadDir . $filename;
    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $path)) return $filename;
    return null;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idCol = 'p_id'; // use 'id' if your table uses id as PK
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = $_POST['category'] ?? 'hot';
        if (in_array($category, ['hot','cold','bread']) && $name && $price > 0) {
            $imageName = saveUploadedImage('image', $upload_dir, $allowed_types);
            $stmt = $conn->prepare("INSERT INTO products (name, price, category, image) VALUES (?, ?, ?, ?)");
            $img = $imageName ?? null;
            $stmt->bind_param("sdss", $name, $price, $category, $img);
            if ($stmt->execute()) $msg = 'Product added.';
            $stmt->close();
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = $_POST['category'] ?? 'hot';
        if ($id && $name && $price > 0 && in_array($category, ['hot','cold','bread'])) {
            $imageName = saveUploadedImage('image', $upload_dir, $allowed_types);
            $sql = $imageName ? "UPDATE products SET name=?, price=?, category=?, image=? WHERE $idCol=?" : "UPDATE products SET name=?, price=?, category=? WHERE $idCol=?";
            $stmt = $conn->prepare($sql);
            if ($imageName) {
                $stmt->bind_param("sdssi", $name, $price, $category, $imageName, $id);
            } else {
                $stmt->bind_param("sdsi", $name, $price, $category, $id);
            }
            if ($stmt->execute()) $msg = 'Product updated.';
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $conn->query("DELETE FROM products WHERE $idCol = $id");
            $msg = 'Product deleted.';
        }
    }
    header("Location: products.php?msg=" . urlencode($msg));
    exit;
}

$products = [];
$r = mysqli_query($conn, "SELECT * FROM products ORDER BY category, name");
if ($r) while ($row = mysqli_fetch_assoc($r)) $products[] = $row;
$msg = $_GET['msg'] ?? '';

// Base path for image URLs (works when app is in subfolder e.g. /debuggggcaf/)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($basePath === '' || $basePath === '.') $basePath = '';
$imgBase = $basePath ? $basePath . '/' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Debug Café</title>
    <link rel="stylesheet" href="../style/admin.css">
    <link rel="stylesheet" href="../style/products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../images/logo.png" alt="Debug Café" class="sidebar-logo">
            <h1 class="sidebar-brand">Debug Café</h1>
            <p class="sidebar-tagline">Coffee & Code</p>
        </div>
        <nav class="nav-links">
            <a href="admin.php" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="neworder.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>New Order</span></a>
            <a href="orders.php" class="nav-item"><i class="fas fa-list"></i><span>Orders</span></a>
            <a href="transactions.php" class="nav-item"><i class="fas fa-exchange-alt"></i><span>Transactions</span></a>
            <a href="products.php" class="nav-item active"><i class="fas fa-box"></i><span>Manage Products</span></a>
             <a href="users.php" class="nav-item "><i class="fas fa-box"></i><span>Manage Users</span></a>
        </nav>
        <a href="logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h2 class="dashboard-title">Manage Products</h2>
            <button type="button" class="btn-add-product" onclick="openModal()">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </header>

        <?php if ($msg): ?>
            <p class="msg-success"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>

        <section class="recent-orders">
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $p): ?>
                                <?php $pid = (int)($p['p_id'] ?? $p['id'] ?? 0); ?>
                                <tr>
                                    <td><?php echo $pid; ?></td>
                                    <td>
                                        <?php if (!empty($p['image']) && file_exists($upload_dir . $p['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($imgBase); ?>../uploads/products/<?php echo htmlspecialchars($p['image']); ?>" alt="" class="product-thumb">
                                        <?php else: ?>
                                            <span class="no-img">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['name'] ?? ''); ?></td>
                                    <td>₱<?php echo number_format((float)($p['price'] ?? 0), 2); ?></td>
                                    <td><?php echo ucfirst($p['category'] ?? 'hot'); ?></td>
                                    <td>
                                        <button type="button" class="btn-edit" onclick="editProduct(<?php echo htmlspecialchars(json_encode($p)); ?>)">Edit</button>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $pid; ?>">
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-orders">No products. Add some to get started.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="productModal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="modalTitle">Add Product</h3>
            <form method="POST" id="productForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="formId" value="">
                <label>Name <input type="text" name="name" id="formName" required></label>
                <label>Price (₱) <input type="number" name="price" id="formPrice" step="0.01" min="0" required></label>
                <label>Image <input type="file" name="image" id="formImage" accept="image/jpeg,image/png,image/gif,image/webp"></label>
                <p class="form-hint">Optional. JPG, PNG, GIF or WebP.</p>
                <div id="currentImage" class="current-image"></div>
                <label>Category
                    <select name="category" id="formCategory">
                        <option value="hot">Hot</option>
                        <option value="cold">Cold</option>
                        <option value="bread">Bread</option>
                    </select>
                </label>
                <div class="modal-buttons">
                    <button type="submit" class="btn-save">Save</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var IMG_BASE = <?php echo json_encode($imgBase); ?>;
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('formId').value = '';
            document.getElementById('formName').value = '';
            document.getElementById('formPrice').value = '';
            document.getElementById('formImage').value = '';
            document.getElementById('currentImage').innerHTML = '';
            document.getElementById('productModal').classList.add('open');
        }
        function editProduct(p) {
            var pid = p.p_id || p.id;
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('formId').value = pid;
            document.getElementById('formName').value = p.name || '';
            document.getElementById('formPrice').value = parseFloat(p.price) || 0;
            document.getElementById('formCategory').value = p.category || 'hot';
            document.getElementById('formImage').value = '';
            var img = document.getElementById('currentImage');
            if (p.image) {
                img.innerHTML = '<p>Current:</p><img src="' + IMG_BASE + '../uploads/products/' + p.image.replace(/["'<>]/g, '') + '" alt="" style="max-width:80px;border-radius:8px;">';
            } else {
                img.innerHTML = '';
            }
            document.getElementById('productModal').classList.add('open');
        }
        function closeModal() {
            document.getElementById('productModal').classList.remove('open');
        }
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
