### Elsya Nur Aulia Handayani 
### nim.10241026 


4.3 Read → Break → Fix → Build

READ — Telusuri satu siklus form gagal (30 menit)
Tanpa AI. Buat form tambah mata kuliah, isi dengan data yang pasti tidak valid (SKS = 99), kirim, lalu jawab:

1. Method apa yang menerima request? Di controller mana?
jawab:
```php
    Route::post('/courses', [CourseController::class, 'store'])
        ->name('courses.store');
```
Request akan masuk ke: app/Http/Controllers/CourseController.php

```php
public function store(Request $request)
```
Request dari form tambah mata kuliah diterima oleh method store() pada CourseController.
Saat tombol simpan pada form tambah mata kuliah ditekan, data akan dikirim ke method store() yang ada di CourseController. Method inilah yang bertugas menerima data dari form sebelum disimpan ke database.

2. Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?
jawab:
```php
$request->validate([
    'sks' => 'required|integer|max:6'
]);
```
di dalam store(). Maka validasi terjadi saat baris itu dijalankan. validasi terjadi di dalam method store(), tepat saat Laravel menjalankan $request->validate(). Jika validasi gagal, kode setelahnya tidak dijalankan.
Validasi terjadi di dalam method store(), tepat saat Laravel menjalankan perintah validasi. Jadi sebelum data disimpan ke database, Laravel akan mengecek dulu apakah data yang dimasukkan sudah sesuai aturan atau belum. Jika ada yang salah, proses penyimpanan langsung dihentikan.

3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?
jawab:

misalnya SKS: 99
padahal aturan: max:6

Jika validasi gagal, Laravel otomatis mengembalikan pengguna ke halaman form sebelumnya. Jadi saat saya memasukkan SKS = 99 yang tidak valid, halaman akan kembali ke form tambah mata kuliah. Proses ini sudah diatur otomatis oleh Laravel sehingga tidak perlu membuat kode redirect sendiri.




4. Dari mana @error('sks') mengambil pesannya?
jawab:

@error('sks') mengambil pesan dari hasil validasi yang gagal. Saat saya mengisi nilai SKS yang tidak sesuai aturan, Laravel akan membuat pesan kesalahan lalu menyimpannya sementara di session. Setelah halaman kembali ke form, pesan tersebut ditampilkan melalui @error('sks') sehingga pengguna dapat mengetahui bagian mana yang salah.


5. Dari mana old('sks') mengambil nilainya? Berapa lama nilai itu bertahan?
jawab:

isalnya user isi: SKS = 99 Gagal validasi.

Laravel menyimpan input lama ke session.

```php 
value="{{ old('sks') }}"
```
akan menampilkan: 99 lagi.

old('sks') mengambil nilai yang sebelumnya dimasukkan oleh pengguna pada form. Saat validasi gagal, Laravel akan menyimpan data input tersebut sementara ke dalam session. Karena itu, ketika halaman kembali ke form, nilai yang tadi diisi masih tetap muncul dan tidak perlu diketik ulang. Nilai ini biasanya hanya bertahan untuk request berikutnya saja atau sampai form ditampilkan kembali setelah redirect.

6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.
Jawab:

Setelah dicek pada DevTools bagian Application → Cookies, Laravel menggunakan cookie bernama laravel_session. Cookie ini digunakan untuk menyimpan informasi session sementara, seperti data login, pesan error validasi, dan nilai input lama yang digunakan oleh old().