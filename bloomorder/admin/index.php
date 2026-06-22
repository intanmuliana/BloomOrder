<?php
require_once 'config.php';
requireLogin();

// Load data
$products = loadData('products.json');
$colors = loadData('colors.json');
$bookings = loadData('bookings.json');
$orders = loadData('orders.json');

if (!is_array($products)) $products = [];
if (!is_array($colors)) $colors = [];
if (!is_array($bookings)) $bookings = [];
if (!is_array($orders)) $orders = [];

$stats = [
    'products' => count($products),
    'colors' => count($colors),
    'booked_dates' => count($bookings),
    'total_orders' => count($orders)
];

// Get recent orders
$recentOrders = array_slice(array_reverse($orders), 0, 5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin BloomOrder</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            color: #333;
        }

        /* Admin Container */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .admin-nav {
            width: 280px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .nav-brand {
            padding: 24px 20px;
            font-size: 1.4rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: #e8b4b8;
            text-align: center;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .nav-menu li a:hover {
            background: rgba(232,180,184,0.15);
            color: #e8b4b8;
            padding-left: 28px;
        }

        .nav-menu li a.active {
            background: rgba(232,180,184,0.2);
            color: #e8b4b8;
            border-left: 3px solid #e8b4b8;
        }

        .nav-menu li a.logout {
            color: #ef5350;
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
        }

        /* Main Content */
        .admin-content {
            margin-left: 280px;
            padding: 30px;
            width: 100%;
        }

        /* Header */
        .content-header {
            margin-bottom: 30px;
        }

        .content-header h1 {
            font-size: 1.8rem;
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .content-header p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 3rem;
        }

        .stat-info h3 {
            font-size: 2rem;
            color: #e8b4b8;
            font-weight: 700;
        }

        .stat-info p {
            color: #666;
            font-size: 0.85rem;
            margin-top: 4px;
        }

        /* Recent Orders Card */
        .recent-orders {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .recent-orders h2 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #1a1a2e;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        .admin-table tr:hover {
            background: #fafafa;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        /* Footer */
        .admin-footer {
            margin-top: 30px;
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-nav {
                width: 100%;
                position: relative;
                height: auto;
            }
            .admin-content {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <div class="nav-brand">🌸 BloomOrder</div>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">📊 Dashboard</a></li>
                <li><a href="manage_dates.php">📅 Kelola Tanggal</a></li>
                <li><a href="manage_products.php">📦 Kelola Produk</a></li>
                <li><a href="manage_colors.php">🎨 Kelola Warna</a></li>
                <li><a href="manage_orders.php">📋 Pesanan</a></li>
                <li><a href="settings.php">⚙️ Pengaturan</a></li>
                <li><a href="logout.php" class="logout">🚪 Logout</a></li>
            </ul>
        </nav>
        
        <div class="admin-content">
            <div class="content-header">
                <h1>Dashboard</h1>
                <p>Selamat datang di panel admin BloomOrder</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['products']; ?></h3>
                        <p>Jenis Buket</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎨</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['colors']; ?></h3>
                        <p>Pilihan Warna</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['booked_dates']; ?></h3>
                        <p>Tanggal Penuh</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Total Pesanan</p>
                    </div>
                </div>
            </div>
            
            <div class="recent-orders">
                <h2>📋 Pesanan Terbaru</h2>
                <div class="table-wrapper">
                    <?php if (empty($recentOrders)): ?>
                        <div class="empty-state">
                            <p>Belum ada pesanan</p>
                            <p style="font-size: 12px; margin-top: 8px;">Pesanan akan muncul setelah customer mengirim form</p>
                        </div>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Tanggal Order</th>
                                    <th>Tanggal Ambil</th>
                                    <th>Jenis</th>
                                    <th>Ukuran</th>
                                    <th>Harga</th>
                                    <th>Penerima</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><?php echo isset($order['tanggal_order']) ? date('d/m/Y H:i', strtotime($order['tanggal_order'])) : '-'; ?></td>
                                    <td><?php echo isset($order['tanggal']) ? date('d/m/Y', strtotime($order['tanggal'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($order['jenis_buket'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($order['ukuran'] ?? '-'); ?></td>
                                    <td>Rp <?php echo number_format($order['harga'] ?? 0, 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($order['nama_penerima'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> BloomOrder - Sistem Pemesanan Buket Online</p>
            </div>
        </div>
    </div>
</body>
</html>