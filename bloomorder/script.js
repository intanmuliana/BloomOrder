document.addEventListener('DOMContentLoaded', function() {
    
    // State
    let currentStep = 1;
    let selectedDate = null;
    let selectedProduct = null;
    let selectedSize = null;
    let selectedPrice = 0;
    let selectedColor = null;
    
    // Elements
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
    
    // Functions
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
    
    // Calendar
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
    
    // Product
    document.querySelectorAll('.product-item').forEach(item => {
        const radios = item.querySelectorAll('input[type="radio"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.product-item').forEach(p => p.classList.remove('selected'));
                item.classList.add('selected');
                selectedProduct = item.querySelector('h4')?.textContent || 'Buket';
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
    
    // Color
    document.querySelectorAll('input[name="warna"]').forEach(radio => {
        radio.addEventListener('change', function() {
            selectedColor = this.value;
            updateSummary();
            updateProgress();
        });
    });
    
    // Inputs
    if (namaPengirim) namaPengirim.addEventListener('input', updateProgress);
    if (namaPenerima) namaPenerima.addEventListener('input', updateProgress);
    
    // Navigation
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
    
    // Submit
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) e.preventDefault();
        });
    }
    
    // Start button
    if (startBtn) {
        startBtn.addEventListener('click', () => {
            document.getElementById('orderForm')?.scrollIntoView({ behavior: 'smooth' });
        });
    }
    
    // Toggle sidebar
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            toggleBtn.textContent = sidebar.classList.contains('collapsed') ? '+' : '−';
        });
    }
    
    updateProgress();
    console.log('BloomOrder ready');
});

// ==================== CAROUSEL FUNCTIONS ====================
let carouselStates = {};

function initCarousel(productIndex, slideCount) {
    if (!carouselStates[productIndex]) {
        carouselStates[productIndex] = {
            currentSlide: 0,
            totalSlides: slideCount
        };
    }
    updateCarousel(productIndex);
}

function changeSlide(productIndex, direction) {
    if (!carouselStates[productIndex]) return;
    let newSlide = carouselStates[productIndex].currentSlide + direction;
    if (newSlide < 0) newSlide = carouselStates[productIndex].totalSlides - 1;
    if (newSlide >= carouselStates[productIndex].totalSlides) newSlide = 0;
    carouselStates[productIndex].currentSlide = newSlide;
    updateCarousel(productIndex);
}

function currentSlide(productIndex, slideIndex) {
    if (!carouselStates[productIndex]) return;
    carouselStates[productIndex].currentSlide = slideIndex;
    updateCarousel(productIndex);
}

function updateCarousel(productIndex) {
    const state = carouselStates[productIndex];
    if (!state) return;
    
    const slidesContainer = document.querySelector(`#carousel-${productIndex} .carousel-slides`);
    const dots = document.querySelectorAll(`#carousel-${productIndex} ~ .carousel-dots .dot, .product-item:has(#carousel-${productIndex}) .carousel-dots .dot`);
    
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

// Initialize carousels on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all carousels
    const carousels = document.querySelectorAll('.carousel-container');
    carousels.forEach((carousel, idx) => {
        const slides = carousel.querySelectorAll('.carousel-slide');
        initCarousel(idx, slides.length);
    });
});