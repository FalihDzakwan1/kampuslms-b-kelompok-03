### Nama : Indriani Anwar
#### NIM : 10241036
---
READ — Bandingkan dua jalur (30 menit)

1. Jalankan php artisan install:api. Baca perubahan yang terjadi di bootstrap/app.php.

Pengecekan setelah menjalankan : ```php artisan install:api``` maka akan muncul ```api: __DIR__.'/../routes/api.php',``` agar laravel dapat mengenal dan memuat route API sekaligus pemasangan sanctum untuk autentifikasi berbasis token.

2. Buat satu endpoint GET /api/v1/courses sederhana.

Pembuatan ```routes/api.php``` dengan mendaftarkan method, prefix, dan endpoint.

3. Bandingkan dengan CourseController versi web yang sudah ada. Tulis di catatan: apa yang sama dan apa yang berbeda di antara keduanya?

| Bagian | CourseController Web | CourseController API |
|---|---|---|
| Lokasi Controller | `app/Http/Controllers/CourseController.php` | `app/Http/Controllers/Api/CourseController.php` |
| File Route | `routes/web.php` | `routes/api.php` |
| URL Endpoint | `/courses` | `/api/v1/courses` |
| Tujuan | Menampilkan halaman course kepada pengguna melalui browser | Menyediakan data course untuk client seperti frontend terpisah atau mobile app |
| Output | HTML (Blade View) | JSON Response |
| Return Method | `return view()` | `return response()->json()` |
| Pengolahan Data | Mengambil data lalu mengirimnya ke halaman Blade | Mengambil data lalu mengirimkannya sebagai data JSON |
| Authentication | Session dan cookie | Token authentication (contoh: Laravel Sanctum) |
| CSRF Protection | Menggunakan CSRF | Tidak menggunakan CSRF seperti web route |
| Tampilan | Controller ikut mengatur halaman yang ditampilkan | Controller hanya menyediakan data, tampilan diatur oleh client |
| Pengguna Utama | Browser pengguna aplikasi web | Frontend lain, mobile app, atau sistem eksternal |

| Bagian | Penjelasan |
|---|---|
| Model | Sama-sama menggunakan model `Course` untuk mengambil data dari database |
| Fungsi | Sama-sama bertugas mengelola data course |
| Method | Dapat memiliki method yang sama seperti `index()`, `store()`, `update()`, dan `destroy()` |
| Logika Bisnis | Dapat menggunakan aturan bisnis yang sama |

CourseController Web dan CourseController API memiliki tujuan yang sama yaitu mengelola data course. Perbedaannya terdapat pada cara penyampaian data. Controller web mengembalikan halaman HTML menggunakan Blade, sedangkan controller API mengembalikan data dalam format JSON agar dapat digunakan oleh berbagai client.

4. Panggil endpoint API tanpa header Accept: application/json. Lalu dengan header itu. Catat bedanya.

| Pengujian | Request | Hasil |
|---|---|---|
| Tanpa header `Accept: application/json` | `GET /api/v1/courses` | Laravel tidak diberi informasi bahwa client meminta JSON. Jika terjadi error, response dapat berupa HTML atau redirect ke halaman login |
| Dengan header `Accept: application/json` | `GET /api/v1/courses` + header `Accept: application/json` | Laravel memberikan response dalam format JSON. Error authentication dikembalikan sebagai JSON dengan status 401 |

Header `Accept: application/json` digunakan untuk memberitahu Laravel bahwa client mengharapkan response JSON. Pada API, penggunaan header ini membantu memastikan response tetap dalam format JSON, terutama ketika terjadi error.

5. Jalankan php artisan route:list --path=api. Cocokkan dengan kontrak di spesifikasi.

---
BREAK — Tujuh kerusakan (45 menit)

| No | Pengujian yang Dilakukan | Hasil Pengamatan | Kesimpulan |
|---|---|---|---|
| 1 | Mengembalikan `response()->json(User::all())` pada endpoint uji | Response JSON menampilkan seluruh data user, termasuk field sensitif seperti hash password, email, dan kolom lain yang ada di database | Mengembalikan model mentah berbahaya karena dapat menyebabkan kebocoran data. Gunakan API Resource untuk menentukan data yang boleh dikirim |
| 2 | Menghapus middleware `auth:sanctum` dari route API lalu mengakses endpoint tanpa token | Endpoint dapat diakses tanpa autentikasi dan data dapat dilihat oleh pengguna yang tidak memiliki izin | Middleware autentikasi wajib digunakan agar endpoint tidak terbuka untuk publik |
| 3 | Memanggil endpoint yang dilindungi menggunakan token yang sudah dihapus/tidak valid | Laravel menolak request dan mengembalikan response `401 Unauthorized` dengan pesan tidak terautentikasi | Token yang tidak valid tidak dapat digunakan untuk mengakses API |
| 4 | Login sebagai mahasiswa lalu mengakses `POST /api/v1/assignments` | Request ditolak dengan status `403 Forbidden` karena user sudah login tetapi tidak memiliki hak akses | Perbedaan 401 dan 403 harus diterapkan. 401 untuk tidak terautentikasi, 403 untuk tidak memiliki izin |
| 5 | Menghapus eager loading pada endpoint daftar course lalu mengecek query melalui Telescope/Debugbar | Jumlah query meningkat karena setiap data course melakukan query tambahan untuk relasi yang dibutuhkan | Tanpa eager loading dapat terjadi masalah N+1 query yang menurunkan performa API |
| 6 | Menghapus throttle pada endpoint login lalu melakukan 50 percobaan login berturut-turut | Semua percobaan login dapat diproses tanpa pembatasan jumlah request | Endpoint login membutuhkan rate limiting untuk mengurangi risiko brute force |
| 7 | Membuat pesan error berbeda antara email tidak ditemukan dan password salah | Penyerang dapat mengetahui email mana yang terdaftar berdasarkan pesan error yang berbeda | Pesan login harus dibuat sama agar tidak terjadi user enumeration |