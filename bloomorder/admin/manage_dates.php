<?php
require_once 'config.php';
requireLogin();

// Load data
$bookedDates = loadData('bookings.json');
$settings = loadData('settings.json');
$closedDays = isset($settings['closed_days']) ? $settings['closed_days'] : [0];

$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_date'])) {
        $newDate = $_POST['new_date'] ?? '';
        if ($newDate && !in_array($newDate, $bookedDates)) {
            $bookedDates[] = $newDate;
            saveData('bookings.json', $bookedDates);
            $message = 'Tanggal berhasil ditambahkan!';
        } else {
            $error = 'Tanggal sudah ada atau tidak valid!';
        }
    } elseif (isset($_POST['remove_date'])) {
        $dateToRemove = $_POST['remove_date'] ?? '';
        $bookedDates = array_values(array_filter($bookedDates, function($d) use ($dateToRemove) {
            return $d !== $dateToRemove;
        }));
        saveData('bookings.json', $bookedDates);
        $message = 'Tanggal berhasil dihapus!';
    } elseif (isset($_POST['update_closed_days'])) {
        $closed = isset($_POST['closed_days']) ? $_POST['closed_days'] : [];
        $settings['closed_days'] = $closed;
        saveData('settings.json', $settings);
        $closedDays = $closed;
        $message = 'Pengaturan hari libur berhasil diupdate!';
    }
}

// Generate calendar for next 60 days
$availableDates = [];
$startDate = new DateTime();
$endDate = new DateTime('+60 days');
for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
    $availableDates[] = $date->format('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tanggal - Admin BloomOrder</title>
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
        .inline-form { display: flex; gap: 10px; align-items: flex-end; }
        .inline-form input, .inline-form select { padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-primary { background: linear-gradient(135deg, #e8b4b8, #f4c3a3); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .btn-danger-small { background: #dc3545; color: white; border: none; padding: 5px 12px; border-radius: 6px; cursor: pointer; }
        .checkbox-group { display: flex; gap: 20px; margin-top: 10px; }
        .checkbox-group label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .date-list { display: flex; flex-wrap: wrap; gap: 10px; }
        .date-item { background: #f5f5f5; padding: 8px 12px; border-radius: 8px; display: flex; align-items: center; gap: 10px; }
        .calendar-preview { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 8px; max-height: 400px; overflow-y: auto; }
        .calendar-preview-item { background: #e8f5e9; padding: 8px; text-align: center; border-radius: 8px; font-size: 0.8rem; position: relative; }
        .calendar-preview-item.booked { background: #ffebee; color: #c62828; }
        .calendar-preview-item .badge { font-size: 0.6rem; background: #ef5350; color: white; padding: 2px 4px; border-radius: 4px; margin-left: 4px; }
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
                <li><a href="manage_dates.php" class="active">Kelola Tanggal</a></li>
                <li><a href="manage_products.php">Kelola Produk</a></li>
                <li><a href="manage_colors.php">Kelola Warna</a></li>
                <li><a href="manage_orders.php">Pesanan</a></li>
                <li><a href="settings.php">Pengaturan</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
        
        <div class="admin-content">
            <h1>Kelola Tanggal</h1>
            
            <?php if ($message): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="admin-card">
                <h2>Tambah Tanggal Full Booked</h2>
                <form method="POST" class="inline-form">
                    <input type="date" name="new_date" required>
                    <button type="submit" name="add_date" class="btn-primary">Tambah</button>
                </form>
            </div>
            
            <div class="admin-card">
                <h2>Hari Libur (Toko Tutup)</h2>
                <form method="POST">
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="closed_days[]" value="0" <?php echo in_array(0, $closedDays) ? 'checked' : ''; ?>> Minggu</label>
                        <label><input type="checkbox" name="closed_days[]" value="1" <?php echo in_array(1, $closedDays) ? 'checked' : ''; ?>> Senin</label>
                        <label><input type="checkbox" name="closed_days[]" value="2" <?php echo in_array(2, $closedDays) ? 'checked' : ''; ?>> Selasa</label>
                        <label><input type="checkbox" name="closed_days[]" value="3" <?php echo in_array(3, $closedDays) ? 'checked' : ''; ?>> Rabu</label>
                        <label><input type="checkbox" name="closed_days[]" value="4" <?php echo in_array(4, $closedDays) ? 'checked' : ''; ?>> Kamis</label>
                        <label><input type="checkbox" name="closed_days[]" value="5" <?php echo in_array(5, $closedDays) ? 'checked' : ''; ?>> Jumat</label>
                        <label><input type="checkbox" name="closed_days[]" value="6" <?php echo in_array(6, $closedDays) ? 'checked' : ''; ?>> Sabtu</label>
                    </div>
                    <button type="submit" name="update_closed_days" class="btn-primary" style="margin-top: 15px;">Simpan Pengaturan</button>
                </form>
            </div>
            
            <div class="admin-card">
                <h2>Daftar Tanggal Full Booked</h2>
                <?php if (empty($bookedDates)): ?>
                    <p>Tidak ada tanggal yang full booked</p>
                <?php else: ?>
                    <div class="date-list">
                        <?php foreach ($bookedDates as $date): ?>
                        <div class="date-item">
                            <span><?php echo date('d F Y', strtotime($date)); ?></span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="remove_date" value="<?php echo $date; ?>">
                                <button type="submit" name="remove_date" class="btn-danger-small">Hapus</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="admin-card">
                <h2>Preview Kalender (60 hari ke depan)</h2>
                <div class="calendar-preview">
                    <?php foreach ($availableDates as $date): ?>
                    <div class="calendar-preview-item <?php echo in_array($date, $bookedDates) ? 'booked' : ''; ?>">
                        <?php echo date('d M', strtotime($date)); ?>
                        <?php if (in_array($date, $bookedDates)): ?>
                            <span class="badge">Full</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>