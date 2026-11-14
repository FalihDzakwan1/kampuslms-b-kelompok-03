# Catatan Individu Minggu - 12 - Elsya Nur Aulia Handayani (10241026)

## 12.3 Read → Break → Fix → Build

---

### READ — Bedah server (40 menit)

Setelah aplikasi kami berhasil live di server produksi Railway, saya melakukan observasi langsung ke server:

#### 1. Temukan konfigurasi Nginx/Apache untuk situs Anda. Baris mana yang menentukan document root? Ke folder apa ia mengarah?

Pada proyek KampusLMS kami yang dideploy menggunakan Docker Apache di Railway, baris yang menentukan *Document Root* ada di dalam file `Dockerfile` baris ke-27:
```dockerfile
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
```
Baris ini kemudian dimasukkan ke dalam konfigurasi Apache agar server hanya membaca folder **`public/`**.

Kenapa harus mengarah ke `public/`? Karena folder `public/` ini adalah satu-satunya area yang diizinkan untuk dibuka oleh browser dari internet. Berkas-berkas rahasia seperti file `.env` (yang berisi password database) dan seluruh kode program di folder `app/` sengaja ditaruh di luar folder `public/`. Jadi, kalau ada orang asing mencoba mengunduh `https://domain.com/.env` lewat browser, server akan langsung menolaknya dengan error 404 Not Found.

---

#### 2. Jalankan `php artisan about` di server. Bandingkan dengan lokal — apa saja yang berbeda?

Saya menjalankan perintah `php artisan about` di server Railway lalu membandingkan hasilnya dengan yang ada di laptop saya:

1. **Environment:** Di server produksi bernilai `production`, sedangkan di laptop lokal bernilai `local`.
2. **Debug Mode:** Di server statusnya `OFF` (`false`), sedangkan di laptop berstatus `ON` (`true`).
3. **URL Aplikasi:** Di server menggunakan alamat HTTPS resmi `https://kampuslms-b-kelompok-03-production-03.up.railway.app`, sedangkan di laptop menggunakan `http://127.0.0.1:8000`.
4. **Versi PHP:** Di server container menggunakan `PHP 8.4.27` Linux Debian, sedangkan di laptop saya menggunakan Laragon Windows `PHP 8.5.10`.
5. **Database:** Di server tersambung langsung ke database cloud `mysql` milik Railway, sedangkan di laptop memakai database lokal.
6. **Status Cache:** Di server produksi, config, route, dan view blade semuanya berstatus `CACHED` supaya aplikasinya cepat, sedangkan di laptop semuanya `NOT CACHED` agar kodingan yang baru diubah bisa langsung terlihat.

---

#### 3. Jalankan `supervisorctl status`. Berapa worker yang berjalan?

Ketika saya mengetik perintah `supervisorctl status` di terminal server Railway, hasilnya menampilkan:
```text
apache2                            RUNNING   pid 14, uptime 0:24:18
laravel-worker:laravel-worker_00   RUNNING   pid 15, uptime 0:24:18
laravel-worker:laravel-worker_01   RUNNING   pid 16, uptime 0:24:18
```
Dari sini terlihat ada **2 worker antrean** yang sedang aktif berjalan (`laravel-worker_00` dan `laravel-worker_01`) dengan status **RUNNING**, ditambah web server Apache yang juga aktif. Dua worker ini bertugas di latar belakang untuk mengirimkan notifikasi tugas dan nilai ke mahasiswa tanpa membuat website dosen menjadi lambat.

---

#### 4. Buka `storage/logs/laravel.log`. Ada berapa entri? Baca yang terbaru.

Saya membuka file `storage/logs/laravel.log` di dalam server. Karena baru saja dideploy ulang, isi lognya masih sedikit dan tidak ada catatan error yang membahayakan.

Entri log yang paling baru tercatat adalah:
```text
[2026-11-10 13:16:40] production.INFO: Broadcasting queue restart signal.
```
Catatan ini muncul otomatis dari skrip `deploy.sh` saat proses deployment selesai. Tujuannya untuk menyuruh worker antrean memuat ulang kode PHP terbaru yang baru saja ditarik dari Git.

---

#### 5. Jalankan ketiga perintah `curl -I` dari daftar periksa. Catat hasilnya.

Saya mengetes 3 perintah pengujian keamanan cURL langsung dari terminal laptop ke alamat server produksi:

1. **Uji file `.env`:**
   ```bash
   curl.exe -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/.env
   ```
   * **Hasil:** `HTTP/2 404` (Lolos, file `.env` tidak bisa dibuka dari internet).
2. **Uji file log sistem (`laravel.log`):**
   ```bash
   curl.exe -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/storage/logs/laravel.log
   ```
   * **Hasil:** `HTTP/2 404` (Lolos, catatan log error tidak bocor ke publik).
3. **Uji download tugas mahasiswa tanpa login:**
   ```bash
   curl.exe -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/submissions/1/download
   ```
   * **Hasil:** `HTTP/2 302 Found` (Lolos, pengunjung tanpa login langsung dialihkan ke halaman login `/login`).

---

#### 6. Buka aplikasi dari HP Anda, bukan laptop. Ada yang rusak?

Saya mencoba membuka aplikasi website langsung dari HP Android saya (Chrome Mobile) menggunakan kuota data seluler 4G, bukan lewat Wi-Fi laptop.

* **Hasilnya:** **Semuanya berjalan lancar dan tidak ada tampilan yang rusak.**
* Tampilan Tailwind CSS otomatis menyesuaikan ukuran layar HP.
* Semua warna CSS dan tombol JavaScript langsung muncul sempurna tanpa ada error *mixed content*, karena server sudah kami atur dengan `$middleware->trustProxies(at: '*');` supaya selalu membaca koneksi HTTPS.
* Form pengumpulan tugas dan tombol penilaian mudah ditekan lewat layar sentuh, dan tabel rekap nilai bisa digeser ke samping dengan rapi tanpa merusak tampilan.

---

### BREAK — Delapan percobaan (50 menit)

Dokumentasi hasil eksperimen ketika saya sengaja mencoba merusak setelan server produksi untuk melihat risikonya:

| # | Yang dicoba | Yang harus Anda amati | Penjelasan Teknis & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Set `APP_DEBUG=true`, picu error di halaman yang menerima form (tambahkan `throw new \Exception('uji debug');` di controller penyimpan tugas, lalu kirim formnya), buka dari jendela penyamaran | Isi form, cookie session, query SQL, dan source code Anda di layar | **Sangat Berbahaya karena Membocorkan Password.** Saat `APP_DEBUG=true`, Laravel akan memunculkan layar error merah (Laravel Ignition). Layar ini menampilkan isi file `.env` (termasuk password database dan APP_KEY), query SQL, dan kodingan kita secara terbuka ke siapa pun yang membuka web. Karena itu di server produksi wajib dimatikan (`false`). |
| **2** | Arahkan document root Nginx/Apache ke akar proyek, hapus sementara blok proteksi berkas titik, lalu akses `/.env` | Seluruh rahasia terunduh | **File Rahasia Langsung Terunduh.** Kalau Document Root salah diarahkan ke folder utama proyek (bukan ke `public/`), web server menganggap semua file kodingan boleh dilihat orang. Akibatnya, siapa saja bisa mengetik `domain.com/.env` di browser dan langsung men-download seluruh password database kita. |
| **3** | Jalankan `config:cache` dengan `env()` masih terpanggil di controller | Nilai `null` di produksi | **Kode Jadi Salah Membaca Data.** Begitu `config:cache` dijalankan, Laravel menyatukan semua setelan ke dalam file cache dan berhenti membaca file `.env`. Kalau ada controller yang memanggil `env()`, hasilnya bakal jadi `null` kosong. Makanya di controller kita wajib selalu memakai `config('app.name')`, bukan `env()`. |
| **4** | Hentikan Supervisor, buat tugas baru lewat akun dosen | Notifikasi berhenti tanpa jejak | **Kegagalan Diam-diam (*Silent Failure*).** Dosen merasa tugasnya berhasil disimpan karena di layar muncul notifikasi sukses. Tapi antrean kirim notifikasi cuma menumpuk di tabel `jobs` database. Karena worker-nya mati, notifikasi dan email tidak pernah sampai ke mahasiswa sama sekali tanpa ada pesan error apa pun. |
| **5** | Deploy kode baru tanpa `queue:restart` | Worker menjalankan kode lama | **Worker Masih Menjalankan Kodingan Lama.** Worker antrean itu menyala terus di memori RAM dan tidak pernah membaca ulang file dari harddisk. Jadi walaupun kodingan di server sudah di-update dari Git, worker di RAM masih menjalankan kodingan versi lama sebelum di-restart. Makanya perintah `queue:restart` wajib dijalankan setiap kali deploy. |
| **6** | Set izin storage menjadi 555 (`chmod -R 555 storage`) | Error 500, dan lognya pun tidak bisa ditulis | **Aplikasi Rusak Total (Error 500).** Izin 555 membuat folder `storage` cuma bisa dibaca, tidak boleh ditulisi. Akibatnya web server Apache (`www-data`) tidak bisa menyimpan file sesi login pengguna, tidak bisa membuat cache, bahkan tidak bisa menulis pesan error ke `laravel.log`, sehingga website langsung mati total. |
| **7** | Set `upload_max_filesize=1M` di `php.ini`, unggah berkas tugas berukuran 3 MB | Ditolak langsung oleh server sebelum mencapai router Laravel | **Ditolak oleh PHP Sebelum Masuk Aplikasi.** Kalau setelan di `php.ini` cuma 1 MB, maka file tugas 3 MB langsung ditolak mentah-mentah oleh server sebelum kodingan controller Laravel kita sempat memeriksanya. Mahasiswa bakal bingung karena form tugas mental tanpa ada pesan peringatan yang jelas. |
| **8** | Akses situs lewat `http://` setelah `SESSION_SECURE_COOKIE=true` diaktifkan | Session tidak terbentuk | **Gagal Login Terus-menerus.** Aturan `SESSION_SECURE_COOKIE=true` menyuruh browser untuk hanya mau menyimpan cookie sesi jika koneksinya lewat HTTPS yang aman. Kalau pengguna membuka web lewat HTTP biasa, browser menolak menyimpan cookie sesi tersebut, sehingga pengguna bakal selalu gagal login dan dianggap sebagai tamu. |
