<?php
session_start();

// Load settings
$settingsFile = __DIR__ . '/data/settings.json';
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
} else {
    $settings = [];
}

$whatsappNumber = isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '6281234567890';
$storeName = isset($settings['store_name']) ? $settings['store_name'] : 'BloomOrder';
$shippingCost = isset($settings['shipping_cost']) ? $settings['shipping_cost'] : 0;
$bankInfo = isset($settings['bank_info']) ? $settings['bank_info'] : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Ambil data dari form
$tanggal = $_POST['tanggal'] ?? '';
$jenis_buket = $_POST['jenis_buket'] ?? '';
$ukuran = $_POST['ukuran'] ?? '';
$harga = intval($_POST['harga'] ?? 0);
$warna = $_POST['warna'] ?? '';
$nama_pengirim = htmlspecialchars($_POST['nama_pengirim'] ?? '');
$nama_penerima = htmlspecialchars($_POST['nama_penerima'] ?? '');
$ucapan = htmlspecialchars($_POST['ucapan'] ?? '');
$catatan_tambahan = htmlspecialchars($_POST['catatan_tambahan'] ?? '');

// Validasi
if (empty($tanggal) || empty($jenis_buket) || empty($ukuran) || empty($nama_pengirim) || empty($nama_penerima)) {
    header('Location: index.php?error=missing_fields');
    exit;
}

// Format tanggal
$tanggal_formatted = date('d F Y', strtotime($tanggal));

// Format pesan WhatsApp
$message = "*🌿 " . strtoupper($storeName) . " - PESANAN BARU* 🌿\n\n";
$message .= "*📋 DETAIL PESANAN:*\n";
$message .= "📅 Tanggal Pengambilan: {$tanggal_formatted}\n";
$message .= "💐 Jenis Buket: {$jenis_buket} ({$ukuran})\n";
$message .= "💰 Harga: Rp " . number_format($harga, 0, ',', '.') . "\n";
$message .= "🎨 Warna Dominan: {$warna}\n\n";

$message .= "*✉️ DATA PENGIRIMAN:*\n";
$message .= "👤 Pengirim: {$nama_pengirim}\n";
$message .= "🎁 Penerima: {$nama_penerima}\n\n";

if (!empty($ucapan)) {
    $message .= "*💌 ISI KARTU UCAPAN:*\n";
    $message .= "_{$ucapan}_\n\n";
}

if (!empty($catatan_tambahan)) {
    $message .= "*📝 CATATAN TAMBAHAN:*\n";
    $message .= "{$catatan_tambahan}\n\n";
}

if ($shippingCost > 0) {
    $message .= "*🚚 BIAYA PENGIRIMAN:*\n";
    $message .= "Ongkir: Rp " . number_format($shippingCost, 0, ',', '.') . "\n\n";
}

if (!empty($bankInfo)) {
    $message .= "*💰 INFORMASI PEMBAYARAN:*\n";
    $message .= "{$bankInfo}\n\n";
}

$message .= "*⏰ Langkah Selanjutnya:*\n";
$message .= "1. Mohon konfirmasi ketersediaan\n";
$message .= "2. Konfirmasi total biaya (termasuk ongkir jika ada)\n";
$message .= "3. Lakukan pembayaran ke rekening di atas\n";
$message .= "4. Konfirmasi pembayaran setelah transfer\n\n";

$message .= "Terima kasih sudah memesan di {$storeName}! 💐";

// Simpan ke database (orders.json)
$ordersFile = __DIR__ . '/data/orders.json';
$existingOrders = [];
if (file_exists($ordersFile)) {
    $existingOrders = json_decode(file_get_contents($ordersFile), true);
    if (!is_array($existingOrders)) $existingOrders = [];
}

$orderData = [
    'id' => uniqid(),
    'tanggal_order' => date('Y-m-d H:i:s'),
    'tanggal' => $tanggal,
    'jenis_buket' => $jenis_buket,
    'ukuran' => $ukuran,
    'harga' => $harga,
    'warna' => $warna,
    'nama_pengirim' => $nama_pengirim,
    'nama_penerima' => $nama_penerima,
    'ucapan' => $ucapan,
    'catatan_tambahan' => $catatan_tambahan
];

$existingOrders[] = $orderData;
file_put_contents($ordersFile, json_encode($existingOrders, JSON_PRETTY_PRINT));

// Encode untuk URL WhatsApp
$encodedMessage = urlencode($message);
$whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";

// Redirect ke WhatsApp
header("Location: {$whatsappUrl}");
exit;
?>