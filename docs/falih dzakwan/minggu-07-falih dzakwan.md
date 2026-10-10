# Minggu 7 - Falih Dzakwan

## READ

**1. Berkas mana yang menangani POST login? Method apa?**

Proses `POST` dari form login ditangani oleh **`LoginController`** (`app/Http/Controllers/LoginController.php`). Method yang mengeksekusinya adalah method `login()`.

**2. Di baris mana `Auth::attempt()` atau setaranya dipanggil?**

`Auth::attempt()` dipanggil di dalam method `login()` pada controller tersebut. Fungsi ini bertugas mencocokkan inputan kredensial seperti email dan password milik user dengan data di dalam database.

**3. Temukan `session()->regenerate()`. Kenapa ia ada di situ?**

Fungsi `session()->regenerate()` dipanggil tepat setelah `Auth::attempt()` berhasil. Tujuannya adalah untuk **mengganti ID session lama dengan ID session baru**. Ini dilakukan untuk mencegah serangan **Session Fixation** yang dimana hacker menanamkan ID session miliknya ke browser korban sebelum korban login.

**4. Di mana kata sandi di-hash? Cari cast `hashed` di model User.**

Pada Laravel 11/12, mekanisme hashing otomatis diatur di dalam model `User` (`app/Models/User.php`) di dalam method `casts()`. Dengan mendefinisikan `'password' => 'hashed'`, Laravel secara otomatis akan mengubah teks polos menjadi *hash* setiap kali atribut diisi password sebelum menyimpannya ke database.

**5. Buka DevTools → Cookies sebelum dan sesudah login. Bandingkan nilai cookie session.**

Sebelum login, nilai cookie `laravel_session` misalnya `abc123xyz...`. Tepat setelah login berhasil, nilai tersebut berubah total menjadi string acak yang baru. Ini adalah bukti bahwa `session()->regenerate()` bekerja mengamankan sesi.

**6. Logout, lalu tekan tombol back. Apa yang terjadi? Kenapa?**

Saat menekan tombol back, halaman mungkin terlihat sekilas karena di-cache oleh memori browser. Namun, jika merefresh halaman atau mengklik tombol apa pun, kita akan dilempar ke halaman login (401 Unauthenticated). Ini terjadi karena Middleware `auth` memblokir akses ke rute-rute dalam, dan session login sebelumnya sudah dihancurkan saat proses Logout (`session()->invalidate()`).

---

## REAK

| # | Yang Dicoba | Apa yang Terjadi | Kesimpulan |
|---|---|---|---|
| 1 | Hapus `session()->regenerate()` dari method login. | Nilai cookie `laravel_session` tetap sama persis antara sebelum dan sesudah login. | Celah **Session Fixation** terbuka untuk peretas agar bisa mengatur *session ID* di perangkat korban, lalu mengambil alih akunnya setelah korban login. |
| 2 | Hapus `Gate::authorize()` dari Controller (update), tapi biarkan `@can` di Blade (UI). | Tombol "Edit" memang hilang dari layar. Tapi ketika ditembak lewat cURL atau Postman ke URL `PUT /courses/...`, **data berhasil diubah!** | Directive `@can` di Blade HANYA menyembunyikan antarmuka saja namun tidak mengamankan data. Otorisasi sebenarnya wajib dilakukan di backend atau Controller menggunakan `Gate::authorize()`. |
| 3 | Sebagai Dosen A, kirim request `PUT` ke mata kuliah Dosen B lewat `curl`. | Sistem menolak mentah-mentah dan mengembalikan error **403 Forbidden**. | Jika sudah mengimplementasikan `CoursePolicy` , sistem keamanan tingkat objek berfungsi sempurna menahan peretas dengan role yang sama. |
| 4 | Sebagai mahasiswa, buka halaman submission (Tugas) milik mahasiswa lain dengan mengubah ID di URL (IDOR minggu 5). | Sistem memblokir dan mengembalikan error **403 Forbidden** atau 404 jika di-scope. | Celah **IDOR** dari minggu 5 sudah berhasil ditutup berkat implementasi `SubmissionPolicy@view` di Controller. |
| 5 | Ubah query `index` menjadi `Course::paginate()` murni tanpa filter, lalu login sebagai mahasiswa. | Mahasiswa dapat melihat seluruh daftar mata kuliah di sistem, termasuk yang bukan miliknya atau belum di-enroll. | Terjadi kebocoran data tingkat daftar (*Collection-level IDOR*). Semua *query* pada method `index` harus selalu difilter berdasarkan role user yang sedang login. |
| 6 | Kirim paksa parameter `role=admin` pada saat submit form edit profil. | Jika validasi Controller tidak mengunci melalui syarat auth role admin, peran pengguna akan berubah. | *Mass Assignment* sangat berbahaya. Pastikan properti sensitif seperti `role` tidak sembarangan dimasukkan ke `$fillable`, atau di-filter ketat di Controller. |
| 7 | Hapus pemetaan/cast `hashed` dari model `User`, lalu buat akun baru. Cek database langsung. | password tersimpan sebagai Teks Polos yang bisa dibaca siapapun. | Jika database bocor, peretas langsung mendapatkan password asli seluruh pengguna. Pemetaan `hashed` wajib ada agar enkripsi berjalan otomatis. |
| 8 | Login, salin isi token cookie `laravel_session` dari DevTools, tempel di browser komputer lain. | Browser di komputer lain itu dapat langsung berada dalam status **Login** tanpa perlu memasukkan username atau password sama sekali. | Inilah alasan mengapa Cookie wajib diberi tanda **HttpOnly** agar tak bisa dicuri skrip XSS dan **Secure** agar tak bisa disadap lewat WiFi publik atau HTTP. |
