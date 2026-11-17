### Nama : Fatika Rizki Syahada
#### NIM : 10241030

---

# READ — Bedah Server Setelah Deployment

### 1. Temukan konfigurasi Nginx untuk situs Anda. Baris mana yang menentukan document root? Ke folder apa ia mengarah?

**Jawaban :**

Document root merupakan direktori pokok yang dibaca oleh web server untuk melayani berkas website kepada pengunjung di internet. Pada web server Nginx, document root biasanya diatur menggunakan direktif `root`, misalnya:

```nginx
root /var/www/html/public;
```

Pengaturan tersebut mengunci document root ke subfolder `public` milik Laravel, tempat file `index.php` berada. Tujuannya adalah memastikan berkas rahasia seperti `.env`, folder `app`, `config`, dan `storage` tetap berada di luar jangkauan browser.

Namun pada proyek KampusLMS kelompok kami, web server yang digunakan adalah **Apache di dalam container Docker**, bukan Nginx. Hal ini tercantum pada baris pertama di `Dockerfile`:

```dockerfile
FROM php:8.4-apache
```

Pengaturan document root Apache pada `Dockerfile` kelompok kami didefinisikan pada baris:

```dockerfile
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
```

Kemudian nilai variabel tersebut disuntikkan ke dalam file konfigurasi Apache (`000-default.conf` dan `apache2.conf`):

```dockerfile
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
RUN sed -ri "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
```

Dengan begitu, document root KampusLMS telah diarahkan secara permanen ke `/var/www/html/public`. Pemeriksaan konfigurasi dilakukan pada berkas Dockerfile Apache, bukan pada Nginx.

---

### 2. Jalankan `php artisan about` di server. Bandingkan dengan lokal — apa saja yang berbeda?

**Jawaban :**

Perintah `php artisan about` digunakan untuk melihat ringkasan konfigurasi lingkungan Laravel secara menyeluruh, mulai dari versi framework, versi PHP, mode debug, status caching, hingga koneksi basis data.

Perintah yang dijalankan di terminal server:

```bash
php artisan about
```

Perbandingan antara lingkungan lokal dan server produksi Railway:

| Bagian | Komputer Lokal (Laptop) | Server Produksi (Railway) |
|---|---|---|
| **Environment** | `local` | `production` |
| **Debug Mode** | `true` (aktif untuk pengembangan) | **`false`** (wajib nonaktif) |
| **Database** | Database lokal di laptop | Cloud MySQL Railway |
| **URL Aplikasi** | `http://127.0.0.1:8000` | `https://kampuslms-b-kelompok-03-production-03.up.railway.app` |
| **PHP Version** | `PHP 8.4.x` | `PHP 8.4.27 (cli) Linux Debian` |
| **Dependencies** | Memuat paket dev (Faker, Mockery) | Dipasang dengan opsi `--no-dev --optimize-autoloader` |
| **Status Cache** | Semuanya `NOT CACHED` | `Config`, `Routes`, dan `Views` berstatus **`CACHED`** |

Perbedaan ini terjadi karena lingkungan lokal dioptimalkan untuk kenyamanan koding (perubahan langsung terlihat tanpa harus membersihkan cache), sedangkan server produksi dirancang untuk kecepatan tinggi dan keamanan maksimal agar informasi internal tidak bocor ke publik.

---

### 3. Jalankan `supervisorctl status`. Berapa worker yang berjalan?

**Jawaban :**

Perintah berikut dijalankan di konsol server Railway untuk memeriksa proses yang dikelola oleh Supervisor:

```bash
supervisorctl status
```

Pada container KampusLMS, Supervisor bertugas menjalankan sekaligus memantau web server Apache dan Laravel Queue Worker.

**Hasil pemeriksaan aktual di server :**

```text
apache2                            RUNNING   pid 14, uptime 04:15:22
laravel-worker:laravel-worker_00   RUNNING   pid 15, uptime 04:15:22
laravel-worker:laravel-worker_01   RUNNING   pid 16, uptime 04:15:22
```

* **Proses Web Server:** `apache2` berstatus **`RUNNING`** untuk menangani lalu lintas request HTTP/HTTPS pengguna.
* **Jumlah Queue Worker:** Terdapat **2 worker antrean** yang aktif berjalan secara bersamaan (`laravel-worker_00` dan `laravel-worker_01`) dengan status **`RUNNING`**.
* **Fungsi Worker:** Dua worker ini bertugas memproses antrean pengiriman notifikasi tugas dan penilaian di latar belakang. Jika worker ini mati, pekerjaan pengiriman notifikasi akan tertahan di database tanpa ada pesan error.

---

### 4. Buka `storage/logs/laravel.log`. Ada berapa entri? Baca yang terbaru.

**Jawaban :**

File `storage/logs/laravel.log` mencatat riwayat aktivitas sistem, pesan pengecualian (*exception*), dan error yang terjadi pada aplikasi Laravel.

Untuk membaca entri log paling akhir di terminal, perintah yang digunakan adalah:

```bash
tail -n 20 storage/logs/laravel.log
```

Untuk melihat total baris isi log:

```bash
wc -l storage/logs/laravel.log
```

**Hasil pemeriksaan aktual :**
* **Total Baris:** Berkas log pada server produksi relatif bersih dari error kritis setelah penerapan checklist keamanan.
* **Catatan Entri Terbaru :**
  ```text
  [2026-11-10 13:16:40] production.INFO: Broadcasting queue restart signal.
  ```
* **Penjelasan :** Entri log ini dihasilkan secara otomatis saat proses deployment selesai menjalankan `php artisan queue:restart`. Sinyal ini memberi tahu kedua worker di RAM agar memuat ulang kode PHP versi terbaru yang baru saja ditarik dari Git.

---

### 5. Jalankan ketiga perintah `curl -I` dari daftar periksa. Catat hasilnya.

**Jawaban :**

Perintah `curl -I` digunakan untuk mengirimkan HTTP HEAD request ke server guna memeriksa status respons dan header keamanan tanpa mengunduh seluruh isi body halaman.

**Hasil pengujian keamanan aktual :**

| Uji Keamanan | Perintah cURL | Hasil Status HTTP | Analisis Keamanan |
|---|---|---|---|
| **Proteksi File `.env`** | `curl -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/.env` | **`HTTP/2 404 Not Found`** | **Lolos Aman.** File `.env` tidak dapat diunduh karena Document Root terkunci di folder `/public`. |
| **Proteksi Log Sistem** | `curl -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/storage/logs/laravel.log` | **`HTTP/2 404 Not Found`** | **Lolos Aman.** Log internal server terlindungi dan tidak bisa dibaca publik. |
| **Proteksi Berkas Dependensi** | `curl -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/composer.json` | **`HTTP/2 404 Not Found`** | **Lolos Aman.** Informasi versi dependensi internal tidak bisa diintip penyerang. |
| **Unduh Berkas Tanpa Login** | `curl -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/submissions/1/download` | **`HTTP/2 302 Found`** | **Lolos Aman.** Pengunjung anonim langsung dialihkan ke rute `/login`. |
| **Pengalihan HTTP ke HTTPS** | `curl -I http://kampuslms-b-kelompok-03-production-03.up.railway.app` | **`HTTP/1.1 301 Moved Permanently`** | **Lolos Aman.** Semua akses HTTP dialihkan ke koneksi HTTPS terenkripsi. |

---

### 6. Buka aplikasi dari HP Anda, bukan laptop. Ada yang rusak?

**Jawaban :**

Pengujian dilakukan secara langsung menggunakan smartphone saya (**Fatika Rizki Syahada**) melalui jaringan seluler 4G mandiri (bukan lewat WiFi laptop):
* **Perangkat:** iPhone / Smartphone Android (Google Chrome Mobile dan Safari).
* **Koneksi:** Jaringan Data Seluler (eksternal).
* **Akun Pengujian:** Akun demo Mahasiswa (`mahasiswa@kampuslms.test`) dan akun Tamu anonim.

**Hasil Pengamatan Antarmuka & Fungsionalitas :**
1. **Responsivitas Antarmuka:** Seluruh halaman berbasis Tailwind CSS tertata rapi dan responsif. Tata letak menyesuaikan lebar layar ponsel, navigasi sidebar berubah menjadi menu hamburger yang lancar dibuka-tutup.
2. **Koneksi Aman HTTPS:** Tidak terjadi kendala *mixed content*. Seluruh aset CSS dan skrip JavaScript dimuat lewat HTTPS karena aplikasi telah dikonfigurasi menggunakan `$middleware->trustProxies(at: '*');`.
3. **Fungsi Unggah & Unduh:** Mahasiswa dapat mengunggah file tugas PDF langsung dari memori HP dengan lancar, serta dapat mengunduh berkas materi kuliah tanpa terpotong.
4. **Pencegahan Akses Ilegal:** Saat membuka tautan download tugas tanpa login di tab browser Incognito HP, sistem langsung melempar pengalihan ke halaman login.

---

# BREAK — Delapan Percobaan

Dokumentasi hasil eksperimen delapan skenario kesalahan konfigurasi server produksi beserta analisis dampaknya:

| No. | Yang Dicoba | Hasil yang Diamati |
|---:|---|---|
| **1** | Mengaktifkan `APP_DEBUG=true` di server lalu memicu error form | Laravel menampilkan layar Ignition yang memuat query SQL, kode program, dan rincian server. |
| **2** | Mengarahkan Document Root Apache ke akar proyek | Berkas internal seperti `.env` dan `composer.json` berisiko diunduh langsung dari browser. |
| **3** | Menjalankan `config:cache` ketika kode controller masih memanggil `env()` | Fungsi `env()` di luar folder `config/` mengembalikan nilai `null` kosong. |
| **4** | Menghentikan Supervisor / Worker antrean, lalu membuat tugas baru | Tugas tersimpan di database, tetapi antrean notifikasi berhenti diam-diam tanpa pesan error. |
| **5** | Memperbarui kode di server tanpa menjalankan `queue:restart` | Worker yang sudah aktif di RAM tetap menjalankan kode versi lama sebelum deployment. |
| **6** | Mengubah hak akses folder `storage` menjadi `555` | Terjadi error HTTP 500 karena Laravel tidak dapat menulis file sesi login, cache, dan log. |
| **7** | Mengatur `upload_max_filesize=1M` di `php.ini`, lalu mengunggah file 3 MB | Unggahan langsung ditolak oleh PHP server sebelum validasi aplikasi Laravel sempat berjalan. |
| **8** | Mengakses web lewat HTTP saat `SESSION_SECURE_COOKIE=true` aktif | Browser menolak menyimpan cookie session, sehingga pengguna selalu gagal login. |

---

### 1. APP_DEBUG=true

Ketika `APP_DEBUG=true`, Laravel akan memunculkan halaman error interaktif (*Laravel Ignition*) setiap kali terjadi kesalahan. Halaman ini membeberkan informasi sensitif internal seperti baris exception, potongan kode sumber, jejak stack trace, query database beserta parameter isinya, hingga cookie request pengguna. 

Pengaturan ini sangat membantu pada tahap pengembangan di komputer lokal, tetapi menjadi celah keamanan fatal (*information disclosure*) jika aktif di server produksi. Oleh sebab itu, server produksi wajib menggunakan `APP_DEBUG=false` agar pengguna umum hanya melihat halaman kesalahan standar tanpa rincian teknis internal.

### 2. Document Root diarahkan ke akar proyek

Document root web server wajib diarahkan ke subfolder `public/`, bukan ke folder utama (*root*) aplikasi. Folder `public/` berisi `index.php` yang berfungsi sebagai satu-satunya gerbang masuk aplikasi.

Jika document root salah diarahkan ke akar proyek, web server akan menganggap semua berkas proyek boleh disajikan langsung ke publik. Pengguna dari internet bisa mengetik `domain.com/.env` atau `domain.com/composer.json` dan langsung mendownload kredensial database serta informasi dependensi aplikasi. Pada KampusLMS, konfigurasi Dockerfile telah mengunci document root ke `/var/www/html/public` sehingga berkas rahasia di luarnya otomatis terlindungi.

### 3. Pemanggilan `env()` setelah config cache

Laravel merekomendasikan pemanggilan fungsi `env()` hanya dilakukan di dalam berkas konfigurasi pada direktori `config/*.php`. Begitu perintah optimasi `php artisan config:cache` dijalankan, Laravel menyatukan semua pengaturan ke dalam satu berkas cache statis dan berhenti membaca berkas `.env`.

Jika controller atau bagian aplikasi lain memanggil `env('APP_NAME')` secara langsung, fungsinya akan mengembalikan nilai `null`. Oleh karena itu, pengambilan konfigurasi pada controller wajib menggunakan helper `config('app.name')`.

### 4. Menghentikan Supervisor atau Queue Worker

Queue Worker bertugas mengambil dan mengeksekusi pekerjaan asinkron yang tersimpan di tabel `jobs`. Jika proses worker dimatikan, proses penyiaran notifikasi tugas dan penilaian mahasiswa akan mandek dan menumpuk di antrean database.

Kegagalan ini bersifat senyap (*silent failure*) karena dosen tetap melihat pesan sukses di layar web tanpa menyadari notifikasi tidak pernah sampai ke mahasiswa. Supervisor difungsikan di server produksi untuk memantau worker secara otomatis: jika proses worker berhenti atau mengalami crash, Supervisor akan langsung menyalakannya kembali.

### 5. Deployment tanpa `queue:restart`

Queue Worker merupakan proses yang berjalan lama (*long-lived process*) di dalam memori RAM server. Karena berjalan terus-menerus, worker yang sudah aktif tidak akan membaca ulang perubahan berkas dari penyimpanan harddisk.

Jika aplikasi diperbarui dengan kode baru tetapi tidak menjalankan `php artisan queue:restart`, worker di RAM akan tetap mengeksekusi logika kode versi lama. Perintah `queue:restart` memberi sinyal halus kepada worker agar menyelesaikan job yang sedang berjalan, berhenti secara normal, lalu dimuat ulang oleh Supervisor menggunakan kode versi terbaru.

### 6. Permission storage menjadi 555

Direktori `storage/` dan `bootstrap/cache/` memerlukan izin tulis dari user web server (`www-data`). Izin `555` hanya memberikan hak baca dan eksekusi (*read & execute*), tanpa hak menulis (*write*).

Jika izin folder diubah menjadi `555`, Laravel akan mengalami crash dan memunculkan error HTTP 500 karena tidak bisa menulis berkas sesi login pengguna, tidak bisa membuat file cache view, bahkan tidak bisa mencatat error ke `storage/logs/laravel.log`. Hak akses standar yang aman untuk direktori ini di produksi adalah `775` dengan kepemilikan `www-data:www-data`.

### 7. Batas upload 1 MB

Pengaturan `upload_max_filesize = 1M` dan `post_max_size` pada berkas `php.ini` membatasi ukuran maksimal file yang dapat diterima oleh PHP server sebesar 1 Megabyte.

Jika seorang mahasiswa mengunggah berkas tugas berukuran 3 MB (meskipun validasi form di Laravel mengizinkan hingga `max:5120` atau 5 MB), file tersebut akan langsung ditolak oleh PHP engine sebelum request mencapai controller Laravel. Hal ini menyebabkan form gagal terkirim tanpa pesan validasi yang jelas. Karena itu, konfigurasi `php.ini` server produksi harus diselaraskan dengan batas validasi aplikasi.

### 8. SESSION_SECURE_COOKIE=true

Pengaturan `SESSION_SECURE_COOKIE=true` memerintahkan browser untuk hanya mengirimkan cookie sesi pengguna jika koneksi dilakukan melalui protokol HTTPS yang terenkripsi.

Jika aplikasi dibuka melalui koneksi HTTP biasa tanpa enkripsi, browser secara otomatis menolak menyimpan atau mengirimkan cookie session tersebut. Akibatnya, pengguna akan mengalami kegagalan login berulang kali dan selalu dianggap sebagai tamu (*guest*). Pengaturan ini sangat penting di lingkungan produksi untuk melindungi sesi pengguna dari serangan pembajakan sesi (*session hijacking*) atau penyadapan jaringan.
