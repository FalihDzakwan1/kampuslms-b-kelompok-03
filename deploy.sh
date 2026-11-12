#!/bin/bash
# ==============================================================================
# SCRIPT DEPLOYMENT ULANG (deploy.sh) - KAMPUSLMS
# Untuk memperbarui aplikasi di server Linux/VPS yang diakses lewat SSH.
#
# Catatan: di Railway skrip ini TIDAK dipakai. Railway membangun ulang kontainer
# dari Dockerfile, dan langkah setara (migrate, cache, worker) ada di
# docker/start.sh.
# ==============================================================================

# Hentikan eksekusi pada kesalahan pertama. Lebih baik berhenti di tengah
# daripada melanjutkan dengan keadaan setengah jadi.
set -e

# Apa pun hasil skrip (sukses atau gagal), situs harus keluar dari mode
# maintenance. Tanpa ini, skrip yang gagal meninggalkan situs terkunci.
trap 'php artisan up' EXIT

echo "🚀 [1/8] Memulai proses deployment KampusLMS..."

# 1. Maintenance mode: mencegah pengguna submit data saat kode/database diubah.
echo "🔒 [2/8] Mengaktifkan maintenance mode..."
php artisan down

# 2. Ambil kode terbaru. Tanpa '|| true' agar set -e menghentikan skrip bila gagal.
echo "📥 [3/8] Menarik kode terbaru dari repository Git..."
git pull origin main

# 3. Dependensi PHP produksi.
# --no-dev: lewati paket testing. --optimize-autoloader: classmap statis.
echo "📦 [4/8] Menginstal dependensi Composer produksi..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Aset frontend (Vite). Dilewati bila Node.js tidak terpasang di server.
echo "🎨 [5/8] Membangun aset frontend..."
if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
else
  echo "   npm tidak ditemukan, memakai public/build yang ada di repository."
fi

# 5. Migrasi database. --force wajib di produksi (tanpa itu Laravel meminta konfirmasi).
echo "🗄️ [6/8] Menjalankan migrasi database..."
php artisan migrate --force

# 6. Cache produksi. env() di luar config/ akan bernilai null setelah config:cache.
echo "⚡ [7/8] Membangun cache Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link || true

# 7. Restart worker: worker lama masih memegang kode versi lama di memori.
echo "🔄 [8/8] Merestart antrean background worker..."
php artisan queue:restart

echo "✅ Deployment KampusLMS selesai. Maintenance mode dimatikan otomatis (trap)."
