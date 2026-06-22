<?php
require_once 'config.php';
requireLogin();

$orders = loadData('orders.json');
$orders = array_reverse($orders); // Terbaru di atas

// Filter
$search = $_GET['search'] ?? '';
if ($search) {
    $filtered = [];
    foreach ($orders as $order) {
        if (stripos($order['nama_pengirim'] ?? '', $search) !== false ||
            stripos($order['nama_penerima'] ?? '', $search) !== false) {
            $filtered[] = $order;
        }
    }
    $orders = $filtered;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan - Admin BloomOrder</title>
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
        .search-bar { margin-bottom: 20px; }
        .search-bar form { display: flex; gap: 10px; }
        .search-bar input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-primary { background: linear-gradient(135deg, #e8b4b8, #f4c3a3); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .btn-secondary { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; }
        .table-wrapper { overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f8f8; font-weight: 600; }
        .admin-table tr:hover { background: #fafafa; }
        .btn-view { background: #e8b4b8; color: white; border: none; padding: 5px 12px; border-radius: 6px; cursor: pointer; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 25px; width: 90%; max-width: 500px; border-radius: 16px; position: relative; }
        .close { position: absolute; right: 20px; top: 15px; font-size: 28px; cursor: pointer; color: #999; }
        .close:hover { color: #333; }
        .modal-content h2 { margin-bottom: 20px; color: #e8b4b8; }
        .modal-detail { margin-bottom: 10px; padding: 8px 0; border-bottom: 1px solid #eee; }
        .modal-detail strong { display: inline-block; width: 120px; color: #666; }
        @media (max-width: 768px) { 
            .admin-nav { width: 100%; position: relative; height: auto; } 
            .admin-content { margin-left: 0; }
            .modal-content { margin: 20% auto; width: 95%; }
        }
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
                <li><a href="manage_orders.php" class="active">Pesanan</a></li>
                <li><a href="settings.php">Pengaturan</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </nav>
        
        <div class="admin-content">
            <h1>Daftar Pesanan</h1>
            
            <div class="search-bar">
                <form method="GET">
                    <input type="text" name="search" placeholder="Cari nama pengirim/penerima..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-primary">Cari</button>
                    <?php if ($search): ?>
                        <a href="manage_orders.php" class="btn-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="admin-card">
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Tanggal Order</th>
                                <th>Tanggal Ambil</th>
                                <th>Jenis</th>
                                <th>Ukuran</th>
                                <th>Harga</th>
                                <th>Pengirim</th>
                                <th>Penerima</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Belum ada pesanan</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $index => $order): ?>
                                <tr>
                                    <td><?php echo isset($order['tanggal_order']) ? date('d/m/Y H:i', strtotime($order['tanggal_order'])) : '-'; ?></td>
                                    <td><?php echo isset($order['tanggal']) ? date('d/m/Y', strtotime($order['tanggal'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($order['jenis_buket'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($order['ukuran'] ?? '-'); ?></td>
                                    <td>Rp <?php echo number_format($order['harga'] ?? 0, 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($order['nama_pengirim'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($order['nama_penerima'] ?? '-'); ?></td>
                                    <td>
                                        <button class="btn-view" onclick="showDetail(<?php echo htmlspecialchars(json_encode($order)); ?>)">Detail</button>
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
    
    <!-- Modal Detail -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>📋 Detail Pesanan</h2>
            <div id="modalBody"></div>
        </div>
    </div>
    
    <script>
        function showDetail(order) {
            const modal = document.getElementById('detailModal');
            const modalBody = document.getElementById('modalBody');
            
            // Format tanggal
            let tanggalAmbil = '-';
            if (order.tanggal) {
                const d = new Date(order.tanggal);
                tanggalAmbil = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            }
            
            let tanggalOrder = '-';
            if (order.tanggal_order) {
                const d = new Date(order.tanggal_order);
                tanggalOrder = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            }
            
            modalBody.innerHTML = `
                <div class="modal-detail"><strong>📅 Tanggal Order:</strong> ${tanggalOrder}</div>
                <div class="modal-detail"><strong>📅 Tanggal Ambil:</strong> ${tanggalAmbil}</div>
                <div class="modal-detail"><strong>💐 Jenis Buket:</strong> ${order.jenis_buket || '-'}</div>
                <div class="modal-detail"><strong>📏 Ukuran:</strong> ${order.ukuran || '-'}</div>
                <div class="modal-detail"><strong>💰 Harga:</strong> Rp ${new Intl.NumberFormat('id-ID').format(order.harga || 0)}</div>
                <div class="modal-detail"><strong>🎨 Warna:</strong> ${order.warna || '-'}</div>
                <div class="modal-detail"><strong>👤 Pengirim:</strong> ${order.nama_pengirim || '-'}</div>
                <div class="modal-detail"><strong>🎁 Penerima:</strong> ${order.nama_penerima || '-'}</div>
                <div class="modal-detail"><strong>💌 Ucapan:</strong> ${order.ucapan || '-'}</div>
                <div class="modal-detail"><strong>📝 Catatan:</strong> ${order.catatan_tambahan || '-'}</div>
            `;
            modal.style.display = 'block';
        }
        
        // Close modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('detailModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('detailModal')) {
                document.getElementById('detailModal').style.display = 'none';
            }
        }
    </script>
</body>
</html>