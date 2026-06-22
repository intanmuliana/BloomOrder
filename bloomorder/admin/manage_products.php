<?php
require_once 'config.php';
requireLogin();

$products = loadData('products.json');
$message = '';
$error = '';

// Handle upload directory
$uploadDir = __DIR__ . '/../uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Handle file upload
        $photoPath = '';
        if (isset($_FILES['product_photo']) && $_FILES['product_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_photo'];
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $photoPath = 'uploads/products/' . $fileName;
            }
        }
        
        $newProduct = [
            'id' => uniqid(),
            'name' => $_POST['name'] ?? '',
            'price_s' => intval($_POST['price_s'] ?? 0),
            'price_m' => intval($_POST['price_m'] ?? 0),
            'price_l' => intval($_POST['price_l'] ?? 0),
            'photo' => $photoPath,
            'photos' => [$photoPath], // Untuk multiple photos (slider)
            'color' => $_POST['color'] ?? '#ff6b9d'
        ];
        
        if (!empty($newProduct['name'])) {
            $products[] = $newProduct;
            saveData('products.json', $products);
            $message = 'Produk berhasil ditambahkan!';
        } else {
            $error = 'Nama produk tidak boleh kosong!';
        }
    } elseif (isset($_POST['edit_product'])) {
        $id = $_POST['product_id'];
        foreach ($products as &$product) {
            if ($product['id'] === $id) {
                $product['name'] = $_POST['name'];
                $product['price_s'] = intval($_POST['price_s']);
                $product['price_m'] = intval($_POST['price_m']);
                $product['price_l'] = intval($_POST['price_l']);
                $product['color'] = $_POST['color'];
                
                // Handle new photo upload for edit
                if (isset($_FILES['product_photo']) && $_FILES['product_photo']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['product_photo'];
                    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
                    $targetPath = $uploadDir . $fileName;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $product['photo'] = 'uploads/products/' . $fileName;
                        // Add to photos array if not exists
                        if (!in_array($product['photo'], $product['photos'] ?? [])) {
                            $product['photos'][] = $product['photo'];
                        }
                    }
                }
                break;
            }
        }
        saveData('products.json', $products);
        $message = 'Produk berhasil diupdate!';
    } elseif (isset($_POST['delete_product'])) {
        $id = $_POST['product_id'];
        // Find and delete photo file
        foreach ($products as $product) {
            if ($product['id'] === $id && !empty($product['photo'])) {
                $filePath = __DIR__ . '/../' . $product['photo'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        $products = array_values(array_filter($products, function($p) use ($id) {
            return $p['id'] !== $id;
        }));
        saveData('products.json', $products);
        $message = 'Produk berhasil dihapus!';
    } elseif (isset($_POST['add_photo'])) {
        // Add additional photo to product
        $id = $_POST['product_id'];
        if (isset($_FILES['additional_photo']) && $_FILES['additional_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['additional_photo'];
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                foreach ($products as &$product) {
                    if ($product['id'] === $id) {
                        if (!isset($product['photos'])) {
                            $product['photos'] = [];
                        }
                        $product['photos'][] = 'uploads/products/' . $fileName;
                        break;
                    }
                }
                saveData('products.json', $products);
                $message = 'Foto berhasil ditambahkan!';
            }
        }
    } elseif (isset($_POST['delete_photo'])) {
        $id = $_POST['product_id'];
        $photoIndex = intval($_POST['photo_index']);
        foreach ($products as &$product) {
            if ($product['id'] === $id && isset($product['photos'][$photoIndex])) {
                $filePath = __DIR__ . '/../' . $product['photos'][$photoIndex];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                array_splice($product['photos'], $photoIndex, 1);
                // Update main photo if deleted
                if ($photoIndex == 0 && !empty($product['photos'])) {
                    $product['photo'] = $product['photos'][0];
                } elseif (empty($product['photos'])) {
                    $product['photo'] = '';
                }
                break;
            }
        }
        saveData('products.json', $products);
        $message = 'Foto berhasil dihapus!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin BloomOrder</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        .admin-container { display: flex; min-height: 100vh; }
        .admin-nav { width: 260px; background: #1a1a2e; color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .nav-brand { padding: 24px 20px; font-size: 1.3rem; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); color: #e8b4b8; }
        .nav-menu { list-style: none; padding: 20px 0; }
        .nav-menu li a { display: block; padding: 12px 24px; color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.3s; }
        .nav-menu li a:hover, .nav-menu li a.active { background: rgba(232,180,184,0.2); color: #e8b4b8; border-left: 3px solid #e8b4b8; }
        .nav-menu li a.logout { color: #ef5350; margin-top: 20px; }
        .admin-content { margin-left: 260px; padding: 30px; width: 100%; }
        .admin-content h1 { margin-bottom: 10px; color: #333; }
        .admin-card { background: white; border-radius: 16px; padding: 24px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .admin-card h2 { margin-bottom: 20px; font-size: 1.2rem; color: #333; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap; }
        .form-field { flex: 1; min-width: 150px; }
        .form-field label { display: block; margin-bottom: 5px; font-size: 0.85rem; color: #666; }
        .form-field input { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; }
        .form-field input[type="file"] { padding: 6px; }
        .btn-primary { background: linear-gradient(135deg, #e8b4b8, #f4c3a3); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .btn-edit { background: #ffc107; color: #333; border: none; padding: 5px 12px; border-radius: 6px; cursor: pointer; margin-right: 5px; }
        .btn-danger-small { background: #dc3545; color: white; border: none; padding: 5px 12px; border-radius: 6px; cursor: pointer; }
        .btn-sm { background: #17a2b8; color: white; border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; }
        .table-wrapper { overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        .admin-table th { background: #f8f8f8; font-weight: 600; }
        .admin-table input { padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; width: 100px; }
        .product-preview-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .photo-list { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .photo-item { position: relative; display: inline-block; }
        .photo-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .photo-item .delete-photo { position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; cursor: pointer; }
        @media (max-width: 768px) { .admin-nav { width: 100%; position: relative; height: auto; } .admin-content { margin-left: 0; } }
    </style>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <div class="nav-brand">BloomOrder Admin</div>
            <ul class="nav-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_dates.php">Kelola Tanggal</a></li>
                <li><a href="manage_products.php" class="active">Kelola Produk</a></li>
                <li><a href="manage_colors.php">Kelola Warna</a></li>
                <li><a href="manage_orders.php">Pesanan</a></li>
                <li><a href="settings.php">Pengaturan</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
        
        <div class="admin-content">
            <h1>Kelola Jenis Buket</h1>
            
            <?php if ($message): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="admin-card">
                <h2>Tambah Produk Baru</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-field">
                            <label>Nama Produk</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-field">
                            <label>Foto Produk</label>
                            <input type="file" name="product_photo" accept="image/*" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label>Harga Small</label>
                            <input type="number" name="price_s" required>
                        </div>
                        <div class="form-field">
                            <label>Harga Medium</label>
                            <input type="number" name="price_m" required>
                        </div>
                        <div class="form-field">
                            <label>Harga Large</label>
                            <input type="number" name="price_l" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Warna Theme (hex)</label>
                        <input type="color" name="color" value="#ff6b9d">
                    </div>
                    <button type="submit" name="add_product" class="btn-primary" style="margin-top: 15px;">Tambah Produk</button>
                </form>
            </div>
            
            <div class="admin-card">
                <h2>Daftar Produk</h2>
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Foto</th><th>Nama</th><th>Small</th><th>Medium</th><th>Large</th><th>Gallery</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                            <tr><td colspan="7" style="text-align: center;">Belum ada produk</td></tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <form method="POST" enctype="multipart/form-data" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <td>
                                            <?php if (!empty($product['photo'])): ?>
                                                <img src="../<?php echo $product['photo']; ?>" class="product-preview-img">
                                            <?php else: ?>
                                                <span style="color:#ccc;">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>"></td>
                                        <td><input type="number" name="price_s" value="<?php echo $product['price_s']; ?>"></td>
                                        <td><input type="number" name="price_m" value="<?php echo $product['price_m']; ?>"></td>
                                        <td><input type="number" name="price_l" value="<?php echo $product['price_l']; ?>"></td>
                                        <td>
                                            <input type="file" name="product_photo" accept="image/*" style="width: 100px;">
                                        </td>
                                        <td>
                                            <button type="submit" name="edit_product" class="btn-edit">Edit</button>
                                            <button type="submit" name="delete_product" class="btn-danger-small" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                        </td>
                                    </form>
                                </tr>
                                <tr>
                                    <td colspan="7" style="padding-top: 0;">
                                        <div class="photo-list">
                                            <form method="POST" enctype="multipart/form-data" style="display: inline-flex; gap: 10px; align-items: center;">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <input type="file" name="additional_photo" accept="image/*" required style="padding: 4px;">
                                                <button type="submit" name="add_photo" class="btn-sm">+ Tambah Foto</button>
                                            </form>
                                            <?php if (!empty($product['photos'])): ?>
                                                <?php foreach ($product['photos'] as $idx => $photo): ?>
                                                <div class="photo-item">
                                                    <img src="../<?php echo $photo; ?>">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <input type="hidden" name="photo_index" value="<?php echo $idx; ?>">
                                                        <button type="submit" name="delete_photo" class="delete-photo" onclick="return confirm('Hapus foto ini?')">×</button>
                                                    </form>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>