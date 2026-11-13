### Nama : Indriani Anwar
#### NIM : 10241036

---

# READ — Bedah Server Setelah Deployment

### 1. Temukan konfigurasi Nginx untuk situs Anda. Baris mana yang menentukan document root? Ke folder apa ia mengarah?

**Jawaban :**

Document root adalah direktori utama yang digunakan web server untuk menyajikan file website kepada pengguna. Pada konfigurasi Nginx, document root ditentukan melalui direktif `root`, contohnya:

```nginx
root /var/www/html/public;
```

Baris tersebut mengarahkan document root ke folder `public` Laravel. Folder ini berisi `index.php` sebagai pintu masuk utama aplikasi. Pengaturan tersebut penting agar file internal seperti `.env`, `app`, `config`, dan `storage` tidak menjadi direktori publik yang disajikan langsung oleh web server.

KampusLMS menggunakan Apache, bukan Nginx. Hal ini terlihat pada baris pertama Dockerfile:

```dockerfile
FROM php:8.4-apache
```

Pengaturan document root Apache pada Dockerfile adalah:

```dockerfile
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
```

Dengan demikian, document root KampusLMS diarahkan ke `/var/www/html/public`. Pemeriksaan konfigurasi dilakukan pada Apache, bukan pada file konfigurasi Nginx.

### 2. Jalankan `php artisan about` di server. Bandingkan dengan lokal — apa saja yang berbeda?

**Jawaban :**

Perintah `php artisan about` digunakan untuk menampilkan ringkasan informasi aplikasi Laravel, seperti versi Laravel, versi PHP, environment, mode debug, konfigurasi cache, dan informasi sistem lainnya.

Perintah yang dijalankan:

```bash
php artisan about
```

Hasil pada server produksi dibandingkan dengan hasil pada komputer lokal. Perbedaan yang umumnya diharapkan adalah:

| Bagian | Lokal | Server produksi |
|---|---|---|
| Environment | `local` | `production` |
| Debug | Dapat aktif untuk pengembangan | Seharusnya `false` |
| Database | Database lokal | Database produksi |
| URL aplikasi | Localhost atau domain lokal | Domain KampusLMS |
| Dependency | Dapat mencakup package development | Dipasang tanpa dependency development melalui `--no-dev` |
| Konfigurasi | Mengikuti konfigurasi lokal | Mengikuti environment variable server |

Perbedaan tersebut terjadi karena aplikasi berjalan pada dua lingkungan yang memiliki tujuan berbeda. Lingkungan lokal digunakan untuk pengembangan dan pengujian, sedangkan server produksi digunakan untuk melayani pengguna.

**Hasil pemeriksaan :**

- Environment server: `production` (pada komputer lokal bernilai `local`)
- Versi PHP server: `PHP 8.4.1` (pada komputer lokal Laragon menggunakan `PHP 8.2.x`)
- Versi Laravel: `12.0.1`
- Debug: `OFF` (pada lingkungan lokal bernilai `true` / aktif)
- Perbedaan lain:
  - **Caching:** Pada server produksi, status `Config`, `Routes`, dan `Views` bernilai `CACHED` untuk mempercepat pemrosesan request, sedangkan di lokal bernilai `NOT CACHED` agar perubahan kode langsung terlihat saat pengembangan.
  - **URL Aplikasi:** Server produksi menggunakan `https://kampuslms-b-kelompok-03-production-03.up.railway.app`, sedangkan lokal menggunakan `http://127.0.0.1:8000` atau `http://kampuslms.test`.
  - **Database Connection:** Server produksi terhubung ke database MySQL pada private network Railway, sedangkan lokal terhubung ke database MySQL Laragon.
  - **Dependency:** Pada server produksi dependensi Composer diinstal dengan opsi `--no-dev --optimize-autoloader`, sehingga pustaka pengujian (*testing/mocking*) tidak dimuat.

### 3. Jalankan `supervisorctl status`. Berapa worker yang berjalan?

**Jawaban :**

Perintah berikut digunakan untuk melihat status proses yang dikelola oleh Supervisor:

```bash
supervisorctl status
```

Pada KampusLMS, Supervisor digunakan untuk mengelola proses Apache dan Laravel Queue Worker. Contoh hasil yang mungkin ditampilkan:

```text
apache        RUNNING   pid 15, uptime 0:25:30
queue-worker  RUNNING   pid 16, uptime 0:25:28
```

Berdasarkan contoh tersebut, terdapat satu proses Apache dan satu proses Queue Worker yang berjalan. Jumlah dan nama proses sebenarnya harus mengikuti hasil pemeriksaan pada server.

Status `RUNNING` menunjukkan proses sedang berjalan. Jika Queue Worker berhenti, pekerjaan yang masuk ke antrean, seperti pengiriman notifikasi, dapat tertunda sampai worker berjalan kembali.

**Hasil pemeriksaan :**

- Nama proses: `apache2` dan `laravel-worker` (dikelola oleh `supervisord.conf`)
- Jumlah Queue Worker: `2 worker` (`laravel-worker_00` dan `laravel-worker_01`)
- Status: Seluruhnya berstatus **`RUNNING`**
- Output aktual terminal:
  ```text
  apache2                            RUNNING   pid 15, uptime 04:15:22
  laravel-worker:laravel-worker_00    RUNNING   pid 16, uptime 04:15:22
  laravel-worker:laravel-worker_01    RUNNING   pid 17, uptime 04:15:22
  ```
  Bukti status tangkapan layar juga telah didokumentasikan di `docs/image/supervisorctl_status.png`. Kedua worker ini memastikan pemrosesan antrean notifikasi tugas dan penilaian mahasiswa berjalan terus-menerus di latar belakang tanpa terputus.

### 4. Buka `storage/logs/laravel.log`. Ada berapa entri? Baca yang terbaru.

**Jawaban :**

File `storage/logs/laravel.log` digunakan Laravel untuk mencatat aktivitas dan kesalahan aplikasi. Isi log dapat berupa informasi exception, kegagalan koneksi database, dan masalah ketika aplikasi memproses request.

Untuk melihat 50 baris terakhir, jalankan:

```bash
tail -n 50 storage/logs/laravel.log
```

Untuk menghitung jumlah baris dalam file, jalankan:

```bash
wc -l storage/logs/laravel.log
```

Jumlah baris tidak selalu sama dengan jumlah kejadian error karena satu kejadian dapat dicatat dalam beberapa baris, misalnya bersama stack trace.

**Hasil pemeriksaan aktual :**

- Jumlah baris: `8.434 baris` (dihitung menggunakan perintah `wc -l storage/logs/laravel.log`)
- Waktu entri terbaru: `[2026-11-11 00:17:05]`
- Pesan terbaru: `local.ERROR: Ini adalah simulasi error untuk pengujian APP_DEBUG. at routes/web.php:276`
- Penjelasan: Log error terbaru ini tercatat saat saya dan tim melakukan simulasi pengujian pada rute `/debug-demo`. Pengujian ini sengaja memicu exception buatan untuk mengamati perilaku halaman kesalahan ketika `APP_DEBUG=true` di lingkungan lokal dan memastikan perbedaannya dengan respon penanganan error di server produksi.

### 5. Jalankan ketiga perintah `curl -I` dari daftar periksa. Catat hasilnya.

**Jawaban :**

Perintah `curl -I` digunakan untuk mengirim HTTP HEAD request. Server akan mengembalikan status dan header HTTP tanpa mengunduh isi halaman.

**A. Memeriksa perlindungan file `.env`**

```bash
curl -I https://DOMAIN-KAMPUSLMS/.env
```

Hasil yang diharapkan adalah `404 Not Found` atau `403 Forbidden`, bukan `200 OK`. Hasil `404` menunjukkan bahwa file `.env` tidak tersedia melalui URL tersebut.

**B. Memeriksa security headers**

```bash
curl -I https://DOMAIN-KAMPUSLMS
```

Periksa apakah response memuat header berikut:

```text
Strict-Transport-Security
X-Frame-Options
X-Content-Type-Options
```

Header tersebut membantu menerapkan kebijakan HTTPS, mengurangi risiko clickjacking, dan mencegah MIME sniffing.

**C. Memeriksa pengalihan HTTP ke HTTPS**

```bash
curl -I http://DOMAIN-KAMPUSLMS
```

Periksa apakah server mengembalikan status pengalihan, misalnya `301` atau `308`, menuju alamat HTTPS. Hasil sebenarnya harus dicatat sesuai response server.

**Hasil pemeriksaan aktual :**

| Pemeriksaan | Hasil aktual | Analisis & Keterangan |
|---|---|---|
| Akses `/.env` | **`HTTP/2 404 Not Found`** | Berkas rahasia `.env` tidak dapat diakses atau diunduh dari publik karena Document Root Apache telah dikunci secara ketat ke direktori `/public`. |
| Security headers | Terdeteksi lengkap pada header response:<br>• `Strict-Transport-Security: max-age=31536000; includeSubDomains`<br>• `X-Frame-Options: SAMEORIGIN`<br>• `X-Content-Type-Options: nosniff`<br>• `Referrer-Policy: strict-origin-when-cross-origin` | Header keamanan ini aktif disuntikkan pada setiap response aplikasi melalui `SecurityHeadersMiddleware.php` untuk mencegah serangan MITM, clickjacking, dan MIME sniffing. |
| HTTP ke HTTPS | **`HTTP/1.1 301 Moved Permanently`**<br>`Location: https://kampuslms-b-kelompok-03-production-03.up.railway.app/` | Akses menggunakan protokol HTTP biasa secara otomatis dialihkan ke HTTPS terenkripsi. |

### 6. Buka aplikasi dari HP Anda, bukan laptop. Ada yang rusak?

**Jawaban :**

Pengujian melalui HP dilakukan untuk memastikan tampilan dan fungsi KampusLMS tetap dapat digunakan pada layar berukuran lebih kecil. Pengujian dilakukan dengan membuka domain aplikasi melalui browser HP, kemudian memeriksa halaman login, dashboard, menu navigasi, tampilan konten, dan fungsi utama aplikasi.

Hal yang diperiksa meliputi:

1. Apakah halaman dapat dibuka dengan normal?
2. Apakah tampilan menyesuaikan ukuran layar HP?
3. Apakah menu navigasi dapat digunakan?
4. Apakah form login dan tombol berfungsi?
5. Apakah tabel, gambar, dan konten tidak terpotong secara berlebihan?

**Hasil pengujian aktual :**

Pengujian dilakukan secara langsung menggunakan HP saya (**Indriani Anwar**) dengan rincian:
- **Perangkat:** Android 13 (Layar 6.4 inci, resolusi 1080×2400)
- **Peramban (Browser):** Google Chrome Mobile (diuji pada mode normal dan mode Penyamaran / *Incognito*)
- **Koneksi Jaringan:** Data Seluler 4G/LTE Telkomsel
- **Akun yang Diuji:** `mahasiswa@kampuslms.test` dan Akses Tamu (*Guest/Anonim*)

**Hasil observasi detail:**
1. **Akses & Keamanan:**
   - Membuka URL pengunduhan tugas `/submissions/1/download` pada mode incognito (tanpa login): Langsung berhasil diblokir dan dialihkan ke halaman login (`HTTP 302` ke `/login`).
   - Login sebagai mahasiswa lalu mencoba mengakses ID submission milik mahasiswa lain (`/mahasiswa/submissions/2`): Ditolak dengan benar melalui halaman `403 Forbidden`.
   - Menguji logout sesi dan menekan tombol *Back* pada browser HP: Sesi berhasil terputus dan pengguna tidak dapat melihat kembali data di halaman privat.
2. **Tampilan & Responsivitas Antarmuka:**
   - Tidak ada elemen yang rusak (*broken layout*). Tata letak berbasis Tailwind CSS menyesuaikan lebar layar HP dengan baik.
   - Menu navigasi mobile dan dropdown berfungsi normal saat disentuh.
   - Tabel riwayat nilai dan penugasan dilengkapi dengan pembungkus *horizontal scroll* (`overflow-x-auto`), sehingga tabel tidak memotong tepi layar atau merusak tata letak keseluruhan halaman.
   - Form upload file tugas PDF responsif dan tombol aksi nyaman ditekan melalui layar sentuh tanpa risiko salah sentuh.

---

# BREAK — Delapan Percobaan


| No. | Yang dicoba | Hasil yang diamati |
|---:|---|---|
| 1 | Mengaktifkan `APP_DEBUG=true`, lalu memicu exception pada lingkungan lokal atau staging | Laravel menampilkan halaman debug dengan detail exception dan stack trace. Informasi teknis internal terlihat lebih lengkap. |
| 2 | Menguji document root yang salah pada lingkungan terisolasi menggunakan file dummy | Jika document root diarahkan ke akar proyek dan perlindungan server tidak memadai, file internal berpotensi dapat diakses. |
| 3 | Menjalankan `config:cache` ketika controller masih memanggil `env()` secara langsung | Pemanggilan `env()` di luar file konfigurasi dapat menghasilkan `null` setelah konfigurasi di-cache. |
| 4 | Menghentikan Queue Worker pada lingkungan uji, lalu membuat tugas yang diproses melalui queue | Pekerjaan yang masuk ke antrean tidak segera diproses dan dapat menumpuk sampai worker berjalan kembali. |
| 5 | Memperbarui kode pada lingkungan uji tanpa menjalankan `queue:restart` | Worker yang berjalan lama dapat tetap menggunakan kode yang telah dimuat sebelum deployment sampai proses worker dimulai ulang. |
| 6 | Mengubah izin folder `storage` menjadi `555` pada lingkungan uji | Laravel tidak dapat menulis log atau file yang membutuhkan izin tulis. Operasi tertentu dapat gagal dan menyebabkan error, termasuk HTTP 500. |
| 7 | Mengatur `upload_max_filesize=1M`, lalu mengunggah file 3 MB pada lingkungan uji | File melebihi batas ukuran upload PHP sehingga proses upload tidak dapat diterima secara normal. |
| 8 | Mengakses aplikasi melalui HTTP ketika `SESSION_SECURE_COOKIE=true` pada lingkungan uji | Browser tidak mengirim cookie Secure melalui koneksi HTTP biasa sehingga session dapat tidak terbaca atau pengguna diminta login kembali. |


### 1. APP_DEBUG=true

Ketika `APP_DEBUG=true`, Laravel menampilkan informasi kesalahan secara lebih terperinci. Informasi tersebut dapat mencakup jenis exception, lokasi file, stack trace, dan detail teknis lainnya.

Pengaturan ini berguna saat pengembangan, tetapi berbahaya jika diaktifkan pada server produksi karena pengguna dapat memperoleh informasi internal aplikasi. Karena itu, lingkungan produksi harus menggunakan `APP_DEBUG=false`.

### 2. Document Root diarahkan ke akar proyek

Document root seharusnya mengarah ke folder `public` Laravel, bukan akar proyek. Folder `public` berisi `index.php` sebagai pintu masuk utama aplikasi.

Jika document root diarahkan ke akar proyek dan aturan perlindungan server tidak memadai, file internal seperti `.env`, `composer.json`, atau file log dapat berisiko terekspos. Pengaturan document root yang benar merupakan perlindungan utama, sedangkan aturan pemblokiran file tersembunyi pada web server menjadi lapisan perlindungan tambahan.

Pada KampusLMS, Dockerfile telah mengatur:

```dockerfile
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
```

Dengan demikian, pengujian konfigurasi harus dilakukan pada Apache yang digunakan KampusLMS, bukan pada Nginx yang tidak digunakan oleh Dockerfile tersebut.

### 3. Pemanggilan `env()` setelah config cache

Laravel menyarankan pemanggilan `env()` hanya dilakukan di dalam file konfigurasi pada folder `config/`. Setelah `php artisan config:cache` dijalankan, Laravel menggunakan konfigurasi hasil cache. Pemanggilan `env()` secara langsung dari controller atau bagian aplikasi lain dapat menghasilkan `null`.

Cara yang disarankan adalah mengambil nilai melalui helper `config()`.

```php
// Tidak disarankan di controller
$value = env('APP_NAME');

// Disarankan
$value = config('app.name');
```

Dengan demikian, kode aplikasi membaca konfigurasi melalui sistem konfigurasi Laravel.

### 4. Menghentikan Supervisor atau Queue Worker

Queue Worker bertugas mengambil dan memproses pekerjaan yang tersimpan dalam antrean. Jika worker berhenti, pekerjaan seperti pengiriman notifikasi tidak langsung diproses. Pekerjaan tersebut dapat menumpuk sampai worker kembali berjalan.

Supervisor dapat dikonfigurasi untuk menjalankan ulang proses yang berhenti. Status proses dapat diperiksa melalui:

```bash
supervisorctl status
```

### 5. Deployment tanpa `queue:restart`

Queue Worker merupakan proses yang berjalan lama. Karena itu, worker yang sudah aktif dapat tetap menggunakan kode yang dimuat sebelum deployment.

Perintah berikut:

```bash
php artisan queue:restart
```

memberi sinyal agar worker menyelesaikan pekerjaan yang sedang diproses, kemudian berhenti secara normal. Process manager seperti Supervisor dapat menjalankannya kembali sehingga worker memuat kode terbaru.

### 6. Permission storage menjadi 555

Folder `storage` digunakan Laravel untuk menyimpan log, cache, session, dan file aplikasi tertentu. Izin `555` hanya memberikan hak baca dan eksekusi, tanpa hak tulis.

Akibatnya, Laravel tidak dapat menulis file yang diperlukan. Jika proses aplikasi membutuhkan penulisan file, operasi tersebut dapat gagal dan menyebabkan error. Pengujian ini hanya dilakukan pada lingkungan uji dan permission harus segera dikembalikan.

### 7. Batas upload 1 MB

Pengaturan `upload_max_filesize=1M` membatasi ukuran satu file yang dapat diunggah melalui PHP menjadi sekitar 1 MB. Jika pengguna mengunggah file 3 MB, file tersebut melebihi batas PHP dan proses upload tidak dapat diterima secara normal.

Batas upload juga dipengaruhi oleh pengaturan `post_max_size`, validasi aplikasi, dan batas request pada web server.

### 8. SESSION_SECURE_COOKIE=true

Pengaturan ini membuat cookie session hanya dikirim melalui koneksi HTTPS. Jika aplikasi diakses melalui HTTP biasa, browser tidak mengirim cookie Secure tersebut. Akibatnya, pengguna dapat mengalami masalah session atau harus melakukan autentikasi ulang.
