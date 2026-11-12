#!/bin/sh
# Skrip start kontainer produksi (Railway). Dipanggil oleh CMD di Dockerfile.
# Menggantikan CMD satu baris panjang yang rawan salah ketik.

# Berhenti pada kesalahan pertama: lebih baik kontainer gagal start
# daripada berjalan dengan keadaan setengah jadi.
set -e

cd /var/www/html

# Railway memberi port lewat variabel PORT; Apache harus mendengarkan port itu.
export PORT="${PORT:-8080}"
sed -i "s/Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Pastikan hanya satu MPM aktif (mpm_prefork) agar Apache tidak gagal start.
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*

# Migrasi: --force wajib karena produksi meminta konfirmasi.
php artisan migrate --force

# Akun demo untuk penguji (idempotent, aman dijalankan berulang).
php artisan db:seed --class=DemoAccountSeeder --force

# Cache produksi. Dijalankan setelah environment variable Railway tersedia,
# jadi nilai .env/variabel ikut terbaca. env() hanya boleh ada di config/.
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Serahkan kendali ke Supervisor (menjalankan Apache + queue worker).
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
