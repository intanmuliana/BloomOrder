<?php
// Load semua data dari file JSON
$dataPath = __DIR__ . '/data/';

// Load products
$productsFile = $dataPath . 'products.json';
if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true);
    if (!is_array($products)) $products = [];
} else {
    // Default products if file not exists
    $products = [
        ['id' => 'fresh', 'name' => 'Buket Bunga Asli', 'price_s' => 150000, 'price_m' => 250000, 'price_l' => 400000, 'photo' => '', 'photos' => [], 'color' => '#ff6b9d'],
        ['id' => 'paper', 'name' => 'Buket Bunga Kertas', 'price_s' => 100000, 'price_m' => 175000, 'price_l' => 275000, 'photo' => '', 'photos' => [], 'color' => '#ffb347'],
        ['id' => 'money', 'name' => 'Buket Uang', 'price_s' => 200000, 'price_m' => 350000, 'price_l' => 550000, 'photo' => '', 'photos' => [], 'color' => '#4caf50'],
        ['id' => 'snack', 'name' => 'Buket Snack', 'price_s' => 80000, 'price_m' => 150000, 'price_l' => 250000, 'photo' => '', 'photos' => [], 'color' => '#ff9800']
    ];
}

// Load colors
$colorsFile = $dataPath . 'colors.json';
if (file_exists($colorsFile)) {
    $colors = json_decode(file_get_contents($colorsFile), true);
    if (!is_array($colors)) $colors = [];
} else {
    $colors = [
        ['name' => 'Pastel Pink', 'code' => '#f8bbd0', 'value' => 'Pastel Pink'],
        ['name' => 'Soft Blue', 'code' => '#bbdef5', 'value' => 'Soft Blue'],
        ['name' => 'White & Gold', 'code' => '#fff9c4', 'value' => 'White & Gold'],
        ['name' => 'Bold Red', 'code' => '#ef5350', 'value' => 'Bold Red'],
        ['name' => 'Purple Lavender', 'code' => '#ce93d8', 'value' => 'Purple Lavender'],
        ['name' => 'Sunset Orange', 'code' => '#ffab91', 'value' => 'Sunset Orange'],
        ['name' => 'Forest Green', 'code' => '#a5d6a7', 'value' => 'Forest Green'],
        ['name' => 'All White', 'code' => '#ffffff', 'value' => 'All White']
    ];
}

// Load booked dates
$bookingsFile = $dataPath . 'bookings.json';
if (file_exists($bookingsFile)) {
    $bookedDates = json_decode(file_get_contents($bookingsFile), true);
    if (!is_array($bookedDates)) $bookedDates = [];
} else {
    $bookedDates = [];
}

// Load settings
$settingsFile = $dataPath . 'settings.json';
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    if (!is_array($settings)) $settings = [];
} else {
    $settings = [];
}

// Get closed days from settings
$closedDays = isset($settings['closed_days']) ? $settings['closed_days'] : [0];
$whatsappNumber = isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '6281234567890';
$storeName = isset($settings['store_name']) ? $settings['store_name'] : 'BloomOrder';

// Generate daftar tanggal untuk 60 hari ke depan
$availableDates = [];
$startDate = new DateTime();
$endDate = new DateTime('+60 days');

for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
    $dayOfWeek = (int)$date->format('w');
    $dateString = $date->format('Y-m-d');
    
    $isBooked = in_array($dateString, $bookedDates);
    $isClosed = in_array($dayOfWeek, $closedDays);
    
    $availableDates[$dateString] = [
        'day' => $date->format('d'),
        'monthShort' => $date->format('M'),
        'dayNameShort' => $date->format('D'),
        'disabled' => $isBooked || $isClosed,
        'booked' => $isBooked,
        'closed' => $isClosed
    ];
}

// Function to format number to Rupiah
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Function to get product photos
function getProductPhotos($product) {
    if (isset($product['photos']) && is_array($product['photos']) && count($product['photos']) > 0) {
        return $product['photos'];
    } elseif (!empty($product['photo'])) {
        return [$product['photo']];
    }
    return ['assets/default-product.jpg'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo htmlspecialchars($storeName); ?> - Pesan Buket Online</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Product Carousel Styles */
        .product-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border: 2px solid #e5e5e5;
            border-radius: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
        }
        .product-item.selected {
            border-color: #e8b4b8;
            background: #fef6f9;
            box-shadow: 0 4px 15px rgba(232,180,184,0.2);
        }
        .product-carousel {
            width: 140px;
            flex-shrink: 0;
        }
        .carousel-container {
            position: relative;
            width: 100%;
            height: 140px;
            overflow: hidden;
            border-radius: 16px;
            background: #f5f5f5;
        }
        .carousel-slides {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.3s ease;
        }
        .carousel-slide {
            min-width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: all 0.2s;
        }
        .carousel-btn:hover {
            background: rgba(0,0,0,0.8);
        }
        .carousel-btn.prev {
            left: 5px;
        }
        .carousel-btn.next {
            right: 5px;
        }
        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 10px;
        }
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ccc;
            cursor: pointer;
            transition: all 0.3s;
        }
        .dot.active {
            background: #e8b4b8;
            width: 20px;
            border-radius: 4px;
        }
        .product-info {
            flex: 1;
        }
        .product-info h4 {
            font-size: 1.1rem;
            margin-bottom: 12px;
            color: #333;
        }
        .size-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .size-opt {
            flex: 1;
            min-width: 80px;
            cursor: pointer;
        }
        .size-opt input {
            display: none;
        }
        .size-opt span {
            display: block;
            padding: 10px 8px;
            background: #f5f5f5;
            border-radius: 12px;
            font-size: 0.8rem;
            text-align: center;
            transition: all 0.2s;
        }
        .size-opt input:checked + span {
            background: linear-gradient(135deg, #e8b4b8, #f4c3a3);
            color: white;
        }
        .size-opt strong {
            display: block;
            font-size: 0.9rem;
            margin-top: 4px;
        }
        @media (max-width: 600px) {
            .product-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .product-carousel {
                width: 200px;
            }
            .carousel-container {
                height: 180px;
            }
            .size-group {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- Hero Section -->
<div class="hero-wrap">
    <div class="hero-simple">
        <h1><?php echo htmlspecialchars($storeName); ?></h1>
        <p>Pesan buket custom dengan mudah dan cepat</p>
        <button class="cta-button" id="startOrderBtn">Mulai Buat Pesanan →</button>
    </div>
</div>

<!-- Container Utama -->
<div class="main-container">
    <div class="main-wrapper">
        
        <!-- Form Section -->
        <div class="form-wrapper" id="orderForm">
            <div class="form-card">
                <div class="form-title">
                    <h2>Buat Pesanan Buket</h2>
                    <p>Isi formulir di bawah ini</p>
                </div>

                <form id="bloomOrderForm" method="POST" action="process.php">
                    <!-- STEP 1: Pilih Tanggal -->
                    <div class="form-step active" data-step="1">
                        <div class="step-indicator">
                            <span class="step-num">1</span>
                            <h3>Pilih Tanggal Pengambilan</h3>
                        </div>
                        <div class="calendar-grid">
                            <?php foreach ($availableDates as $date => $info): ?>
                            <div class="calendar-card <?php echo $info['disabled'] ? 'disabled' : ''; ?>" 
                                 data-date="<?php echo $date; ?>"
                                 data-disabled="<?php echo $info['disabled'] ? 'true' : 'false'; ?>">
                                <div class="cal-day"><?php echo $info['day']; ?></div>
                                <div class="cal-month"><?php echo $info['monthShort']; ?></div>
                                <div class="cal-week"><?php echo $info['dayNameShort']; ?></div>
                                <?php if ($info['booked']): ?>
                                    <div class="badge booked">Full</div>
                                <?php elseif ($info['closed']): ?>
                                    <div class="badge closed">Libur</div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="tanggal" id="selectedDate">
                        <div class="step-action">
                            <button type="button" class="btn-next" data-next="2">Selanjutnya →</button>
                        </div>
                    </div>

                    <!-- STEP 2: Pilih Jenis Buket dengan Carousel Foto -->
                    <div class="form-step" data-step="2">
                        <div class="step-indicator">
                            <span class="step-num">2</span>
                            <h3>Pilih Jenis Buket</h3>
                        </div>
                        <div class="product-list" id="productList">
                            <?php foreach ($products as $index => $product): ?>
                            <?php $photos = getProductPhotos($product); ?>
                            <div class="product-item" data-product-id="<?php echo htmlspecialchars($product['id']); ?>" data-product-index="<?php echo $index; ?>">
                                <!-- Product Image Carousel -->
                                <div class="product-carousel">
                                    <div class="carousel-container" id="carousel-<?php echo $index; ?>">
                                        <button type="button" class="carousel-btn prev" onclick="event.stopPropagation(); changeSlide(<?php echo $index; ?>, -1)">‹</button>
                                        <div class="carousel-slides">
                                            <?php foreach ($photos as $photoIdx => $photo): ?>
                                            <div class="carousel-slide" data-slide-index="<?php echo $photoIdx; ?>">
                                                <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='assets/default-product.jpg'">
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="carousel-btn next" onclick="event.stopPropagation(); changeSlide(<?php echo $index; ?>, 1)">›</button>
                                    </div>
                                    <div class="carousel-dots" id="dots-<?php echo $index; ?>">
                                        <?php foreach ($photos as $dotIdx => $photo): ?>
                                        <span class="dot" data-dot-index="<?php echo $dotIdx; ?>" onclick="event.stopPropagation(); currentSlide(<?php echo $index; ?>, <?php echo $dotIdx; ?>)"></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <div class="size-group">
                                        <label class="size-opt">
                                            <input type="radio" name="prod_<?php echo htmlspecialchars($product['id']); ?>" value="Small" data-price="<?php echo $product['price_s']; ?>">
                                            <span>Small<br><strong><?php echo formatRupiah($product['price_s']); ?></strong></span>
                                        </label>
                                        <label class="size-opt">
                                            <input type="radio" name="prod_<?php echo htmlspecialchars($product['id']); ?>" value="Medium" data-price="<?php echo $product['price_m']; ?>">
                                            <span>Medium<br><strong><?php echo formatRupiah($product['price_m']); ?></strong></span>
                                        </label>
                                        <label class="size-opt">
                                            <input type="radio" name="prod_<?php echo htmlspecialchars($product['id']); ?>" value="Large" data-price="<?php echo $product['price_l']; ?>">
                                            <span>Large<br><strong><?php echo formatRupiah($product['price_l']); ?></strong></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="jenis_buket" id="selectedProduct">
                        <input type="hidden" name="ukuran" id="selectedSize">
                        <input type="hidden" name="harga" id="selectedPrice">
                        <div class="step-action double">
                            <button type="button" class="btn-prev" data-prev="1">← Kembali</button>
                            <button type="button" class="btn-next" data-next="3">Selanjutnya →</button>
                        </div>
                    </div>

                    <!-- STEP 3: Pilih Warna -->
                    <div class="form-step" data-step="3">
                        <div class="step-indicator">
                            <span class="step-num">3</span>
                            <h3>Pilih Warna Dominan</h3>
                        </div>
                        <div class="color-list">
                            <?php foreach ($colors as $color): ?>
                            <label class="color-item" style="background: <?php echo $color['code']; ?>;">
                                <input type="radio" name="warna" value="<?php echo htmlspecialchars($color['value']); ?>">
                                <span><?php echo htmlspecialchars($color['name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="step-action double">
                            <button type="button" class="btn-prev" data-prev="2">← Kembali</button>
                            <button type="button" class="btn-next" data-next="4">Selanjutnya →</button>
                        </div>
                    </div>

                    <!-- STEP 4: Catatan & Ucapan -->
                    <div class="form-step" data-step="4">
                        <div class="step-indicator">
                            <span class="step-num">4</span>
                            <h3>Catatan & Ucapan</h3>
                        </div>
                        <div class="input-group">
                            <label>Nama Pengirim *</label>
                            <input type="text" name="nama_pengirim" id="nama_pengirim" placeholder="Siapa yang mengirim?">
                        </div>
                        <div class="input-group">
                            <label>Nama Penerima *</label>
                            <input type="text" name="nama_penerima" id="nama_penerima" placeholder="Untuk siapa buket ini?">
                        </div>
                        <div class="input-group">
                            <label>Isi Kartu Ucapan</label>
                            <textarea name="ucapan" id="ucapan" rows="3" placeholder="Tulis ucapanmu di sini...&#10;Contoh: Selamat ulang tahun! Semoga sukses selalu!"></textarea>
                        </div>
                        <div class="input-group">
                            <label>Catatan Tambahan</label>
                            <textarea name="catatan_tambahan" id="catatan_tambahan" rows="2" placeholder="Contoh: Tolong pakai pita warna gold, tambahkan bubble wrap, dll."></textarea>
                        </div>
                        <div class="step-action double">
                            <button type="button" class="btn-prev" data-prev="3">← Kembali</button>
                            <button type="submit" class="btn-submit">Kirim Pesanan →</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Ringkasan -->
        <div class="sidebar-wrapper" id="orderSummary">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h4>📋 Ringkasan Pesanan</h4>
                    <button class="toggle-sidebar" id="summaryToggle">−</button>
                </div>
                <div class="sidebar-body">
                    <div class="summary-row">
                        <span>📅 Tanggal</span>
                        <span id="summaryDate" class="summary-value">-</span>
                    </div>
                    <div class="summary-row">
                        <span>💐 Jenis</span>
                        <span id="summaryProduct" class="summary-value">-</span>
                    </div>
                    <div class="summary-row">
                        <span>📏 Ukuran</span>
                        <span id="summarySize" class="summary-value">-</span>
                    </div>
                    <div class="summary-row">
                        <span>🎨 Warna</span>
                        <span id="summaryColor" class="summary-value">-</span>
                    </div>
                    <div class="summary-row total">
                        <span>💰 Total</span>
                        <span id="summaryPrice" class="summary-value">Rp 0</span>
                    </div>
                    
                    <div class="progress-area">
                        <div class="step-progress">
                            <div class="step-circle" data-step="1">1</div>
                            <div class="step-line"></div>
                            <div class="step-circle" data-step="2">2</div>
                            <div class="step-line"></div>
                            <div class="step-circle" data-step="3">3</div>
                            <div class="step-line"></div>
                            <div class="step-circle" data-step="4">4</div>
                        </div>
                        <p class="progress-status" id="progressText">Lengkapi data pesanan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($storeName); ?> - Pesan Buket Mudah & Cepat</p>
</footer>

<script>
// Pass PHP variables to JavaScript
var whatsappNumber = '<?php echo $whatsappNumber; ?>';
var storeName = '<?php echo htmlspecialchars($storeName); ?>';
var productCount = <?php echo count($products); ?>;
</script>

<script>
// ==================== CAROUSEL FUNCTIONS ====================
let carouselStates = {};

function initCarousel(productIndex, slideCount) {
    if (!carouselStates[productIndex]) {
        carouselStates[productIndex] = {
            currentSlide: 0,
            totalSlides: slideCount
        };
    }
    updateCarouselUI(productIndex);
}

function changeSlide(productIndex, direction) {
    event.stopPropagation();
    if (!carouselStates[productIndex]) return;
    let newSlide = carouselStates[productIndex].currentSlide + direction;
    if (newSlide < 0) newSlide = carouselStates[productIndex].totalSlides - 1;
    if (newSlide >= carouselStates[productIndex].totalSlides) newSlide = 0;
    carouselStates[productIndex].currentSlide = newSlide;
    updateCarouselUI(productIndex);
}

function currentSlide(productIndex, slideIndex) {
    event.stopPropagation();
    if (!carouselStates[productIndex]) return;
    carouselStates[productIndex].currentSlide = slideIndex;
    updateCarouselUI(productIndex);
}

function updateCarouselUI(productIndex) {
    const state = carouselStates[productIndex];
    if (!state) return;
    
    const slidesContainer = document.querySelector(`#carousel-${productIndex} .carousel-slides`);
    const dots = document.querySelectorAll(`#dots-${productIndex} .dot`);
    
    if (slidesContainer) {
        slidesContainer.style.transform = `translateX(-${state.currentSlide * 100}%)`;
    }
    
    dots.forEach((dot, idx) => {
        if (idx === state.currentSlide) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

// ==================== STATE MANAGEMENT ====================
let currentStep = 1;
let selectedDate = null;
let selectedProduct = null;
let selectedProductId = null;
let selectedSize = null;
let selectedPrice = 0;
let selectedColor = null;

// DOM Elements
const steps = document.querySelectorAll('.form-step');
const summaryDate = document.getElementById('summaryDate');
const summaryProduct = document.getElementById('summaryProduct');
const summarySize = document.getElementById('summarySize');
const summaryColor = document.getElementById('summaryColor');
const summaryPrice = document.getElementById('summaryPrice');
const progressCircles = document.querySelectorAll('.step-circle');
const progressLines = document.querySelectorAll('.step-line');
const progressText = document.getElementById('progressText');
const toggleBtn = document.getElementById('summaryToggle');
const sidebar = document.getElementById('orderSummary');
const startBtn = document.getElementById('startOrderBtn');
const form = document.getElementById('bloomOrderForm');
const namaPengirim = document.getElementById('nama_pengirim');
const namaPenerima = document.getElementById('nama_penerima');

// ==================== HELPER FUNCTIONS ====================
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const d = new Date(dateStr);
    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}

function formatRupiah(val) {
    if (!val || val === 0) return 'Rp 0';
    return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function updateProgress() {
    const step1 = selectedDate !== null;
    const step2 = selectedProduct !== null && selectedSize !== null;
    const step3 = selectedColor !== null;
    const step4 = namaPengirim && namaPengirim.value.trim() !== '' && namaPenerima && namaPenerima.value.trim() !== '';
    
    if (progressCircles.length >= 4) {
        progressCircles[0].classList.toggle('completed', step1);
        progressCircles[1].classList.toggle('completed', step2);
        progressCircles[2].classList.toggle('completed', step3);
        progressCircles[3].classList.toggle('completed', step4);
        
        for (let i = 0; i < progressCircles.length; i++) {
            progressCircles[i].classList.toggle('active', i + 1 === currentStep);
        }
    }
    
    if (progressLines.length >= 3) {
        progressLines[0].classList.toggle('completed', step1);
        progressLines[1].classList.toggle('completed', step2);
        progressLines[2].classList.toggle('completed', step3);
    }
    
    if (progressText) {
        if (!selectedDate) progressText.innerHTML = '📅 Step 1: Pilih tanggal';
        else if (!selectedProduct || !selectedSize) progressText.innerHTML = '💐 Step 2: Pilih jenis & ukuran';
        else if (!selectedColor) progressText.innerHTML = '🎨 Step 3: Pilih warna';
        else if (!namaPengirim || !namaPengirim.value.trim() || !namaPenerima || !namaPenerima.value.trim()) progressText.innerHTML = '✏️ Step 4: Isi nama pengirim & penerima';
        else progressText.innerHTML = '✅ Siap kirim!';
    }
}

function updateSummary() {
    if (summaryDate) summaryDate.textContent = formatDate(selectedDate);
    if (summaryProduct) summaryProduct.textContent = selectedProduct || '-';
    if (summarySize) summarySize.textContent = selectedSize || '-';
    if (summaryColor) summaryColor.textContent = selectedColor || '-';
    if (summaryPrice) summaryPrice.textContent = formatRupiah(selectedPrice);
}

function goToStep(step) {
    if (step < 1 || step > steps.length) return;
    for (let i = 0; i < steps.length; i++) {
        steps[i].classList.toggle('active', i + 1 === step);
    }
    currentStep = step;
    updateProgress();
    document.querySelector('.form-wrapper')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validateStep(step) {
    if (step === 2 && !selectedDate) { alert('Pilih tanggal dulu!'); return false; }
    if (step === 3 && (!selectedProduct || !selectedSize)) { alert('Pilih jenis & ukuran buket!'); return false; }
    if (step === 4 && !selectedColor) { alert('Pilih warna buket!'); return false; }
    return true;
}

function validateForm() {
    if (!selectedDate) { alert('Pilih tanggal pengambilan!'); return false; }
    if (!selectedProduct || !selectedSize) { alert('Pilih jenis & ukuran buket!'); return false; }
    if (!selectedColor) { alert('Pilih warna buket!'); return false; }
    if (!namaPengirim || !namaPengirim.value.trim()) { alert('Isi nama pengirim!'); return false; }
    if (!namaPenerima || !namaPenerima.value.trim()) { alert('Isi nama penerima!'); return false; }
    return true;
}

// ==================== EVENT CALENDAR ====================
document.querySelectorAll('.calendar-card:not(.disabled)').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.calendar-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        selectedDate = this.dataset.date;
        document.getElementById('selectedDate').value = selectedDate;
        updateSummary();
        updateProgress();
    });
});

// ==================== EVENT PRODUCT ====================
document.querySelectorAll('.product-item').forEach(item => {
    const productId = item.dataset.productId;
    const productName = item.querySelector('h4')?.textContent || 'Produk';
    const radios = item.querySelectorAll('input[type="radio"]');
    
    // Click on product item to select
    item.addEventListener('click', function(e) {
        // Don't trigger if clicking on carousel buttons or radio
        if (e.target.closest('.carousel-btn') || e.target.closest('.dot') || e.target.closest('.size-opt')) return;
        
        // Auto-select first radio if none selected
        const checkedRadio = item.querySelector('input[type="radio"]:checked');
        if (!checkedRadio && radios.length > 0) {
            radios[0].checked = true;
            const event = new Event('change');
            radios[0].dispatchEvent(event);
        }
    });
    
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.product-item').forEach(p => p.classList.remove('selected'));
            item.classList.add('selected');
            selectedProduct = productName;
            selectedProductId = productId;
            selectedSize = this.value;
            selectedPrice = parseInt(this.dataset.price);
            document.getElementById('selectedProduct').value = selectedProduct;
            document.getElementById('selectedSize').value = selectedSize;
            document.getElementById('selectedPrice').value = selectedPrice;
            updateSummary();
            updateProgress();
        });
    });
});

// ==================== EVENT COLOR ====================
document.querySelectorAll('input[name="warna"]').forEach(radio => {
    radio.addEventListener('change', function() {
        selectedColor = this.value;
        updateSummary();
        updateProgress();
    });
});

// ==================== EVENT INPUT ====================
if (namaPengirim) namaPengirim.addEventListener('input', updateProgress);
if (namaPenerima) namaPenerima.addEventListener('input', updateProgress);

// ==================== EVENT NAVIGATION ====================
document.querySelectorAll('.btn-next').forEach(btn => {
    btn.addEventListener('click', function() {
        let next = parseInt(this.dataset.next);
        if (validateStep(next)) goToStep(next);
    });
});
document.querySelectorAll('.btn-prev').forEach(btn => {
    btn.addEventListener('click', function() {
        goToStep(parseInt(this.dataset.prev));
    });
});

// ==================== EVENT SUBMIT ====================
if (form) {
    form.addEventListener('submit', function(e) {
        if (!validateForm()) e.preventDefault();
    });
}

// ==================== EVENT START BUTTON ====================
if (startBtn) {
    startBtn.addEventListener('click', () => {
        document.getElementById('orderForm')?.scrollIntoView({ behavior: 'smooth' });
    });
}

// ==================== EVENT TOGGLE SIDEBAR ====================
if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = sidebar.classList.contains('collapsed') ? '+' : '−';
    });
}

// ==================== INITIALIZE CAROUSELS ====================
function initAllCarousels() {
    for (let i = 0; i < productCount; i++) {
        const slides = document.querySelectorAll(`#carousel-${i} .carousel-slide`);
        if (slides.length > 0) {
            initCarousel(i, slides.length);
        }
    }
}

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', function() {
    initAllCarousels();
    updateProgress();
    console.log('BloomOrder ready with carousel!');
});
</script>
</body>
</html>