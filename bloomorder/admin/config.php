<?php
// Hapus session_start() yang duplikat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Admin
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'imbuket123');

// Cek login
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Path data
$dataPath = __DIR__ . '/../data/';

// Load data function
function loadData($filename) {
    global $dataPath;
    $file = $dataPath . $filename;
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
    return [];
}

function saveData($filename, $data) {
    global $dataPath;
    $file = $dataPath . $filename;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Ensure data directory exists
if (!is_dir($dataPath)) {
    mkdir($dataPath, 0777, true);
}

// Initialize default data files if they don't exist
function initDataFiles() {
    global $dataPath;
    
    // Products default
    $productsFile = $dataPath . 'products.json';
    if (!file_exists($productsFile)) {
        $defaultProducts = [
            ['id' => 'fresh', 'name' => 'Buket Bunga Asli', 'price_s' => 150000, 'price_m' => 250000, 'price_l' => 400000, 'photo' => '', 'photos' => [], 'color' => '#ff6b9d'],
            ['id' => 'paper', 'name' => 'Buket Bunga Kertas', 'price_s' => 100000, 'price_m' => 175000, 'price_l' => 275000, 'photo' => '', 'photos' => [], 'color' => '#ffb347'],
            ['id' => 'money', 'name' => 'Buket Uang', 'price_s' => 200000, 'price_m' => 350000, 'price_l' => 550000, 'photo' => '', 'photos' => [], 'color' => '#4caf50'],
            ['id' => 'snack', 'name' => 'Buket Snack', 'price_s' => 80000, 'price_m' => 150000, 'price_l' => 250000, 'photo' => '', 'photos' => [], 'color' => '#ff9800']
        ];
        file_put_contents($productsFile, json_encode($defaultProducts, JSON_PRETTY_PRINT));
    }
    
    // Colors default
    $colorsFile = $dataPath . 'colors.json';
    if (!file_exists($colorsFile)) {
        $defaultColors = [
            ['name' => 'Pastel Pink', 'code' => '#f8bbd0', 'value' => 'Pastel Pink'],
            ['name' => 'Soft Blue', 'code' => '#bbdef5', 'value' => 'Soft Blue'],
            ['name' => 'White & Gold', 'code' => '#fff9c4', 'value' => 'White & Gold'],
            ['name' => 'Bold Red', 'code' => '#ef5350', 'value' => 'Bold Red'],
            ['name' => 'Purple Lavender', 'code' => '#ce93d8', 'value' => 'Purple Lavender'],
            ['name' => 'Sunset Orange', 'code' => '#ffab91', 'value' => 'Sunset Orange'],
            ['name' => 'Forest Green', 'code' => '#a5d6a7', 'value' => 'Forest Green'],
            ['name' => 'All White', 'code' => '#ffffff', 'value' => 'All White']
        ];
        file_put_contents($colorsFile, json_encode($defaultColors, JSON_PRETTY_PRINT));
    }
    
    // Bookings default
    $bookingsFile = $dataPath . 'bookings.json';
    if (!file_exists($bookingsFile)) {
        file_put_contents($bookingsFile, json_encode([], JSON_PRETTY_PRINT));
    }
    
    // Orders default
    $ordersFile = $dataPath . 'orders.json';
    if (!file_exists($ordersFile)) {
        file_put_contents($ordersFile, json_encode([], JSON_PRETTY_PRINT));
    }
    
    // Settings default
    $settingsFile = $dataPath . 'settings.json';
    if (!file_exists($settingsFile)) {
        $defaultSettings = [
            'whatsapp_number' => '6281234567890',
            'store_name' => 'BloomOrder',
            'store_address' => 'Jl. Contoh No. 123, Kota Contoh',
            'closed_days' => [0],
            'bank_info' => 'Bank BCA - 1234567890 a.n BloomOrder',
            'shipping_cost' => 0
        ];
        file_put_contents($settingsFile, json_encode($defaultSettings, JSON_PRETTY_PRINT));
    }
}

// Run initialization
initDataFiles();
?>