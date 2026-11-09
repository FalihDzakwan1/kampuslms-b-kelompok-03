#!/usr/bin/env bash

# ==============================================================================
# Skrip Deployment Otomatis KampusLMS (Laravel 12)
# ==============================================================================

# Berhenti seketika jika ada perintah yang menghasilkan return code bukan nol.
# AKIBAT JIKA DIHILANGKAN: Jika salah satu perintah gagal (misal: composer install
# gagal atau migrasi error), skrip akan terus lanjut mereset cache dan mematikan
# maintenance mode, sehingga pengguna disajikan aplikasi yang rusak/patah.
set -e

# Pastikan eksekusi berjalan di direktori utama aplikasi.
# AKIBAT JIKA DIHILANGKAN: Perintah artisan/git yang dijalankan dari folder lain
# akan gagal karena tidak menemukan struktur file Laravel.
cd "$(dirname "$0")"

echo "=== [1/8] Mengaktifkan Mode Pemeliharaan (Maintenance Mode) ==="
# Mengalihkan seluruh traffic pengunjung ke halaman holding page khusus dengan kode HTTP 503.
# AKIBAT JIKA DIHILANGKAN: Pengguna aktif yang sedang submit transaksi atau login
# di tengah-tengah proses pembaruan kode/migrasi akan mengalami crash fatal atau data korup.
php artisan down --refresh=15 --retry=60 || true

echo "=== [2/8] Mengambil Pembaruan Kode Terbaru dari Git ==="
# Menarik commit terbaru dari branch produksi di repositori GitHub.
# AKIBAT JIKA DIHILANGKAN: Server akan tetap menjalankan kode program lama meskipun
# dependensi dan cache diperbarui.
git fetch origin main
git reset --hard origin/main

echo "=== [3/8] Menginstal Dependensi PHP (Composer) ==="
# Menginstal paket dependensi vendor produksi secara optimal tanpa paket development (testing/debug).
# AKIBAT JIKA DIHILANGKAN: Fitur baru yang memakai library pihak ketiga akan error
# "Class not found", atau package dev yang berat ikut terpasang di produksi.
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "=== [4/8] Memperbarui Struktur Basis Data (Migrasi) ==="
# Menjalankan migrasi skema tabel baru secara aman di server produksi dengan flag --force.
# AKIBAT JIKA DIHILANGKAN: Kolom baru atau tabel baru di kode tidak sinkron dengan
# basis data nyata, memicu SQL Error "Column not found".
php artisan migrate --force

echo "=== [5/8] Membersihkan dan Membangun Ulang Seluruh Cache ==="
# Menghapus seluruh cache konfigurasi, rute, view Blade, dan event lama.
# AKIBAT JIKA DIHILANGKAN: Laravel tetap membaca konfigurasi/rute lama dari cache file fisik
# di bootstrap/cache/, sehingga perubahan file .env atau rute web.php baru tidak akan aktif.
php artisan optimize:clear

# Mengompilasi seluruh file config, rute, dan view Blade menjadi array PHP statis tunggal.
# AKIBAT JIKA DIHILANGKAN: Kinerja aplikasi melambat drastis karena Laravel harus membaca
# dan mem-parse ratusan file konfigurasi dan view dari hard disk pada setiap HTTP request.
php artisan optimize
php artisan view:cache
php artisan event:cache

echo "=== [6/8] Membangun Aset Frontend (Vite) ==="
# Memasang dependensi Node.js dan mengompilasi CSS/JS Tailwind ke folder public/build.
# AKIBAT JIKA DIHILANGKAN: Tampilan web menjadi berantakan (CSS hilang) atau Javascript
# interaktif tidak berfungsi karena berkas manifest Vite tidak sesuai dengan asset terkompilasi.
if command -v npm &> /dev/null; then
    npm ci --prefer-offline --no-audit
    npm run build
fi

echo "=== [7/8] Me-restart Antrean Latar Belakang (Queue Worker) ==="
# Mengirim sinyal restart ke worker antrean.
# MENGAPA WAJIB: Queue worker di Laravel berjalan sebagai 'long-running process' di memori RAM.
# Worker tidak akan membaca perubahan kode PHP atau model database baru KECUALI worker tersebut
# dimatikan dan dinyalakan ulang.
# AKIBAT JIKA DIHILANGKAN: Worker akan terus mengeksekusi antrean menggunakan kode lama di memori,
# mengakibatkan kegagalan saat memproses job yang strukturnya sudah berubah.
php artisan queue:restart
if command -v supervisorctl &> /dev/null; then
    sudo supervisorctl restart kampuslms-worker:* || true
fi

echo "=== [8/8] Uji Kesehatan Aplikasi Sebelum Menonaktifkan Maintenance ==="
# Memastikan server lokal merespons dengan status 200/OK sebelum aplikasi dibuka untuk umum.
# AKIBAT JIKA DIHILANGKAN: Jika terjadi kesalahan konfigurasi fatal yang tidak terdeteksi,
# pengunjung luar langsung dihadapkan pada halaman error begitu maintenance dimatikan.
HEALTH_CHECK_URL="http://127.0.0.1:8080/up"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_CHECK_URL" || true)

if [ "$HTTP_STATUS" -ne 200 ] && [ "$HTTP_STATUS" -ne 302 ]; then
    echo "Peringatan: Pemeriksaan kesehatan lokal menghasilkan HTTP $HTTP_STATUS!"
    echo "Memeriksa endpoint login lokal..."
    HTTP_STATUS_LOGIN=$(curl -k -s -o /dev/null -w "%{http_code}" "http://127.0.0.1:8080/login" || true)
    if [ "$HTTP_STATUS_LOGIN" -ne 200 ] && [ "$HTTP_STATUS_LOGIN" -ne 302 ]; then
        echo "KESALAHAN FATAL: Aplikasi merespons HTTP $HTTP_STATUS_LOGIN. Maintenance tetap diaktifkan!"
        exit 1
    fi
fi

# Menonaktifkan mode maintenance sehingga aplikasi kembali online untuk publik.
# AKIBAT JIKA DIHILANGKAN: Aplikasi akan selamanya menampilkan status '503 Service Unavailable'
# kepada semua pengunjung meskipun deployment di server sebenarnya sudah berhasil.
php artisan up

echo "=== Deployment Berhasil Diselesaikan! Aplikasi telah aktif kembali. ==="
exit 0

