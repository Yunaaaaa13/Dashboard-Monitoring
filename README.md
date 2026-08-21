# Dashboard-Monitoring: Monitoring Purchasing & Produksi PT Kawai Indonesia

Aplikasi internal untuk mencatat realisasi pembelian, memantau outstanding PO, mengelola alur approval, mencatat produksi, serta analisis perbandingan 3-Slide (Kurs, Infografis Tren Supplier, dan Stock Forecast vs Actual).

## Menjalankan aplikasi

1. Salin `.env.example` menjadi `.env`, lalu sesuaikan konfigurasi database.
2. Jalankan `php artisan migrate --seed`.
3. Jalankan `php artisan serve` dan buka alamat yang ditampilkan.

## Peran pengguna

- **Staff:** membuat input purchasing/produksi dan memperbarui progress PO.
- **Leader:** mengelola periode monitoring, riwayat, serta konfirmasi supplier.
- **Supervisor:** mengelola master data, penghapusan, approval PO, dan keputusan IAD.

Semua perubahan data membutuhkan login. Akun seed memakai password awal `password`; ganti untuk penggunaan selain demo lokal.

## Integrasi EZRunner

Webhook produksi tersedia di `POST /api/ezrunner/sync`. Atur `EZRUNNER_WEBHOOK_KEY` pada `.env`, kemudian kirim secret tersebut melalui header `X-EZRunner-Key`.

