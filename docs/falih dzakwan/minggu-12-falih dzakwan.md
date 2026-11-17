#### Nama : Falih Dzakwan
#### NIM  : 10241028

---

# MINGGU 12 — Deployment Produksi, Hardening Container, dan Audit Server

Catatan individu Minggu 12 ini berisi hasil inspeksi lingkungan server produksi di Railway (`https://kampuslms-b-kelompok-03-production-03.up.railway.app`) (**READ**) serta analisis delapan skenario kesalahan konfigurasi server (**BREAK**) pada repositori `kampuslms-b-kelompok-03`.

---

## READ — Bedah Server Setelah Deployment (40 Menit)

### 1. Temukan konfigurasi Nginx/Apache untuk situs Anda. Baris mana yang menentukan document root? Ke folder apa ia mengarah?

Aplikasi KampusLMS Kelompok 03 di-deploy ke Railway menggunakan image Docker **`php:8.4-apache`** (`Dockerfile:2`), bukan Nginx. Pengaturan *Document Root* ditentukan pada **`Dockerfile` baris 27–34**:

```dockerfile
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/sites-available/*.conf
RUN sed -ri "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf
```

Document root diarahkan secara tegas ke subfolder **`/var/www/html/public`** (tempat `index.php` berada). Tujuannya agar direktori sensitif seperti `.env`, `app/`, `config/`, `database/`, dan `storage/` berada di luar jangkauan web server sehingga tidak dapat diunduh langsung dari browser internet.

---

### 2. Jalankan `php artisan about` di server. Bandingkan dengan lokal — apa saja yang berbeda?

| Parameter | Lingkungan Lokal (Windows) | Lingkungan Produksi (Railway Container) |
|---|---|---|
| **Environment** | `local` | `production` |
| **Debug Mode (`APP_DEBUG`)** | `ENABLED` (`true`) | **`OFF` (`false`)** |
| **URL Aplikasi** | `http://127.0.0.1:8000` / `.test` | `https://kampuslms-b-kelompok-03-production-03.up.railway.app` |
| **Sistem & PHP** | Windows (Laragon/Herd) | Linux Debian Container (`php:8.4-apache`) |
| **Database Driver** | Database lokal development | Cloud MySQL Railway |
| **Status Optimasi Cache** | `Config`, `Routes`, `Views`, `Events` = **`NOT CACHED`** | `Config`, `Routes`, `Views`, `Events` = **`CACHED`** (dibangun oleh `docker/start.sh:27-30`) |
| **Composer Packages** | Termasuk paket dev (`phpunit`, `faker`, dll.) | Hanya dependensi produksi (`--no-dev --optimize-autoloader` di `Dockerfile:44-47`) |

---

### 3. Jalankan `supervisorctl status`. Berapa worker yang berjalan?

Berdasarkan konfigurasi pada **`supervisord.conf:17-36`** dan verifikasi pada container server (`docs/image/supervisorctl_status.png`):

```text
apache2                            RUNNING   pid 14, uptime ...
laravel-worker:laravel-worker_00   RUNNING   pid 15, uptime ...
laravel-worker:laravel-worker_01   RUNNING   pid 16, uptime ...
```

Terdapat **1 proses web server (`apache2`)** dan **2 proses Queue Worker (`numprocs=2`)**, yaitu `laravel-worker_00` dan `laravel-worker_01`, yang ketiganya berstatus **`RUNNING`**. Keduanya menjalankan perintah:
`php /var/www/html/artisan queue:work database --sleep=3 --tries=3 --max-time=3600` sebagai user non-root `www-data`.

---

### 4. Buka `storage/logs/laravel.log`. Ada berapa entri? Baca yang terbaru.

Berkas log produksi (`storage/logs/laravel.log`) dipantau pasca-deployment untuk memastikan tidak ada exception fatal atau error koneksi database. Ketika kontainer atau deployment selesai dijalankan dan memicu sinyal restart antrean, entri informasi terbaru yang tercatat berbentuk:

```text
[2026-11-10 ...] production.INFO: Broadcasting queue restart signal.
```

Serta log worker diarahkan ke `/var/www/html/storage/logs/worker.log` oleh Supervisor agar pesan kesalahan latar belakang (termasuk method `failed()` dari job notifikasi) mudah ditelusuri tanpa bercampur dengan access log Apache.

---

### 5. Jalankan perintah `curl -I` dari daftar periksa. Catat hasilnya.

Pengujian header HTTP (*Security Hardening Check*) terhadap domain produksi `https://kampuslms-b-kelompok-03-production-03.up.railway.app`:

| Target Pengujian | Perintah `curl -I` | Status HTTP yang Diamati | Kesimpulan |
|---|---|---|---|
| **Akses Berkas `.env`** | `curl -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/.env` | **`404 Not Found`** | **Aman:** Kredensial `.env` tidak bisa diunduh publik karena root terkunci di `/public`. |
| **Akses Log Aplikasi** | `curl -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/storage/logs/laravel.log` | **`404 Not Found`** | **Aman:** Log internal terlindungi dari akses internet. |
| **Akses Unduh Tugas Tanpa Login** | `curl -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/submissions/1/download` | **`302 Found`** (`Location: .../login`) | **Aman:** Rute privat terproteksi middleware `auth` dan melempar anonim ke halaman login. |
| **Header Keamanan & `expose_php`** | `curl -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/` | Terdapat `Strict-Transport-Security`, `X-Frame-Options`, `X-Content-Type-Options`, dan tidak ada `X-Powered-By` | **Aman:** Dilindungi `SecurityHeadersMiddleware` (`bootstrap/app.php:21`) dan `expose_php = Off` (`docker/php/custom.ini:10`). |

---

### 6. Buka aplikasi dari HP Anda, bukan laptop. Ada yang rusak?

Pengujian lintas perangkat dilakukan secara langsung menggunakan **smartphone dengan jaringan data seluler eksternal** (termasuk verifikasi akun demo yang saya dokumentasikan di commit `73c9950`):
* **Tampilan Responsif:** Layout berbasis Tailwind CSS beradaptasi rapi di layar sempit ponsel; menu navigasi tidak meluber dan tabel memiliki gulir horizontal yang terbaca jelas.
* **Protokol HTTPS & Aset CSS/JS:** Tidak ada error *mixed content* atau aset CSS hilang. Konfigurasi `$middleware->trustProxies(at: '*');` pada `bootstrap/app.php:18` memastikan Laravel mengenali header `X-Forwarded-Proto: https` dari reverse proxy Railway sehingga seluruh URL `asset()` dan `route()` dibangkitkan menggunakan `https://`.
* **Alur Fungsional:** Login akun demo, unggah tugas PDF, pratinjau/unduh materi, dan notifikasi berjalan normal di peramban mobile.

---

## BREAK — Delapan Percobaan Server Produksi (50 Menit)

| # | Yang Dicoba | Yang Harus Diamati | Penjelasan Teknis & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Set `APP_DEBUG=true` di server produksi lalu picu exception pada form controller dan buka dari jendela penyamaran. | Layar error Laravel Ignition menampilkan potongan *source code*, query SQL lengkap, variabel request/cookie, dan konfigurasi environment. | **Kebocoran Informasi Fatal (*Information Disclosure*):** Setiap pengunjung asing dapat memicu error dan membaca rahasia konfigurasi server. Produksi wajib mengunci `APP_DEBUG=false`. |
| **2** | Arahkan Document Root web server ke akar proyek (`/var/www/html`) alih-alih `/var/www/html/public`. | Berkas `.env`, `composer.json`, dan kode backend dapat diunduh langsung dari URL browser. | **Kebocoran Total Aplikasi & Kredensial:** Tanpa mengisolasi akses publik ke folder `public/`, seluruh password database, `APP_KEY`, dan kode sumber terbuka bagi siapa saja. |
| **3** | Jalankan `php artisan config:cache` ketika kode controller masih memanggil `env('APP_NAME')`. | Fungsi `env()` mengembalikan **`null`** sehingga konfigurasi aplikasi gagal terbaca. | **Cache Config Memutus Pembacaan `.env`:** Setelah `config:cache` aktif, `.env` tidak lagi dibaca per request. Seluruh pembacaan konfigurasi di kode wajib memakai helper `config(...)`. |
| **4** | Hentikan proses Supervisor / Queue Worker, lalu buat tugas baru di web. | Tugas tetap tersimpan normal, tetapi notifikasi tidak terkirim dan antrean menumpuk di tabel `jobs` tanpa pesan error di browser. | **Silent Failure Queue:** Supervisor wajib menjaga proses `laravel-worker` tetap hidup (`autorestart=true` di `supervisord.conf:21`) agar kegagalan atau crash worker langsung dipulihkan otomatis. |
| **5** | Lakukan pembaruan kode (`git pull`) di server VPS tanpa menjalankan `php artisan queue:restart`. | Worker yang sudah aktif di RAM terus mengeksekusi kode lama sebelum pembaruan. | **Kode Basi di Memori Worker:** Queue worker adalah proses *long-lived* di RAM. `php artisan queue:restart` (seperti di `deploy.sh:57`) wajib dijalankan agar worker berhenti dengan aman dan dimulai ulang oleh Supervisor memuat kode baru. |
| **6** | Ubah hak akses direktori `storage/` menjadi `555` (tanpa izin tulis). | Aplikasi mengalami crash **HTTP 500 Internal Server Error** saat menulis file log, session, cache Blade, atau unggahan berkas. | **Permission Web Server:** User `www-data` membutuhkan hak baca dan tulis (`chmod -R 775` pada `Dockerfile:53-55`) untuk `storage/` dan `bootstrap/cache/`. |
| **7** | Set `upload_max_filesize = 1M` di `php.ini`, lalu unggah berkas 3 MB. | PHP server langsung menolak unggahan sebelum validasi Laravel sempat memproses request. | **Batas Upload PHP Server:** Konfigurasi `php.ini` harus lebih besar daripada batas validasi aplikasi. Oleh sebab itu saya menambahkan `docker/php/custom.ini` (`upload_max_filesize = 25M`, `post_max_size = 30M` pada commit `b72de93`) agar unggahan materi 20 MB dan tugas 5 MB tidak terpotong di tengah jalan. |
| **8** | Akses aplikasi via `http://` biasa ketika `SESSION_SECURE_COOKIE=true` aktif. | Browser menolak menyimpan cookie session sehingga pengguna terus terlempar kembali ke halaman login setiap kali masuk. | **Cookie Terenkripsi Wajib HTTPS:** Flag `Secure` pada cookie melarang peramban mengirim atau menyimpan session melalui koneksi HTTP polos guna mencegah pembajakan sesi (*session hijacking*). Di Railway, koneksi eksternal selalu HTTPS dan reverse proxy dipercaya melalui `trustProxies(at: '*')`. |

---

## Kesimpulan

Keamanan dan keandalan server produksi ditentukan bukan hanya oleh kode PHP di controller, tetapi juga oleh konfigurasi infrastruktur di sekitarnya: penguncian `APACHE_DOCUMENT_ROOT` ke `public/`, `APP_DEBUG=false`, pemakaian `config()` setelah `config:cache`, penyelarasan batas upload `php.ini` (`25M`/`30M`), pengelolaan proses web dan queue worker oleh Supervisor, serta pengujian nyata melalui `curl -I` dan perangkat mobile di jaringan luar.
