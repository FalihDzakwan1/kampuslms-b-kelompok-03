### Nama: Fatika Rizki Syahada
#### NIM: 10241030

---

# READ — Bedah Starter Kit (30 menit)

Setelah memasang autentikasi, telusuri tanpa AI:

1. Berkas mana yang menangani POST login? Method apa?

Pada aplikasi web, request `POST /login` ditangani oleh `LoginController` pada method `login`. Route ini didefinisikan di `routes/web.php`:

```php
Route::post('/login', [LoginController::class, 'login']);
```

Sedangkan pada jalur API (Sanctum), request `POST /api/v1/auth/login` ditangani oleh method `login` pada `app/Http/Controllers/Api/AuthController.php`, yang didaftarkan di `routes/api.php`.

2. Di baris mana Auth::attempt() atau setaranya dipanggil?

Pemanggilan `Auth::attempt()` terdapat pada berkas `app/Http/Controllers/LoginController.php`:
- Baris 26: `$attempt = Auth::attempt($credentials);`
- Baris 31: `$attempt = Auth::attempt(['email' => $credentials['email'], 'password' => $altPassword]);` (mekanisme fallback untuk akun seeder demo).

Pada jalur API, pemanggilan terdapat di `app/Http/Controllers/Api/AuthController.php` pada baris 27:

```php
if (!Auth::attempt($credentials))
```

3. Temukan session()->regenerate(). Kenapa ia ada di situ?

Pemanggilan `session()->regenerate()` ditemukan pada berkas `app/Http/Controllers/LoginController.php` baris 36:

```php
$request->session()->regenerate();
```

Method ini dipanggil tepat setelah `Auth::attempt()` mengembalikan nilai `true`. Tujuannya adalah untuk mencegah serangan **Session Fixation**. Tanpa regenerasi, peramban akan terus menggunakan session ID lama yang mungkin telah disusupi atau ditanamkan oleh penyerang sebelum pengguna login. Dengan memanggil `session()->regenerate()`, Laravel menerbitkan ID sesi baru yang aman dan mengaitkannya dengan akun pengguna yang telah terautentikasi.

4. Di mana kata sandi di-hash? Cari cast hashed di model User.

Pengaturan hashing otomatis terdapat pada model `app/Models/User.php` di dalam method `casts()` pada baris 27–33:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

Cast `'password' => 'hashed'` memastikan bahwa setiap nilai baru yang diberikan ke atribut password akan secara otomatis di-hash menggunakan algoritma default (Bcrypt/Argon2) sebelum disimpan ke basis data. Selain itu, pada method `store` di `app/Http/Controllers/UserController.php` baris 52, hashing juga dipanggil secara manual menggunakan `\Illuminate\Support\Facades\Hash::make()`.

5. Buka DevTools → Cookies sebelum dan sesudah login. Bandingkan nilai cookie session.

| Kondisi | Nama Cookie | Pengamatan Nilai Cookie |
|---|---|---|
| Sebelum Login | `laravel_session` | Berisi string token terenkripsi sesi tamu (guest), contoh: `eyJpdiI6Ik...` (ID lama). |
| Setelah Login | `laravel_session` | Nilai string cookie berubah total menjadi nilai token acak baru. |

Perubahan nilai ini membuktikan bahwa instruksi `$request->session()->regenerate()` berhasil mengubah identifier sesi pengguna di peramban setelah kredensial divalidasi.

6. Logout, lalu tekan tombol back. Apa yang terjadi? Kenapa?

| Percobaan | Yang Diamati | Penjelasan Teknis |
|---|---|---|
| Logout lalu tekan tombol *Back* di peramban | Halaman sebelumnya (misal Dashboard) sempat muncul sekilas di layar. Namun saat mengklik tautan apa pun atau melakukan refresh, halaman langsung dialihkan kembali ke `/login`. | Tampilan halaman setelah tombol *Back* ditekan berasal dari cache lokal memori peramban (*Back-Forward Cache* / BFCache), bukan dari request server baru. Di sisi server, sesi telah dihancurkan oleh `LoginController@logout` melalui `$request->session()->invalidate()` dan `$request->session()->regenerateToken()`, sehingga hak akses sesi di server sudah tidak berlaku. |



# BREAK — Delapan Kerusakan (50 menit)

| No. | Percobaan | Hasil pengamatan | Kesimpulan |
|---:|---|---|---|
| 1 | Hapus `session()->regenerate()` dari login | **Session fixation terbuka.** Nilai cookie `laravel_session` sebelum dan sesudah login tidak mengalami perubahan sama sekali. | Tanpa regenerasi sesi, ID sesi sebelum login tetap aktif setelah login. Penyerang yang mengetahui ID sesi sebelum korban login dapat memakai ID tersebut untuk membajak akun korban. |
| 2 | Hapus `Gate::authorize()` dari `update`, tapi biarkan `@can` di Blade | **Tombol hilang, URL tetap jalan.** Di antarmuka Blade tombol Edit tidak muncul, namun ketika URL edit diakses langsung atau dikirimi request `PUT` via cURL / Postman, data mata kuliah berhasil diubah. | `@can` hanya mengontrol visibilitas elemen di tampilan antarmuka (UI). Otorisasi yang sesungguhnya wajib divalidasi di sisi server (Controller/Policy). |
| 3 | Sebagai dosen A, kirim PUT ke mata kuliah dosen B lewat `curl` | Server menolak request dan mengembalikan status **403 Forbidden**. | Kebijakan otorisasi pada `CoursePolicy@update` berhasil mencegah dosen mengubah mata kuliah milik dosen lain. |
| 4 | Sebagai mahasiswa, buka submission mahasiswa lain (IDOR minggu 5) | Server menolak akses dengan respons **403 Forbidden**. | Celah IDOR tertutup oleh `SubmissionPolicy@view` pada baris 40: `'mahasiswa' => $submission->user_id === $user->id`. |
| 5 | Ubah `index` menjadi `Course::paginate()` polos, login sebagai mahasiswa | **Kebocoran tingkat daftar.** Mahasiswa dapat melihat semua mata kuliah di seluruh kampus, termasuk yang tidak ia ikuti (*Collection-Level IDOR*). | Pengambilan data harus dibatasi pada query database sebelum pagination, seperti penggunaan `whereHas('students')` pada `CourseController@index`. |
| 6 | Kirim `role=admin` pada form edit profil | Nilai role pengguna tidak berubah menjadi admin; percobaan eksploitasi gagal. | Field `role` dilindungi dari *Mass Assignment* karena tidak didaftarkan di `$fillable` model `User`, serta di `UserController@update` baris 113 dibatasi secara ketat hanya dapat diubah jika pemohon adalah admin. |
| 7 | Ganti cast `hashed` menjadi tidak ada, buat user baru, lihat kolom password di database | Password tersimpan dalam bentuk teks polos (*plaintext*) di database jika tidak di-hash secara manual. | Jika cast `hashed` dihilangkan dan controller tidak memanggil `Hash::make()`, kata sandi tidak terenkripsi satu arah dan sangat rentan bocor jika basis data disusupi. |
| 8 | Login, salin cookie session, tempel di browser lain | Sesi pengguna dapat langsung digunakan di peramban lain (*Session Hijacking*). | Cookie sesi bertindak sebagai kredensial pengganti. Oleh karena itu, cookie wajib memiliki flag `HttpOnly` (agar tidak bisa dicuri script XSS) dan `Secure` (agar hanya dikirim melalui koneksi HTTPS yang terenkripsi). |



# Checkpoint Minggu 7 

1. Apa beda autentikasi dan otorisasi? Tunjukkan satu contoh masing-masing di kode Anda.

- **Autentikasi (Authentication):** Proses pembuktian identitas pengguna ("Siapa Anda?").
  - *Contoh di kode:* Pemeriksaan email dan kata sandi di `app/Http/Controllers/LoginController.php` baris 26:
    ```php
    $attempt = Auth::attempt($credentials);
    ```
- **Otorisasi (Authorization):** Proses penentuan hak akses pengguna terhadap aksi atau sumber daya tertentu ("Apa yang boleh Anda lakukan?").
  - *Contoh di kode:* Pemeriksaan izin akses detail mata kuliah di `app/Http/Controllers/CourseController.php` baris 99:
    ```php
    Gate::authorize('view', $course);
    ```

2. Tunjukkan Policy yang Anda tulis. Jelaskan tiap barisnya.

Berkas `app/Policies/CoursePolicy.php`:

```php
public function view(User $user, Course $course): bool
{
    return match ($user->role) {
        'admin'     => true,                                // Baris 30: Admin boleh melihat semua MK
        'dosen'     => $course->lecturer_id === $user->id,  // Baris 31: Dosen hanya boleh melihat MK miliknya sendiri
        'mahasiswa' => $course->students()                  // Baris 32-34: Mahasiswa hanya boleh melihat jika terdaftar di relasi pivot
                           ->where('users.id', $user->id)
                           ->exists(),
        default     => false,                               // Baris 35: Role lain ditolak secara otomatis
    };
}
```

Method ini memastikan bahwa hak akses ke satu record mata kuliah diverifikasi secara spesifik berdasarkan keterkaitan pengguna dengan data tersebut.

3. Kenapa @can di Blade tidak cukup? Peragakan dengan mengakses URL langsung.

Direktif `@can` hanya berjalan di lapisan antarmuka untuk menyembunyikan tombol dari pandangan mata pengguna. Pengguna yang memiliki niat buruk tetap dapat mengirim HTTP request langsung melalui cURL atau Postman ke URL target (misalnya `PUT /admin/courses/{id}`). Jika di controller tidak dipasang `Gate::authorize('update', $course)`, server akan tetap memproses update tersebut meskipun tombolnya tidak ditampilkan di layar.

4. Buka docs/keamanan.md. Pilih satu baris, jelaskan bagaimana ia ditutup, lalu buktikan dengan curl.

- **Baris yang dipilih:** Baris 13 — `GET /submissions/{submission}` (Pencegahan IDOR Mahasiswa melihat submission mahasiswa lain).
- **Cara ditutup:** Pada `app/Policies/SubmissionPolicy.php` baris 40, pemeriksaan kepemilikan diterapkan:
  ```php
  'mahasiswa' => $submission->user_id === $user->id
  ```
- **Bukti curl:**
  ```bash
  curl -i -X GET "http://localhost:8000/api/v1/submissions/2" \
    -H "Authorization: Bearer <TOKEN_MAHASISWA_1>" \
    -H "Accept: application/json"
  ```
  Respons yang diterima adalah `HTTP/1.1 403 Forbidden` dengan pesan JSON `{"message": "This action is unauthorized."}`.

5. Kenapa kata sandi di-hash, bukan dienkripsi? Apa konsekuensinya untuk fitur lupa password?

- **Alasan Hashing:** Hash bersifat satu arah (*one-way*), sehingga nilai hash tidak dapat didekripsi kembali menjadi teks semula. Sedangkan enkripsi bersifat dua arah (*two-way*) yang memiliki kunci dekripsi; jika kuncinya bocor, seluruh kata sandi pengguna akan terbongkar.
- **Konsekuensi Fitur Lupa Password:** Sistem tidak dapat mengirimkan kata sandi lama kepada pengguna. Konsekuensinya, alur lupa password harus menggunakan token reset satu kali pakai (*one-time token*) melalui email agar pengguna dapat membuat kata sandi baru.

6. Apa fungsi session()->regenerate() saat login?

Fungsinya adalah menghasilkan identifier sesi (*Session ID*) baru dan memindahkan data sesi ke ID baru tersebut saat autentikasi berhasil. Ini menutup celah *Session Fixation*, di mana penyerang mencoba memaksakan ID sesi yang sudah diketahui sebelum korban masuk.

7. Kenapa daftar mata kuliah tidak boleh diambil semua lalu disaring di view?

- **Keamanan (Data Leakage):** Data yang belum disaring sudah terlanjur dikirim dari basis data ke aplikasi. Data tersebut berpotensi bocor melalui API, debug tools, atau kesalahan render.
- **Performa & Skalabilitas:** Mengambil ribuan baris data dengan `Course::all()` akan menghabiskan memori RAM dan bandwidth server. Selain itu, fitur pagination basis data (`LIMIT` & `OFFSET`) menjadi tidak dapat dimanfaatkan.

8. Tunjukkan satu bagian yang Anda tulis dengan bantuan AI. Apa yang Anda ubah, dan kenapa?

- **Bagian Kode:** Filter query pada `app/Http/Controllers/CourseController.php` method `index()`.
- **Yang Diubah:** Menambahkan kondisi `whereHas('students')` khusus untuk mahasiswa dan `where('lecturer_id', $authUser->id)` untuk dosen.
- **Alasan Perubahan:** Rekomendasi awal AI mengambil seluruh record dan menyaringnya pada koleksi memori. Kode tersebut diubah menjadi filter langsung di query builder basis data guna mencegah kebocoran data tingkat koleksi (*Collection IDOR*) dan memastikan paginasi data berjalan optimal.
