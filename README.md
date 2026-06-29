Intan Muliana (101230113) TF23C
# BloomOrder 🌸

Sistem pemesanan buket bunga online berbasis web dengan integrasi WhatsApp.

## Fitur

### Customer
- Pilih tanggal pengambilan (60 hari ke depan)
- Pilih jenis buket dengan carousel foto
- Pilih ukuran (Small / Medium / Large)
- Pilih warna dominan
- Isi data pengirim & penerima
- Kartu ucapan & catatan tambahan
- Ringkasan pesanan real-time
- Kirim pesanan langsung ke WhatsApp

### Admin
- Dashboard dengan statistik
- CRUD produk + upload foto + galeri
- CRUD warna
- Kelola tanggal booking & hari libur
- Lihat & cari pesanan
- Pengaturan toko (WA, bank, ongkir)
- Ubah password admin

## Teknologi

| Layer | Teknologi |
|---|---|
| Frontend | HTML5 + CSS3 + Vanilla JS |
| Backend | PHP 8.x (native, no framework) |
| Database | File-based JSON |
| Auth | Session-based |
| Notifikasi | WhatsApp API (wa.me) |

## Instalasi

1. Clone repositori:
```bash
git clone https://github.com/intanmuliana/BloomOrder.git
```

2. Pindahkan folder `bloomorder` ke `C:\xampp\htdocs\`

3. Pastikan folder berikut writable:
   - `data/`
   - `uploads/products/`

4. Buka di browser:
```
http://localhost/bloomorder/
```

5. Akses admin:
```
http://localhost/bloomorder/admin/login.php
```
   - **Username:** `admin`
   - **Password:** `imbuket123`

## Struktur Direktori

```
bloomorder/
├── index.php              # Halaman utama (multi-step form)
├── process.php            # Processor form → WhatsApp + simpan order
├── style.css              # Stylesheet utama
├── script.js              # JavaScript frontend
├── admin/                 # Panel admin
│   ├── config.php         # Auth & helpers
│   ├── index.php          # Dashboard
│   ├── login.php          # Login admin
│   ├── manage_*.php       # CRUD produk, warna, tanggal, pesanan
│   └── settings.php       # Pengaturan toko
├── assets/                # Aset statis
├── data/                  # Database JSON
│   ├── products.json
│   ├── colors.json
│   ├── bookings.json
│   ├── orders.json
│   └── settings.json
└── uploads/products/      # Foto produk
```

## Testing

Jalankan PHP lint check:
```bash
php -l index.php
php -l process.php
php -l admin/*.php
```
