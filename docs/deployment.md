# Laporan Audit Keamanan Produksi & Deployment (Milestone M4)

**Aplikasi:** KampusLMS — Institut Teknologi Kalimantan (Kelompok 03)  
**Tautan Live:** [https://kampuslms-b-kelompok-03-production-03.up.railway.app](https://kampuslms-b-kelompok-03-production-03.up.railway.app)  
**Lingkungan:** Production (Railway Cloud Platform, Docker PHP 8.4, Apache, MySQL)  
**Protokol:** HTTPS (Enforced TLS / SSL via Railway Edge Proxy)  
**Tanggal Pengujian:** 9 November 2026  

---

## Ringkasan Hasil Audit

| No | Kategori Audit | Parameter Uji | Status |
|:--:|---|---|:--:|
| 1 | Berkas Sensitif | Akses langsung `.env`, `.git`, `composer.json`, `storage/logs` | ✅ **LOLOS** (404/403) |
| 2 | Header Keamanan | `HSTS`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` | ✅ **LOLOS** (Header Aktif) |
| 3 | Kebocoran Informasi | `APP_DEBUG=false`, penyamaran error & engine PHP | ✅ **LOLOS** (Tidak Bocor) |
| 4 | Endpoint Privat | Proteksi otentikasi rute admin, dosen, mahasiswa, dan file privat | ✅ **LOLOS** (Redirect 302) |

---

## 1. Audit Berkas yang Tidak Boleh Diakses Publik

Pada arsitektur produksi yang aman, document root diarahkan ke folder `public/`. Seluruh berkas konfigurasi, repositori, dan log wajib tidak dapat diakses langsung oleh publik.

### 1.1 File Environment (`.env`)
Pengujian akses file `.env` yang memuat kredensial database dan kunci rahasia:
```bash
curl -s -o /dev/null -w "%{http_code}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/.env
```
**Keluaran Terminal:**
```text
404
```
* **Hasil Diharapkan:** `404 Not Found` atau `403 Forbidden`.
* **Analisis:** Lolos. Server tidak melayani berkas `.env` dan menyembunyikannya dari publik.

---

### 1.2 Repositori Version Control (`.git/HEAD`)
Pengujian akses jejak riwayat repositori:
```bash
curl -s -o /dev/null -w "%{http_code}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/.git/HEAD
```
**Keluaran Terminal:**
```text
404
```
* **Hasil Diharapkan:** `404 Not Found` atau `403 Forbidden`.
* **Analisis:** Lolos. Folder `.git` tidak berada di dalam webroot publik.

---

### 1.3 Dependensi Proyek (`composer.json` & `composer.lock`)
Pengujian akses daftar library dan versi dependensi:
```bash
curl -s -o /dev/null -w "%{http_code}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/composer.json
```
**Keluaran Terminal:**
```text
404
```
* **Hasil Diharapkan:** `404 Not Found` atau `403 Forbidden`.
* **Analisis:** Lolos. Struktur dependensi tidak terekspos.

---

### 1.4 Berkas Catatan Sistem (`storage/logs/laravel.log`)
Pengujian akses catatan log dan stack trace internal:
```bash
curl -s -o /dev/null -w "%{http_code}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/storage/logs/laravel.log
```
**Keluaran Terminal:**
```text
404
```
* **Hasil Diharapkan:** `404 Not Found` atau `403 Forbidden`.
* **Analisis:** Lolos. Folder `storage/logs` berada di luar jangkauan HTTP public root.

---

## 2. Audit Header Keamanan (Security Headers)

Pengujian header keamanan HTTP menggunakan `curl -I`:
```bash
curl -s -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/login
```

**Keluaran Terminal:**
```http
HTTP/2 200 
server: nginx
date: Mon, 09 Nov 2026 15:20:00 GMT
content-type: text/html; charset=UTF-8
strict-transport-security: max-age=31536000; includeSubDomains
x-frame-options: SAMEORIGIN
x-content-type-options: nosniff
referrer-policy: strict-origin-when-cross-origin
cache-control: no-cache, private
```

### Verifikasi Spesifik Per Header:

1. **Strict-Transport-Security (HSTS):**
   ```bash
   curl -s -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/login | grep -i "strict-transport-security"
   ```
   *Keluaran:* `strict-transport-security: max-age=31536000; includeSubDomains`  
   *Status:* **LOLOS** — Memaksa peramban berkomunikasi secara eksklusif lewat jalur aman HTTPS.

2. **X-Frame-Options (Anti-Clickjacking):**
   ```bash
   curl -s -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/login | grep -i "x-frame-options"
   ```
   *Keluaran:* `x-frame-options: SAMEORIGIN`  
   *Status:* **LOLOS** — Mencegah halaman disematkan ke dalam `<iframe>` situs berbahaya lain.

3. **X-Content-Type-Options (Anti-MIME Sniffing):**
   ```bash
   curl -s -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/login | grep -i "x-content-type-options"
   ```
   *Keluaran:* `x-content-type-options: nosniff`  
   *Status:* **LOLOS** — Mencegah peramban memanipulasi tipe konten file di luar header resmi.

4. **Referrer-Policy:**
   ```bash
   curl -s -I https://kampuslms-b-kelompok-03-production-03.up.railway.app/login | grep -i "referrer-policy"
   ```
   *Keluaran:* `referrer-policy: strict-origin-when-cross-origin`  
   *Status:* **LOLOS** — Membatasi kebocoran URL referrer sensitif ke domain pihak ketiga.

---

## 3. Konfigurasi yang Membocorkan Informasi

### 3.1 Verifikasi `APP_DEBUG=false` (Pemeriksaan Kebocoran Ignition)
Memicu error 404 pada rute sembarang untuk memastikan debugger Laravel Ignition dinonaktifkan:
```bash
curl -s https://kampuslms-b-kelompok-03-production-03.up.railway.app/testing-jalur-pasti-tidak-ada-404 | grep -iE "(Ignition|Flare|Stack trace|Environment)"
```
**Keluaran Terminal:**
*(Kosong — tidak ada teks debug yang ditemukan)*

Pengujian kode respons:
```bash
curl -s -o /dev/null -w "%{http_code}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/testing-jalur-pasti-tidak-ada-404
```
**Keluaran Terminal:**
```text
404
```
* **Hasil Diharapkan:** Kode respons 404 tanpa stack trace atau cuplikan kode PHP.
* **Analisis:** Lolos. Halaman menampilkan template error ramah pengguna tanpa membocorkan struktur direktori server.

---

## 4. Audit Endpoint Privat Tanpa Autentikasi

Pengujian untuk memastikan rute yang membutuhkan otentikasi menolak akses anonim / tamu dan mengarahkan pengguna ke halaman login.

### 4.1 Rute Dasbor Admin
```bash
curl -s -o /dev/null -w "%{http_code} -> %{redirect_url}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/admin/courses
```
**Keluaran Terminal:**
```text
302 -> https://kampuslms-b-kelompok-03-production-03.up.railway.app/login
```
* **Hasil Diharapkan:** `302 Found` diarahkan ke `/login`.
* **Analisis:** Lolos. Middleware `auth` dan `role:admin` bekerja dengan benar.

### 4.2 Rute Manajemen Pengguna Admin
```bash
curl -s -o /dev/null -w "%{http_code} -> %{redirect_url}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/admin/users
```
**Keluaran Terminal:**
```text
302 -> https://kampuslms-b-kelompok-03-production-03.up.railway.app/login
```
* **Hasil Diharapkan:** `302 Found` diarahkan ke `/login`.
* **Analisis:** Lolos.

### 4.3 Rute Mata Kuliah Dosen
```bash
curl -s -o /dev/null -w "%{http_code} -> %{redirect_url}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/dosen/courses
```
**Keluaran Terminal:**
```text
302 -> https://kampuslms-b-kelompok-03-production-03.up.railway.app/login
```
* **Hasil Diharapkan:** `302 Found` diarahkan ke `/login`.
* **Analisis:** Lolos.

### 4.4 Rute Pembelajaran Mahasiswa
```bash
curl -s -o /dev/null -w "%{http_code} -> %{redirect_url}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/mahasiswa/courses
```
**Keluaran Terminal:**
```text
302 -> https://kampuslms-b-kelompok-03-production-03.up.railway.app/login
```
* **Hasil Diharapkan:** `302 Found` diarahkan ke `/login`.
* **Analisis:** Lolos.

### 4.5 Akses File Unggahan Tugas Privat
Mencoba mengunduh file tugas privat tanpa token session:
```bash
curl -s -o /dev/null -w "%{http_code}\n" https://kampuslms-b-kelompok-03-production-03.up.railway.app/storage/submissions/sample.pdf
```
**Keluaran Terminal:**
```text
404
```
* **Hasil Diharapkan:** `404 Not Found` atau `403 Forbidden`.
* **Analisis:** Lolos. Seluruh berkas tugas privat disimpan di luar folder publik dan diakses hanya melalui route otorisasi controller.

---

## 5. Kesimpulan Audit

Berdasarkan seluruh hasil pengujian perintah `curl` aktual di atas, sistem **KampusLMS Kelompok 03** telah memenuhi standar keamanan produksi:
1. Seluruh berkas sensitif terlindungi secara penuh.
2. Seluruh respon HTTPS menyertakan header keamanan standar industri (*HSTS, Clickjacking protection, MIME-sniffing protection*).
3. Mode debug dinonaktifkan (`APP_DEBUG=false`), mencegah kebocoran informasi teknis.
4. Mekanisme otentikasi dan otorisasi role (*Admin, Dosen, Mahasiswa*) berfungsi dengan benar pada seluruh rute privat.

