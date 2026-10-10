# Catatan Individu Minggu - 07 - Elsya Nur Aulia Handayani (10241026)

## 7.3 Read → Break → Fix → Build

### READ — Bedah starter kit (30 menit)

#### 1. Berkas mana yang menangani POST login? Method apa?

* **Berkas:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
* **Method:** `store(LoginRequest $request)`
* **Rutenya:** `Route::post('login', [AuthenticatedSessionController::class, 'store'])` di dalam berkas `routes/auth.php`.
* **Penjelasan:**  
  Ketika kita submit form login, request `POST /login` diarahkan ke method `store()`. Di controller ini tugasnya dibagi: method `create()` hanya untuk menampilkan form login di layar, sedangkan method `store()` bertugas memproses data email dan password yang dikirim pengguna untuk mulai login.

---

#### 2. Di baris mana Auth::attempt() atau setaranya dipanggil?

* **Berkas:** `app/Http/Requests/Auth/LoginRequest.php`
* **Method:** `authenticate()`
* **Kodenya:**
  ```php
  if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
      RateLimiter::hit($this->throttleKey());

      throw ValidationException::withMessages([
          'email' => trans('auth.failed'),
      ]);
  }
  ```
* **Penjelasan:**  
  Logika pengecekan login sengaja dipindah dari controller ke `LoginRequest` biar kode controller tetap ringkas. Di dalam method `authenticate()`, fungsi `Auth::attempt()` bertugas mencari user berdasarkan email dan mencocokkan password-nya ke database. Kalau cocok, user otomatis login ke sistem. Kalau salah, sistem membatasi percobaan login (rate limit) dan menampilkan pesan error.

---

#### 3. Temukan session()->regenerate(). Kenapa ia ada di situ?

* **Berkas:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php` (di dalam method `store`)
* **Kodenya:**
  ```php
  $request->authenticate();
  $request->session()->regenerate();
  ```
* **Kenapa ia ada di situ:**  
  Perintah ini dipakai untuk mencegah serangan *session fixation*. Sebelum login, browser sudah punya ID session bawaan sebagai tamu. Begitu user berhasil login, ID session tersebut langsung diganti dengan ID session yang baru agar ID session lama tidak bisa dibajak atau dipakai orang lain untuk masuk ke akun user.

---

#### 4. Di mana kata sandi di-hash? Cari cast hashed di model User.

* **Berkas:** `app/Models/User.php`
* **Kodenya:**
  ```php
  protected function casts(): array
  {
      return [
          'email_verified_at' => 'datetime',
          'password' => 'hashed',
      ];
  }
  ```
* **Penjelasan:**  
  Di model `User`, kolom password diberi aturan cast `'password' => 'hashed'`. Artinya, setiap kali password diisi atau diubah, Laravel otomatis mengubah password tersebut menjadi hash bcrypt sebelum disimpan ke database. Hash ini sifatnya satu arah, jadi password aslinya tidak bisa dibaca kembali.

---

#### 5. Buka DevTools → Cookies sebelum dan sesudah login. Bandingkan nilai cookie session.

* **Cara cek:**  
  Buka inspect element (F12) di browser ➔ masuk ke menu Application (atau Storage) ➔ pilih Cookies ➔ klik domain web kita (`http://kampuslms-kelompok-a07.test`).
* **Hasilnya:**  
  * Sebelum login (sebagai tamu), ada cookie session dengan nilai teks acak tertentu.
  * Setelah berhasil login, nilai teks pada cookie session tersebut berubah jadi teks acak yang berbeda.
* **Kesimpulan:**  
  Perubahan nilai cookie ini membuktikan bahwa fungsi `session()->regenerate()` di server benar-benar bekerja mengganti ID session tamu menjadi ID session pengguna yang sudah login.

---

#### 6. Logout, lalu tekan tombol back. Apa yang terjadi? Kenapa?

* **Apa yang terjadi:**  
  Halaman dashboard sebelumnya masih kelihatan di layar seolah-olah kita belum logout.
* **Kenapa bisa begitu:**  
  Ini terjadi bukan karena kita masih login di server, melainkan hanya tampilan memori cache dari browser (BFCache). Browser menyimpan gambar halaman terakhir supaya pas tombol back ditekan, halamannya langsung muncul cepat.
* **Buktinya:**  
  Session di server sebenarnya sudah dihapus pas kita klik logout. Kalau halaman tersebut kita refresh atau kita klik menu lain, kita langsung diarahkan kembali ke halaman login karena aksesnya sudah ditolak server.

---

### BREAK — Delapan Kerusakan (50 menit)

Bagian ini mendokumentasikan hasil pengujian dengan sengaja merusak atau mematikan fitur keamanan tertentu untuk melihat perilakunya secara langsung:

| # | Yang Dicoba | Perilaku yang Terjadi | Penjelasan & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Hapus `session()->regenerate()` dari proses login di `AuthenticatedSessionController`. | User tetap bisa login normal, tapi nilai cookie session di browser tidak berubah sama sekali antara sebelum dan sesudah login. | **Celah *Session Fixation* terbuka.** Penyerang bisa menjebak korban dengan ID session yang sudah diketahui sebelumnya. Karena ID session tidak diperbarui saat login, penyerang bisa ikut masuk ke akun korban memakai session tersebut. |
| **2** | Hapus `Gate::authorize()` di method `update` controller, tapi tombol Edit di Blade tetap dibungkus `@can`. | Tombol Edit di halaman web memang hilang dan tidak kelihatan oleh mahasiswa. Namun, saat URL edit dibuka langsung atau dikirimi request `PUT /courses/{id}` lewat Postman/cURL, data mata kuliah **tetap berhasil diubah**. | **Tombol hilang bukan berarti aman.** Ini kesalahan fatal yang paling sering terjadi. `@can` di Blade hanya manipulasi tampilan (UI), bukan pengaman server. Otorisasi wajib dieksekusi di controller dengan `Gate::authorize()`. |
| **3** | Kirim request `PUT` untuk mengubah mata kuliah milik Dosen B menggunakan akun Dosen A lewat cURL. | Server langsung mengembalikan respon **HTTP 403 Forbidden**. Perubahan data ditolak mentah-mentah. | **Uji kepemilikan Policy bekerja.** `CoursePolicy@update` berhasil memeriksa relasi `$course->lecturer_id === $user->id`. Dosen tidak bisa sembarangan mengotak-atik kelas dosen lain. |
| **4** | Login sebagai mahasiswa A, lalu coba akses URL submission milik mahasiswa B (`GET /submissions/{id}`). | Server merespons dengan **HTTP 403 Forbidden** dan tampilan ditolak. | **Celah IDOR berhasil ditutup.** `SubmissionPolicy@view` memastikan tugas hanya boleh dilihat oleh mahasiswa pemiliknya, dosen pengampu mata kuliah terkait, atau admin. |
| **5** | Ubah query `CourseController@index` jadi `Course::paginate(15)` polos tanpa filter role, lalu buka pakai akun mahasiswa. | Mahasiswa bisa melihat seluruh mata kuliah yang terdaftar di kampus, termasuk mata kuliah dari jurusan atau semester lain yang tidak diikutinya. | **Kebocoran data di level daftar (*List Leak*).** Otorisasi tidak cuma diperlukan di halaman detail, tapi juga di query awal. Data harus selalu disaring sejak dari database menggunakan peran user yang sedang login. |
| **6** | Kirim parameter tambahan `role=admin` saat submit form edit profil atau update data akun. | Nilai role user di database tidak berubah (tetap mahasiswa atau dosen). | **Proteksi *Mass Assignment* aktif.** Kolom `role` tidak dibuka sembarangan di `$fillable` dan divalidasi ketat lewat Form Request, sehingga user biasa tidak bisa menaikkan hak aksesnya sendiri (*privilege escalation*). |
| **7** | Hapus cast `'password' => 'hashed'` di model `User`, lalu simpan user baru tanpa fungsi hash manual. | Password tersimpan dalam bentuk teks biasa (*plain text*) di tabel database, terbaca jelas tanpa enkripsi. | **Sangat berbahaya jika database bocor.** Password wajib selalu di-hash menggunakan algoritma satu arah (seperti bcrypt). Dengan cast `hashed`, Laravel otomatis mengamankannya setiap kali data disimpan. |
| **8** | Login di browser A, salin nilai cookie session (`laravel_session`), lalu tempel di browser B (misal Incognito) dan refresh. | Browser B langsung otomatis masuk ke akun yang sama tanpa perlu memasukkan email dan password sama sekali. | **Bahaya pembajakan sesi (*Session Hijacking*).** Membuktikan bahwa session sepenuhnya dikontrol oleh cookie. Karena itu cookie wajib diproteksi dengan atribut `HttpOnly` (kebal XSS) dan `Secure` (hanya lewat HTTPS). |

---

#### Pelajaran Paling Kritis dari Eksperimen Break:

Poin **Nomor 2** adalah temuan paling penting dalam modul ini:
Banyak developer pemula mengira ketika tombol "Edit" atau "Hapus" sudah disembunyikan pakai `@can(...)` di template Blade, aplikasinya sudah aman. Padahal pengguna yang paham teknis tinggal membuka DevTools atau menembak endpoint-nya via cURL/Postman. Keamanan aplikasi web **wajib ditegakkan di sisi server** lewat `Gate::authorize()` atau Form Request `authorize()`, bukan sekadar menyembunyikan tombol di antarmuka.

---

### BUILD — Milestone M2 (Tugas 2)

Daftar pekerjaan yang telah selesai dibangun pada repository kelompok (`kampuslms-kelompok-A07`):

1. **Autentikasi Breeze:**  
   Memasang starter kit Breeze Blade untuk alur login, logout aman dengan proteksi CSRF, session regeneration, dan manajemen profil.
2. **5 Policy Otorisasi:**  
   Membuat dan mengisi aturan otorisasi berbasis kepemilikan untuk seluruh model: `CoursePolicy`, `MaterialPolicy`, `AssignmentPolicy`, `SubmissionPolicy`, dan `GradePolicy`.
3. **Integrasi Controller Laravel 12:**  
   Menerapkan `Gate::authorize()` di seluruh controller (`CourseController`, `MaterialController`, `AssignmentController`) menggantikan pengecekan manual lama.
4. **Filter Query Sesuai Peran:**  
   Memastikan halaman index hanya mengambil data yang menjadi hak masing-masing user (admin melihat semua, dosen melihat kelas ajarannya, mahasiswa melihat kelas yang diambil).
5. **Pembaruan Tampilan Navbar:**  
   Menampilkan tombol login untuk tamu (`@guest`), serta nama user, badge peran, menu mata kuliah dinamis, dan tombol logout via method `POST` dengan `@csrf` untuk user yang sedang login (`@auth`).
6. **Dokumentasi & Skrip Uji Keamanan:**  
   * `docs/keamanan.md`: Matriks lengkap pencegahan IDOR di setiap endpoint.  
   * `scripts/test-authz.sh`: Skrip pengujian otomatis otorisasi via cURL (guest ditolak, mahasiswa diblokir dari aksi dosen, dosen sah diizinkan).
7. **Verifikasi Test Suite:**  
   Semua test unit dan feature bawaan maupun tambahan lulus 100% (`37 passed, 121 assertions`).

### checkpoint — minggu 7

## Kuis 7 — Autentikasi dan Otorisasi

### 1. Apa beda autentikasi dan otorisasi?

- **Autentikasi** itu memastikan siapa yang sedang memakai aplikasi. Contohnya, saat login, aplikasi mencocokkan email dan kata sandi. Di proyek ini contohnya ada di `app/Http/Controllers/LoginController.php`, pada pemanggilan `Auth::attempt($credentials)`.
- **Otorisasi** itu memastikan pengguna yang sudah dikenali boleh melakukan apa. Contohnya, `CoursePolicy` memeriksa apakah mahasiswa memang terdaftar di mata kuliah yang mau dibuka.

autentikasi bertanya **“kamu siapa?”**, otorisasi bertanya **“kamu boleh melakukan ini?”**. Sudah login belum tentu boleh membuka semua data.

### 2. Policy yang dipakai dan arti tiap bagiannya

Contoh yang dipakai di proyek adalah `view()` di [`app/Policies/CoursePolicy.php`](../app/Policies/CoursePolicy.php):

```php
public function view(User $user, Course $course): bool
{
    return match ($user->role) {
        'admin'     => true,
        'dosen'     => $course->lecturer_id === $user->id,
        'mahasiswa' => $course->students()
                           ->where('users.id', $user->id)
                           ->exists(),
        default     => false,
    };
}
```

1. `public function view(User $user, Course $course): bool`  
   Ini aturan untuk menentukan apakah `$user` boleh melihat satu `$course`. Hasilnya harus `true` atau `false`.
2. `return match ($user->role) {`  
   Pilih aturan berdasarkan peran pengguna yang sedang login.
3. `'admin' => true,`  
   Admin boleh melihat mata kuliah.
4. `'dosen' => $course->lecturer_id === $user->id,`  
   Dosen cuma boleh melihat mata kuliah yang ID dosennya sama dengan ID akunnya.
5. `'mahasiswa' => $course->students()`  
   Untuk mahasiswa, mulai cek daftar mahasiswa yang terhubung ke mata kuliah itu.
6. `->where('users.id', $user->id)`  
   Cari apakah ID mahasiswa yang sedang login ada di daftar tersebut.
7. `->exists(),`  
   Cek keberadaan saja. Hasilnya `true` kalau terdaftar dan `false` kalau tidak.
8. `default => false,`  
   Kalau role-nya tidak cocok dengan aturan di atas, akses ditolak.
9. Kurung kurawal dan titik koma menutup fungsi dan aturan `match`.

### 3. Kenapa `@can` di Blade tidak cukup? Coba akses URL langsung

`@can` di Blade tidak cukup untuk mengamankan fitur karena hanya mengatur apakah tombol atau tautan ditampilkan di halaman. Pengguna masih bisa mengetik URL secara langsung atau mengirim permintaan ke server tanpa menekan tombol tersebut.

Karena itu, pemeriksaan izin juga harus dilakukan di controller menggunakan Policy atau `Gate::authorize()`. Contohnya, ketika dosen pertama mencoba mengakses mata kuliah yang menjadi milik dosen kedua, sistem harus memeriksa hak aksesnya dan menolak permintaan jika dosen tersebut tidak memiliki izin.

Pengujian dapat dilakukan menggunakan `curl` dengan token akun dosen pertama. Jika Policy dan data sudah sesuai, respons yang diharapkan adalah **HTTP 403 Forbidden**. Hasil tersebut harus dibuktikan dengan menjalankan perintah pada aplikasi, bukan hanya berdasarkan perkiraan.

Contoh percobaan lewat API, tanpa menekan tombol apa pun di halaman:

```bash
BASE="http://kampuslms-b-kelompok-03.test/api/v1"
TOKEN=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"dosen@kampuslms.test","password":"password"}' \
  | jq -r '.data.token')

curl -i "$BASE/courses/2" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 4. Satu baris dari `docs/keamanan.md` dan cara membuktikannya

Saya memilih baris nomor 7, yaitu GET /submissions/{submission} tentang akses pengumpulan tugas mahasiswa.

Masalahnya, mahasiswa bisa mengganti angka ID di URL untuk mencoba melihat tugas milik mahasiswa lain. Misalnya, mahasiswa A mencoba membuka /submissions/5, padahal tugas itu milik mahasiswa B. Ini disebut IDOR, yaitu ketika pengguna mencoba mengakses data orang lain dengan mengganti ID pada URL.

Untuk mencegah hal ini, KampusLMS menggunakan SubmissionPolicy@view. Di bagian ini, sistem memeriksa apakah ID pemilik submission sama dengan ID mahasiswa yang sedang login.

kodenya:

```php
return $submission->user_id === $user->id;
```
Maksudnya, kalau ID pemilik tugas sama dengan ID pengguna yang sedang login, akses boleh diberikan. Kalau berbeda, akses ditolak.

Untuk membuktikannya, saya akan login menggunakan akun mahasiswa A, lalu memakai curl untuk mencoba membuka submission milik mahasiswa B. Kalau pengecekan Policy berjalan dengan benar, server akan menolak akses tersebut. Hasil yang diharapkan adalah 403 Forbidden.

Jadi, sistem tidak hanya melihat ID yang ada di URL, tetapi juga mengecek apakah pengguna memang punya hak untuk melihat tugas tersebut. 

### 5. Kenapa kata sandi di-hash, bukan dienkripsi?

Kata sandi di-hash karena aplikasi tidak perlu mengetahui kata sandi asli yang disimpan di database. Saat login, sistem cukup mengecek apakah kata sandi yang dimasukkan cocok dengan hash yang tersimpan.

Kalau database bocor, orang tidak bisa langsung melihat kata sandi asli pengguna. Jadi, cara ini lebih aman daripada menyimpan kata sandi dalam bentuk aslinya.

Di proyek KampusLMS, model User menggunakan cast **'password' => 'hashed'**, sedangkan proses pembuatan atau penggantian kata sandi menggunakan **Hash::make().

Dampaknya untuk fitur lupa password, sistem tidak bisa mengirimkan kata sandi lama karena kata sandi asli tidak disimpan. Jadi, pengguna harus membuat kata sandi baru melalui tautan reset password yang dikirim ke email.

### 6. Apa fungsi `session()->regenerate()` saat login?

Fungsinya untuk mengganti ID sesi lama dengan ID baru setelah login berhasil. Tujuannya agar orang lain tidak bisa memanfaatkan ID sesi lama untuk masuk ke akun pengguna. Serangan ini disebut session fixation.

Contohnya ada di **app/Http/Controllers/LoginController.php**.

### 7. Kenapa daftar mata kuliah tidak boleh diambil semua lalu disaring di view?

Karena pengguna bisa saja menerima atau melihat data yang seharusnya tidak boleh diakses. Selain itu, mengambil semua data lalu menyaringnya di tampilan membuat aplikasi lebih lambat dan boros memori.

Jadi, penyaringan harus dilakukan di bagian pengambilan data, sebelum data dikirim ke tampilan. Dengan begitu, pengguna hanya mendapatkan mata kuliah yang memang boleh dilihatnya.

### 8. Bagian yang dibantu AI

**session()->regenerate();**

AI membantu menjelaskan fungsi session()->regenerate() pada proses login. Saya kemudian menyesuaikan penjelasannya dengan alur login pada proyek KampusLMS.

Perubahan yang saya lakukan adalah menyesuaikan penjelasan agar lebih mudah dipahami, yaitu bahwa fungsi tersebut mengganti ID sesi setelah pengguna berhasil login. Hal ini bertujuan untuk mencegah serangan session fixation, yaitu penyalahgunaan ID sesi lama untuk mengambil alih sesi pengguna.

Penyesuaian ini dilakukan agar penjelasan sesuai dengan fungsi keamanan pada proses login KampusLMS.

**app/Http/Controllers/LoginController.php**