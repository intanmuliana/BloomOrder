<?php
require_once 'config.php';
requireLogin();

$colors = loadData('colors.json');
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_color'])) {
        $newColor = [
            'name' => $_POST['name'] ?? '',
            'code' => $_POST['code'] ?? '#ffffff',
            'value' => $_POST['name'] ?? ''
        ];
        if (!empty($newColor['name'])) {
            $colors[] = $newColor;
            saveData('colors.json', $colors);
            $message = 'Warna berhasil ditambahkan!';
        } else {
            $error = 'Nama warna tidak boleh kosong!';
        }
    } elseif (isset($_POST['edit_color'])) {
        $index = intval($_POST['color_index']);
        if (isset($colors[$index])) {
            $colors[$index]['name'] = $_POST['name'];
            $colors[$index]['code'] = $_POST['code'];
            $colors[$index]['value'] = $_POST['name'];
            saveData('colors.json', $colors);
            $message = 'Warna berhasil diupdate!';
        }
    } elseif (isset($_POST['delete_color'])) {
        $index = intval($_POST['color_index']);
        if (isset($colors[$index])) {
            array_splice($colors, $index, 1);
            saveData('colors.json', $colors);
            $message = 'Warna berhasil dihapus!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Warna - Admin BloomOrder</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        .admin-container { display: flex; min-height: 100vh; }
        .admin-nav { width: 260px; background: #1a1a2e; color: white; position: fixed; height: 100vh; }
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
        .inline-form { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
        .inline-form input { padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-primary { background: linear-gradient(135deg, #e8b4b8, #f4c3a3); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .btn-edit { background: #ffc107; color: #333; border: none; padding: 5px 12px; border-radius: 6px; cursor: pointer; margin-right: 5px; }
        .btn-danger-small { background: #dc3545; color: white; border: none; padding: 5px 12px; border-radius: 6px; cursor: pointer; }
        .color-list-admin { display: flex; flex-direction: column; gap: 10px; }
        .color-item-admin { padding: 10px; border-radius: 8px; margin-bottom: 10px; }
        .color-item-admin form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .color-item-admin input[type="text"] { padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; }
        .preview-colors { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .preview-color { padding: 20px; border-radius: 8px; text-align: center; min-width: 100px; font-weight: 500; color: #333; text-shadow: 0 0 2px white; }
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
                <li><a href="manage_products.php">Kelola Produk</a></li>
                <li><a href="manage_colors.php" class="active">Kelola Warna</a></li>
                <li><a href="manage_orders.php">Pesanan</a></li>
                <li><a href="settings.php">Pengaturan</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
        
        <div class="admin-content">
            <h1>Kelola Pilihan Warna</h1>
            
            <?php if ($message): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="admin-card">
                <h2>Tambah Warna Baru</h2>
                <form method="POST" class="inline-form">
                    <input type="text" name="name" placeholder="Nama Warna" required>
                    <input type="color" name="code" value="#e8b4b8" required>
                    <button type="submit" name="add_color" class="btn-primary">Tambah</button>
                </form>
            </div>
            
            <div class="admin-card">
                <h2>Daftar Warna Tersedia</h2>
                <?php if (empty($colors)): ?>
                    <p>Belum ada warna</p>
                <?php else: ?>
                    <div class="color-list-admin">
                        <?php foreach ($colors as $index => $color): ?>
                        <div class="color-item-admin" style="background: <?php echo $color['code']; ?>20; border: 1px solid <?php echo $color['code']; ?>;">
                            <form method="POST">
                                <input type="hidden" name="color_index" value="<?php echo $index; ?>">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($color['name']); ?>">
                                <input type="color" name="code" value="<?php echo $color['code']; ?>">
                                <button type="submit" name="edit_color" class="btn-edit">Edit</button>
                                <button type="submit" name="delete_color" class="btn-danger-small" onclick="return confirm('Yakin hapus?')">Hapus</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="admin-card">
                <h2>Preview Warna</h2>
                <div class="preview-colors">
                    <?php foreach ($colors as $color): ?>
                    <div class="preview-color" style="background: <?php echo $color['code']; ?>; color: <?php echo strpos($color['code'], '#ffffff') !== false ? '#333' : 'white'; ?>;">
                        <?php echo $color['name']; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>