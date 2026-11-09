# Laporan Deployment & Panduan Produksi KampusLMS (Milestone M4)

Dokumen ini memuat catatan teknis arsitektur, implementasi skrip deployment otomatis (`deploy.sh`), pemantauan background worker menggunakan **Supervisor**, daftar periksa keamanan, serta dokumentasi kendala (*troubleshooting*) aplikasi **KampusLMS** di server produksi.

---

## 1. Ringkasan & Tautan Produksi Live
* **Tautan Aplikasi Live (HTTPS):** [https://kampuslms-b-kelompok-03.up.railway.app](https://kampuslms-b-kelompok-03.up.railway.app)
* **Status Aplikasi:** 🟢 **Active / Live (HTTPS / SSL Valid)**
* **Branch Git Produksi:** `main`

---

## 2. Deliverable 3: Skrip `deploy.sh` di Repository

File skrip: [`deploy.sh`](../deploy.sh)
Skrip bash ini telah diuji dan dirancang dengan komentar penjelasan di setiap tahapannya untuk memperbarui aplikasi secara aman di server produksi.

### Alur Eksekusi Skrip `deploy.sh`:
1. `php artisan down`: Mengaktifkan *maintenance mode* agar pengguna tidak melakukan input saat deploy.
2. `git pull origin main`: Mengambil pembaruan commit terbaru dari branch `main`.
3. `composer install --no-dev --optimize-autoloader`: Memasang dependensi PHP produksi tanpa paket testing.
4. `php artisan migrate --force`: Menjalankan migrasi database baru secara otomatis tanpa konfirmasi interaktif.
5. `php artisan config:cache`, `route:cache`, `view:cache`: Membangun file cache performa Laravel.
6. `php artisan queue:restart`: Memberi sinyal restart ke worker antrean agar memuat kode PHP terbaru dari RAM.
7. `php artisan up`: Mematikan maintenance mode sehingga web kembali aktif diakses pengguna.

### Bukti Pengujian Skrip di Terminal:
```text
🚀 [1/7] Memulai proses deployment KampusLMS...
🔒 [2/7] Mengaktifkan maintenance mode...
INFO  Application is now in maintenance mode.
📥 [3/7] Menarik kode terbaru dari repository Git...
Already up to date.
📦 [4/7] Menginstal dependensi Composer produksi...
Installing dependencies from lock file...
Nothing to install, update or remove.
Generating optimized autoload files.
🗄️ [5/7] Menjalankan migrasi database...
Nothing to migrate.
⚡ [6/7] Mengoptimalkan cache Laravel...
INFO  Configuration cached successfully.
INFO  Routes cached successfully.
INFO  Blade templates cached successfully.
🔄 [7/7] Merestart antrean background worker...
INFO  Broadcasting queue restart signal.
🔓 Mematikan maintenance mode (Aplikasi kembali online)...
INFO  Application is now live.
✅ Deployment KampusLMS berhasil diselesaikan tanpa kendala!
```

---

## 3. Deliverable 4: Supervisor & Bukti `supervisorctl status`

File konfigurasi di repo: [`supervisord.conf`](../supervisord.conf)

Untuk mengelola pemrosesan tugas latar belakang (seperti pengiriman notifikasi penugasan dan nilai kepada mahasiswa), server produksi menjalankan **Supervisor** sebagai *process manager*.

### A. Konfigurasi `supervisord.conf`
Supervisor dikonfigurasi untuk menjalankan 2 worker antrean Laravel secara otomatis:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
```

### B. Bukti Tangkapan Layar & Keluaran `supervisorctl status`

Keluaran status proses dari terminal server produksi:
```text
laravel-worker:laravel-worker_00   RUNNING   pid 142, uptime 18:24:12
laravel-worker:laravel-worker_01   RUNNING   pid 143, uptime 18:24:12
apache2                            RUNNING   pid 144, uptime 18:24:12
```

![Tangkapan Layar Status Supervisor](image/supervisorctl_status.png)

### C. Analisis: Apa Akibatnya Kalau Worker Mati dan Tidak Ada yang Tahu?
Jika worker antrean mati dan tidak diawasi oleh Supervisor, terjadi **Kegagalan Diam-diam (*Silent Failure*)**:
1. Dosen tetap berhasil menyimpan tugas atau nilai di layar web tanpa pesan error.
2. Namun, job notifikasi hanya menumpuk dan tertahan di tabel database `jobs`.
3. Notifikasi tidak akan pernah dikirimkan ke mahasiswa, lonceng notifikasi mahasiswa tetap kosong, dan email tidak pernah terkirim.
4. Dengan adanya Supervisor, jika worker mati akibat *out-of-memory* atau crash sistem, Supervisor akan mendeteksi dan menyalakan kembali worker dalam hitungan milidetik secara otomatis (`autorestart=true`).

---

## 4. Daftar Periksa Keamanan Produksi & Bukti cURL Nyata

Semua pengujian keamanan diuji langsung ke server produksi:

### 1. Proteksi File `.env` (Wajib 404/403, Tidak Boleh 200)
```bash
curl -I https://kampuslms-b-kelompok-03.up.railway.app/.env
```
**Keluaran:**
```text
HTTP/2 404 
date: Mon, 09 Nov 2026 13:16:42 GMT
server: Apache/2.4.62 (Debian)
```
*Hasil:* ✅ **LOLOS (404 Not Found)**. File kredensial aman dari akses publik karena Document Root dikunci ke `/public`.

### 2. Proteksi File Log Sistem
```bash
curl -I https://kampuslms-b-kelompok-03.up.railway.app/storage/logs/laravel.log
```
**Keluaran:**
```text
HTTP/2 404 
```
*Hasil:* ✅ **LOLOS (404 Not Found)**.

---

## 5. Masalah Utama yang Ditemui & Cara Mengatasinya

### Masalah 1: Mixed Content HTTP pada Domain HTTPS Railway
* **Penyebab:** Railway menggunakan TLS Termination Reverse Proxy di depan container, sehingga trafik masuk ke Apache terbaca sebagai HTTP port 8080. Laravel menganggap request adalah HTTP dan menghasilkan link aset `@vite` serta form action berawalan `http://`.
* **Solusi:** Menambahkan `$middleware->trustProxies(at: '*');` pada file `bootstrap/app.php` sehingga Laravel membaca header `X-Forwarded-Proto` dari Railway dan otomatis menggunakan HTTPS.

### Masalah 2: Error Seeder Duplicate Entry Saat Redeploy
* **Penyebab:** Perintah `db:seed` dijalankan otomatis saat container booting, sehingga memicu error SQL unique constraint pada tabel `users`.
* **Solusi:** Mengubah seluruh seeder di `database/seeders/DatabaseSeeder.php` menggunakan `firstOrCreate()` agar bersifat *idempotent* (aman dijalankan berulang kali).

