<?php
require_once 'config.php';
requireLogin();

$message = '';
$error = '';

// Load settings
$settings = loadData('settings.json');
if (!is_array($settings)) {
    $settings = [];
}

// Set default values if not exist
if (!isset($settings['whatsapp_number'])) $settings['whatsapp_number'] = '6281234567890';
if (!isset($settings['store_name'])) $settings['store_name'] = 'BloomOrder';
if (!isset($settings['store_address'])) $settings['store_address'] = '';
if (!isset($settings['closed_days'])) $settings['closed_days'] = [0];
if (!isset($settings['bank_info'])) $settings['bank_info'] = '';
if (!isset($settings['shipping_cost'])) $settings['shipping_cost'] = 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        $settings['whatsapp_number'] = $_POST['whatsapp_number'] ?? '6281234567890';
        $settings['store_name'] = $_POST['store_name'] ?? 'BloomOrder';
        $settings['store_address'] = $_POST['store_address'] ?? '';
        $settings['bank_info'] = $_POST['bank_info'] ?? '';
        $settings['shipping_cost'] = intval($_POST['shipping_cost'] ?? 0);
        $settings['closed_days'] = $_POST['closed_days'] ?? [];
        
        saveData('settings.json', $settings);
        $message = 'Pengaturan berhasil disimpan!';
        
        // Handle password change
        $new_password = $_POST['new_password'] ?? '';
        if (!empty($new_password) && strlen($new_password) >= 6) {
            $configContent = file_get_contents(__DIR__ . '/config.php');
            $configContent = preg_replace(
                "/define\('ADMIN_PASSWORD', '.*'\);/",
                "define('ADMIN_PASSWORD', '" . addslashes($new_password) . "');",
                $configContent
            );
            file_put_contents(__DIR__ . '/config.php', $configContent);
            $message .= ' Password juga telah diupdate!';
        }
    }
    
    if (isset($_POST['reset_defaults'])) {
        $settings = [
            'whatsapp_number' => '6281234567890',
            'store_name' => 'BloomOrder',
            'store_address' => '',
            'closed_days' => [0],
            'bank_info' => '',
            'shipping_cost' => 0
        ];
        saveData('settings.json', $settings);
        $message = 'Pengaturan berhasil direset ke default!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin BloomOrder</title>
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
        .admin-card h3 { margin-bottom: 15px; font-size: 1rem; color: #e8b4b8; margin-top: 10px; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #333; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #e8b4b8; }
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; }
        .form-row .form-group { flex: 1; }
        .checkbox-group { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 5px; }
        .checkbox-group label { display: flex; align-items: center; gap: 5px; font-weight: normal; cursor: pointer; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .btn-primary { background: linear-gradient(135deg, #e8b4b8, #f4c3a3); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .btn-secondary { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .info-text { font-size: 0.75rem; color: #999; margin-top: 5px; }
        .preview-wa { background: #f0f0f0; padding: 15px; border-radius: 10px; font-family: monospace; font-size: 11px; white-space: pre-wrap; margin-top: 15px; max-height: 300px; overflow-y: auto; }
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
                <li><a href="manage_colors.php">Kelola Warna</a></li>
                <li><a href="manage_orders.php">Pesanan</a></li>
                <li><a href="settings.php" class="active">Pengaturan</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
        
        <div class="admin-content">
            <h1>Pengaturan Toko</h1>
            
            <?php if ($message): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="admin-card">
                <form method="POST">
                    <h3>📱 Informasi Toko</h3>
                    <div class="form-group">
                        <label>Nama Toko</label>
                        <input type="text" name="store_name" value="<?php echo htmlspecialchars($settings['store_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Toko</label>
                        <textarea name="store_address" rows="2"><?php echo htmlspecialchars($settings['store_address']); ?></textarea>
                    </div>
                    
                    <h3>📞 WhatsApp & Pengiriman</h3>
                    <div class="form-group">
                        <label>Nomor WhatsApp (untuk menerima pesanan)</label>
                        <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number']); ?>" required>
                        <div class="info-text">Format: 628xxxxxxxxxx (tanpa tanda + atau spasi)</div>
                    </div>
                    <div class="form-group">
                        <label>Biaya Ongkir Default (Rp)</label>
                        <input type="number" name="shipping_cost" value="<?php echo $settings['shipping_cost']; ?>">
                        <div class="info-text">Kosongkan atau isi 0 jika ongkir menyesuaikan lokasi</div>
                    </div>
                    
                    <h3>🕐 Jam Operasional</h3>
                    <div class="form-group">
                        <label>Hari Libur (Toko Tutup)</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="closed_days[]" value="0" <?php echo in_array(0, $settings['closed_days']) ? 'checked' : ''; ?>> Minggu</label>
                            <label><input type="checkbox" name="closed_days[]" value="1" <?php echo in_array(1, $settings['closed_days']) ? 'checked' : ''; ?>> Senin</label>
                            <label><input type="checkbox" name="closed_days[]" value="2" <?php echo in_array(2, $settings['closed_days']) ? 'checked' : ''; ?>> Selasa</label>
                            <label><input type="checkbox" name="closed_days[]" value="3" <?php echo in_array(3, $settings['closed_days']) ? 'checked' : ''; ?>> Rabu</label>
                            <label><input type="checkbox" name="closed_days[]" value="4" <?php echo in_array(4, $settings['closed_days']) ? 'checked' : ''; ?>> Kamis</label>
                            <label><input type="checkbox" name="closed_days[]" value="5" <?php echo in_array(5, $settings['closed_days']) ? 'checked' : ''; ?>> Jumat</label>
                            <label><input type="checkbox" name="closed_days[]" value="6" <?php echo in_array(6, $settings['closed_days']) ? 'checked' : ''; ?>> Sabtu</label>
                        </div>
                    </div>
                    
                    <h3>💰 Informasi Pembayaran</h3>
                    <div class="form-group">
                        <label>Informasi Rekening/Bank</label>
                        <textarea name="bank_info" rows="4" placeholder="Contoh:&#10;Bank BCA - 1234567890 a.n BloomOrder&#10;Bank Mandiri - 0987654321 a.n BloomOrder"><?php echo htmlspecialchars($settings['bank_info']); ?></textarea>
                        <div class="info-text">Informasi ini akan dikirim ke customer via WhatsApp</div>
                    </div>
                    
                    <h3>🔒 Keamanan Admin</h3>
                    <div class="form-group">
                        <label>Username Admin</label>
                        <input type="text" value="admin" disabled>
                        <div class="info-text">Username tidak dapat diubah (admin)</div>
                    </div>
                    <div class="form-group">
                        <label>Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="new_password" placeholder="Masukkan password baru (minimal 6 karakter)">
                        <div class="info-text">Kosongkan jika tidak ingin mengubah password</div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="save_settings" class="btn-primary">Simpan Pengaturan</button>
                        <button type="submit" name="reset_defaults" class="btn-secondary" onclick="return confirm('Reset semua pengaturan ke default?')">Reset ke Default</button>
                    </div>
                </form>
            </div>
            
            <div class="admin-card">
                <h2>📱 Preview Pesan WhatsApp</h2>
                <div class="preview-wa">
                    *🌿 BLOOMORDER - PESANAN BARU* 🌿

*📋 DETAIL PESANAN:*
📅 Tanggal Pengambilan: 12 April 2026
💐 Jenis Buket: Buket Bunga Asli (Medium)
💰 Harga: Rp 250,000
🎨 Warna Dominan: Pastel Pink

*✉️ DATA PENGIRIMAN:*
👤 Pengirim: [Nama Customer]
🎁 Penerima: [Nama Penerima]

*⏰ Langkah Selanjutnya:*
1. Mohon konfirmasi ketersediaan
2. Info ongkir (sesuai lokasi)
3. Instruksi pembayaran akan kami kirimkan

Terima kasih sudah memesan di BloomOrder! 💐
                </div>
            </div>
        </div>
    </div>
</body>
</html>