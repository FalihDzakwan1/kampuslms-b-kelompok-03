#!/bin/bash
# ==============================================================================
# SCRIPT DEPLOYMENT OTOMATIS PRODUKSI (deploy.sh) - KAMPUSLMS
# Digunakan untuk memperbarui aplikasi di server produksi dengan aman & teruji.
# ==============================================================================

# Hentikan eksekusi script jika ada perintah yang gagal (error handling)
set -e

echo "🚀 [1/7] Memulai proses deployment KampusLMS..."

# 1. Aktifkan Mode Pemeliharaan (Maintenance Mode)
# Mencegah pengguna mengakses atau submit data saat kode & database sedang diperbarui.
echo "🔒 [2/7] Mengaktifkan maintenance mode..."
php artisan down || true

# 2. Ambil perubahan kode terbaru dari branch produksi (main)
echo "📥 [3/7] Menarik kode terbaru dari repository Git..."
git pull origin main || true

# 3. Pasang & optimalkan dependensi Composer untuk lingkungan produksi
# --no-dev: lewati library testing untuk menghemat RAM dan disk.
# --optimize-autoloader: buat classmap statis agar respon aplikasi lebih kencang.
echo "📦 [4/7] Menginstal dependensi Composer produksi..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Jalankan migrasi database
# --force: wajib di mode produksi agar tidak berhenti meminta konfirmasi manual.
echo "🗄️ [5/7] Menjalankan migrasi database..."
php artisan migrate --force

# 5. Bangun cache performa Laravel produksi
# Menggabungkan seluruh config, route, dan view blade menjadi file statis siap saji.
echo "⚡ [6/7] Mengoptimalkan cache Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Pastikan symbolic link storage terpasang untuk file upload materi & submission
php artisan storage:link || true

# 6. Restart Background Worker Antrean
# Memaksa worker yang sedang standby di memori RAM memuat kode PHP versi terbaru.
echo "🔄 [7/7] Merestart antrean background worker..."
php artisan queue:restart

# 7. Matikan Mode Pemeliharaan (Aplikasi Kembali Online)
echo "🔓 Mematikan maintenance mode (Aplikasi kembali online)..."
php artisan up

echo "✅ Deployment KampusLMS berhasil diselesaikan tanpa kendala!"

