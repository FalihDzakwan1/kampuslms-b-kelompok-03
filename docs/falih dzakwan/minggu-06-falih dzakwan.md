# Minggu 6 - Falih Dzakwan

## READ


**1. Perubahan saat `php artisan install:api` dijalankan**

Pada Laravel 11/12, file konfigurasi rute API tidak tersedia secara default. Menjalankan perintah ini akan membuatkan file `routes/api.php` dan mendaftarkannya secara otomatis ke dalam `bootstrap/app.php` pada blok `withRouting(api: __DIR__.'/../routes/api.php')`.

**2 & 3. Perbandingan `CourseController` versi Web dan versi API**

- **Yang sama**: Keduanya mengambil data dari model Eloquent yang sama (`Course::class`), menggunakan relasi tabel yang sama, serta dilindungi oleh aturan otorisasi atau Policy yang sama.
- **Yang Berbeda** Controller Web mem-passing data ke fungsi `view()` untuk di-render menjadi halaman HTML yang ditujukan kepada pengguna. Sebaliknya, Controller API mengembalikan format JSON yang bersih dari tag HTML, seringkali dengan cara dibungkus menggunakan Eloquent Resource seperti `CourseResource::collection(...)` agar lebih rapi untuk dikonsumsi oleh aplikasi lain.

**4. Memanggil Endpoint API dengan dan tanpa header `Accept: application/json`**
- **Tanpa Header:** Jika terjadi error misalnya token tidak valid atau validasi form gagal, server mungkin kebingungan dan mencoba me-redirect kita ke halaman web HTML misal halaman `/login`, yang mana akan mengacaukan aplikasi *client*.
- **Dengan Header `Accept: application/json`:** Header ini yang memberitahu Laravel. Sehingga, semua respon, termasuk jika terjadi error seperti `401 Unauthenticated` atau error form `422 Unprocessable Entity`, akan selalu dibalas menggunakan format JSON secara konsisten.

**5. Mencocokkan `php artisan route:list --path=api` dengan Kontrak Spesifikasi**

Perintah ini berguna untuk memfilter dan menampilkan semua endpoint yang menggunakan prefix `/api`. Jadi bisa memastikan rute mana saja yang terlindungi oleh *middleware* Sanctum (`auth:sanctum`) dan memastikan verb (GET, POST, PUT, DELETE).

---

## BREAK

| # | Yang Dicoba | Apa yang Terjadi | Kesimpulan |
|---|---|---|---|
| 1 | Mengembalikan `response()->json(User::all())` dan menonaktifkan `$hidden` di model `User`. | **Bahaya Fatal:** Email seluruh user terekspos. Dan ketika `$hidden` dihapus, **Hash Password** dan `remember_token` ikut tampil di layar JSON! | **Jangan pernah melempar "Model Mentah" langsung ke API** dan selalu gunakan **API Resource** sebagai filter yang menentukan kolom mana saja yang aman dipublikasikan. |
| 2 | Menghapus middleware `auth:sanctum` dari grup route dan memanggilnya tanpa token. | Data yang seharusnya privat dan tertutup seperti daftar nilai atau kelas menjadi **bocor terbuka ke publik**. Siapa saja bisa melihatnya tanpa login. | Middleware `auth:sanctum` untuk keamanan otentikasi AIP. Jika ini terhapus, maka pertahanan otentikasi API akan hancur. |
| 3 | Memanggil endpoint terlindungi dengan token yang sudah dihapus. | Server API menolak *request* dan memberikan response error **401 Unauthenticated**. | Laravel Sanctum selalu memvalidasi token dari database di setiap request. Token yang dihapus saat *logout* tidak akan bisa dipakai lagi. |
| 4 | Login sebagai **mahasiswa**, lalu melakukan POST `api/v1/assignments`. | Muncul pesan error **403 Forbidden** / *This action is unauthorized* (Bukan 401). | **401** berarti eror yang terjadi ketika pengguna belum login dan tidak bisa dikenali oleh sistem. **403** berarti eror yang terjadi ketika kita sudah login, tetapi tidak punya hak akses untuk membuka halaman tertentu dan akan Gagal di Policy/Gate. |
| 5 | Menghapus penulisan *eager loading* (`with(...)`) pada daftar mata kuliah API. | Jika dilihat di *Debugbar*, terjadi **N+1 Query Problem**. Laravel memanggil puluhan query berulang ke database per satu rute API. | Eager loading sangat penting di API karena jika tidak dipakai, API akan melambat dan membebani memori server ketika datanya membesar. |
| 6 | Menghapus pengaturan `throttle` dari *route* Login, lalu melakukan 50 percobaan. | Server menerima dan memproses 50 tebakan sandi berturut-turut tersebut tanpa ada peringatan atau pemblokiran waktu tunggu (*cooldown*). | Tanpa *throttle*, pelaku peretasan bisa melakukan serangan **Brute Force** dengan menebak jutaan sandi per menit sampai akun jebol. |
| 7 | Membuat pesan error login **berbeda** (misal: "Email tidak ditemukan" dan "Password salah"). | Peretas mengetahui secara pasti bahwa salah satu data yang dimasukkan benar, misalnya email yang dimasukkan benar namun password salah, si peretas tinggal mencari password yang benar saja. | Terjadi ancaman **User Enumeration**. Sangat berbahaya. Pesan error kegagalan login WAJIB di buat generik misal: *"Email atau kata sandi tidak cocok"* agar peretas tidak tahu celahnya di mana. |
