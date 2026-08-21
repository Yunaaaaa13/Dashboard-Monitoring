# Walkthrough: Penyelarasan Dashboard & Monitoring Multi-Bulan KAWAI PLANT-3 (Ratio & Realisasi)

Kami telah menyelesaikan serangkaian perbaikan estetika (penyelarasan background, transparansi tabel, dan legibilitas kontras tinggi), penambahan header/navigasi yang jelas pada dashboard, serta implementasi lengkap fitur monitoring multi-bulan untuk **PT Kawai Indonesia (PLANT-3)**.

---

## 🎨 1. Penyelarasan Tampilan & Perbaikan Kontras (Aesthetics & Readability)

Untuk mengatasi masalah teks dan elemen yang tidak terlihat atau tertumpuk oleh efek background dan Bootstrap reset (`#ffffff`), kami telah menerapkan penyelarasan CSS menyeluruh:

1. **Konsistensi Background Dark Glassmorphism:**
   - Seluruh dashboard (`Dashboard Utama`, `Dashboard Master Data & Monitoring`, `Dashboard Analisis`, `Comparison vs Actual`, `Input Realisasi`, dan `Riwayat & Audit`) menggunakan background terpadu `radial-gradient(circle at top right, #1a2236 0%, #0A0E1A 60%)` dengan kartu bernuansa kaca gelap (`rgba(18, 24, 38, 0.75)`).
2. **Perbaikan Transparansi & Kontras Sel Tabel (`.table-custom`, `#tbl-plant3`):**
   - Menambahkan override `--bs-table-bg: transparent !important;` dan `--bs-table-color: var(--text-main) !important;` agar sel tabel tidak kembali menjadi putih (`#ffffff`).
   - Warna teks kolom nomor (`NO / #`) dan deskripsi (`DESCRIPTION`) diselaraskan menggunakan `#cbd5e1` / `#ffffff` dengan ketebalan (`fw-bold` / `fw-semibold`) yang tegas.
   - **Part Number** disorot menggunakan warna kuning emas khas PT Kawai (`#fbbf24` / `text-warning`) agar langsung terlihat jelas saat dipindai oleh tim Purchasing.
   - **Badge `#DIV/0!`** diperbarui menggunakan background gelap tembus pandang (`bg-secondary bg-opacity-50 text-light border border-secondary`) agar angka maupun indikator pembagian nol terbaca dengan sangat tajam.
3. **Perbaikan Placeholder Search Box (`::placeholder`):**
   - Input pencarian (`#searchPlant3`, `#tableSearch`) dilengkapi dengan aturan `.form-control-dark::placeholder` berwarnakan `#cbd5e1` (opacity 0.85), memecahkan masalah teks pencarian yang gelap pada background gelap.

---

## 🏷️ 2. Penambahan Header & Navigasi Antar Dashboard

Kami telah menambahkan **Banner / Header** yang informatif pada dashboard utama serta halaman input realisasi bulanan untuk memudahkan navigasi ke fitur baru maupun analisis:

- **Dashboard Utama (`overview.blade.php`)**: Ditambahkan Banner Pintasan ke *Monitoring Multi-Bulan KAWAI PLANT-3*, *Dashboard Analisis & Sinkronisasi*, dan *Comparison Outstanding vs Actual*.
- **Halaman Input Realisasi (`input.blade.php`)**: Ditambahkan Banner Multi-Bulan KAWAI PLANT-3 yang memberikan arahan langsung bahwa untuk melihat rasio 3, 4, 5 hingga 12 bulan sekaligus, user dapat mengakses tab monitoring khusus di halaman Master Outstanding.

---

## ⚙️ 3. Monitoring Multi-Bulan KAWAI PLANT-3 & Live Ratio (%)

Fitur monitoring khusus untuk **KAWAI PLANT-3** telah diintegrasikan pada tab utama di [resources/views/purchasing/outstanding.blade.php](file:///c:/Users/Acer/Documents/berkas%20kuliah/KP/resources/views/purchasing/outstanding.blade.php) dan didukung oleh backend controller (`PurchasingOutstandingController.php`).

```mermaid
graph TD
    A[Selector Jarak Waktu<br/><b>3 / 4 / 5 / 6 - 12 Bulan</b>] -->|Filter URL ?duration=X| B[Controller & Database]
    B --> C[Tabel Monitoring PLANT-3<br/><b>PO | PROD | STOCK | RATIO %</b>]
    C --> D[Modal Tambah / Edit Data PLANT-3]
    D -->|Real-Time JS Event| E[Kalkulasi Live Ratio %<br/><b>(Stock / Next PROD) * 100%</b>]
```

### ✨ Fitur Kunci:
1. **Pilihan Jarak Waktu Fleksibel (`?duration=3`, `4`, `5`, dst.)**:
   - Tim Purchasing dapat memilih rentang waktu pantauan dari **3 Bulan**, **4 Bulan**, **5 Bulan**, hingga **12 Bulan** melalui dropdown selektor di atas tabel.
   - Kolom PO, PROD, STOCK, dan RATIO (%) untuk setiap bulan secara otomatis ditampilkan sesuai jumlah bulan yang dipilih.

2. **Kalkulasi Ratio (%) Secara Real-Time & Otomatis**:
   - **Rumus Ratio**:
     $$\text{Ratio Bulan } i = \left( \frac{\text{Stock Bulan } i}{\text{PROD Bulan } i+1} \right) \times 100\%$$
   - Jika kebutuhan PROD bulan berikutnya adalah `0`, sistem menampilkan status **`#DIV/0!`**.
   - **Warna Indikator Otomatis**:
     - 🔴 **Ratio < 100%**: Badge Merah (Stok di bawah kebutuhan bulan depan, perlu expedite PO).
     - 🟡 **Ratio 100% - 200%**: Badge Kuning (Stok pas/aman untuk produksi satu bulan ke depan).
     - 🟢 **Ratio > 200%**: Badge Hijau (Stok melimpah, mencukupi lebih dari 2 bulan produksi).

3. **Live JavaScript Calculator dalam Modal Input**:
   - Saat user memasukkan atau mengubah nilai `PROD` dan `STOCK` di dalam modal **+ Tambah Data PLANT-3** atau **Edit Data PLANT-3**, rasio setiap bulan dan stok bulan berikutnya langsung dikalkulasi dan diperbarui secara *live* tanpa perlu me-reload halaman.

---

## 🚀 Status Verifikasi

Semua perubahan telah diuji dan divalidasi menggunakan perintah artisan dan pengecekan sintaksis PHP:
- `php artisan view:clear ; php artisan view:cache` ✅ *(Semua template Blade terkompilasi sempurna tanpa error)*
- `php -l app/Http/Controllers/PurchasingOutstandingController.php` ✅ *(Sintaksis PHP valid)*
- `php -l routes/web.php` ✅ *(Rute konsisten)*
