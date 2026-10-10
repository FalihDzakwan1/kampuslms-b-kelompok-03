# Pemetaan Celah Keamanan & Mitigasi IDOR
**KampusLMS — Kelompok 03**  
*Institut Teknologi Kalimantan*

Dokumen ini mendokumentasikan analisis kerentanan sistem, khususnya potensi **Insecure Direct Object Reference (IDOR)** dan eskalasi hak akses (*Privilege Escalation*), serta implementasi proteksi yang telah diterapkan pada kode sumber KampusLMS melalui **Query Scope (Database Level)**, **Laravel Policy (Authorization Level)**, dan **Model Guarding**.

---

## 1. Tabel Titik Rawan IDOR & Kontrol Akses

Berikut adalah daftar endpoint rawan yang menerima parameter identitas objek (seperti `{course}`, `{material}`, `{assignment}`, `{submission}`, atau `{user}`) beserta mekanisme proteksinya:

| No | Endpoint & Aksi | Skenario Celah Keamanan | Mekanisme Mitigasi pada Kode Sumber | Status |
|:--:|---|---|---|:--:|
| 1 | `GET /courses`<br>`GET /api/v1/courses`<br>*(Katalog Mata Kuliah)* | **Collection-Level IDOR:** Pengguna melihat seluruh mata kuliah kampus di luar hak aksesnya (misal mahasiswa melihat MK kelas lain, atau dosen melihat MK milik pengampu lain). | **Penyaringan Database Query di `CourseController@index`:**<br>Menggunakan klausul `when()`:<br>- Dosen: dibatasi `where('lecturer_id', $authUser->id)`<br>- Mahasiswa: dibatasi `whereHas('students', fn($q) => $q->where('users.id', $authUser->id))` | ✅ Aman |
| 2 | `GET /courses/{course}`<br>`GET /api/v1/courses/{course}`<br>*(Detail Mata Kuliah)* | **Horizontal/Vertical IDOR:** Mahasiswa atau dosen menebak ID pada URL untuk membuka konten mata kuliah yang tidak diikutinya atau bukan kelas ajarannya. | **Otorisasi di `CoursePolicy@view`:**<br>Dipanggil lewat `Gate::authorize('view', $course)`. Dosen wajib cocok dengan `lecturer_id`, dan mahasiswa wajib terdaftar di relasi pivot `students()->exists()`. | ✅ Aman |
| 3 | `GET /courses/{course}/enrollments`<br>`POST .../enrollments`<br>`DELETE .../enrollments/{student}`<br>*(Kelola Peserta MK)* | **Broken Object Level Authorization:** Dosen A mendaftarkan atau mengeluarkan mahasiswa dari kelas milik Dosen B dengan memanipulasi ID `{course}`. | **Otorisasi di `CoursePolicy@manageEnrollments`:**<br>Hanya Admin dan Dosen pengampu resmi (`lecturer_id === user->id`) yang diizinkan mengelola daftar peserta mata kuliah. | ✅ Aman |
| 4 | `GET /materials/{material}`<br>*(Unduh / Pratinjau Materi)* | **IDOR Akses Dokumen:** Mahasiswa menyalin tautan materi kuliah kelas A dan membagikannya ke mahasiswa di luar kelas, atau menebak ID materi. | **Otorisasi di `MaterialPolicy@view`:**<br>Memeriksa relasi berjenjang `$material->course->students()->where('users.id', $user->id)->exists()`. Mahasiswa luar langsung ditolak (403 Forbidden). | ✅ Aman |
| 5 | `PUT /materials/{material}`<br>`DELETE /materials/{material}`<br>*(Ubah/Hapus Materi)* | **IDOR Manipulasi Konten:** Dosen A mengedit atau menghapus berkas materi perkuliahan milik Dosen B. | **Otorisasi di `MaterialPolicy@update` & `@delete`:**<br>Memastikan `$material->course->lecturer_id === $user->id` sebelum instruksi manipulasi database dieksekusi. | ✅ Aman |
| 6 | `PUT /assignments/{assignment}`<br>`DELETE /assignments/{assignment}`<br>*(Kelola Tugas)* | **IDOR Manipulasi Tugas:** Dosen A mengubah instruksi/tenggat waktu tugas atau menghapus tugas dari mata kuliah milik Dosen B. | **Otorisasi di `AssignmentPolicy@update` & `@delete`:**<br>Memvalidasi kepemilikan kursus melalui `$assignment->course->lecturer_id === $user->id`. | ✅ Aman |
| 7 | `GET /submissions/{submission}`<br>*(Pratinjau Pengumpulan Tugas)* | **IDOR Privasi Mahasiswa:** Mahasiswa mengganti ID pada URL (misal `/submissions/5`) untuk mengintip dokumen tugas atau menyontek berkas milik rekan sekelasnya. | **Pengecekan Kepemilikan di `SubmissionPolicy@view`:**<br>Secara ketat mencocokkan `'mahasiswa' => $submission->user_id === $user->id`. Akses lintas mahasiswa menghasilkan respons 403 Forbidden. | ✅ Aman |
| 8 | `POST /assignments/{assignment}/submissions`<br>*(Pengumpulan Tugas)* | **IDOR & Spam Pengumpulan:** Mahasiswa mencoba mengirim tugas ke mata kuliah yang tidak diikutinya, atau mengirimkan pengumpulan ganda secara ilegal. | **Validasi Ganda di `SubmissionPolicy@create`:**<br>Memastikan dua syarat terpenuhi: mahasiswa sudah terdaftar di MK (`$isEnrolled`) dan belum pernah mengumpulkan tugas tersebut (`!$alreadySubmitted`). | ✅ Aman |
| 9 | `DELETE /submissions/{submission}`<br>*(Pembatalan Pengumpulan)* | **Insecure Deletion:** Mahasiswa mencoba membatalkan/menghapus riwayat tugas yang sudah dikumpulkan setelah batas waktu berakhir atau milik orang lain. | **Kebijakan Absolut di `SubmissionPolicy@delete`:**<br>Method mengembalikan nilai `false` secara permanen. Berkas submission bersifat *immutable* setelah dikirim. | ✅ Aman |
| 10 | `PUT /submissions/{submission}/grade`<br>*(Penilaian Tugas Mahasiswa)* | **Privilege Escalation & IDOR Nilai:** Mahasiswa menyuntikkan nilai untuk dirinya sendiri, atau Dosen A menilai tugas mahasiswa di kelas Dosen B. | **Otorisasi di `GradePolicy@create` & `GradePolicy@update`:**<br>Memastikan peran adalah dosen dan pengampu tugas bersangkutan (`$submission->assignment->course->lecturer_id === $user->id`). | ✅ Aman |
| 11 | `PUT /admin/users/{user}`<br>*(Perubahan Data Profil / Akun)* | **Mass Assignment & Privilege Escalation:** Pengguna menyisipkan *payload* `{"role": "admin"}` saat update data profil untuk menaikkan tingkatan hak akses. | **Pengecekan di `UserController@update` & Model Guarding:**<br>1. Field `role` tidak didaftarkan di `$fillable` Model `User`.<br>2. Perubahan nilai role hanya diproses jika pemohon berstatus admin: `if ($authUser->role === 'admin' && isset($validated['role']))`. | ✅ Aman |
| 12 | `GET /api/v1/notifications`<br>`POST /api/v1/notifications/{id}/read`<br>*(Pemberitahuan Akun)* | **IDOR Notifikasi:** Pengguna membaca atau menandai status baca pada pesan notifikasi milik akun lain. | **User-Scoped Query di `NotificationController`:**<br>Pengambilan data selalu terikat langsung pada sesi: `$request->user()->notifications()`, sehingga pengguna mustahil mengakses data di luar cakupannya. | ✅ Aman |

---

## 2. Implementasi Keamanan Tambahan di Luar IDOR

Selain pencegahan IDOR pada level objek, arsitektur KampusLMS dilengkapi dengan praktik keamanan standar web:

### A. Proteksi Session Fixation
- **Lokasi Kode:** `app/Http/Controllers/LoginController.php` (baris 40 & 75).
- **Mekanisme:** Saat kredensial login berhasil diverifikasi, sistem memanggil `$request->session()->regenerate()` untuk memperbarui session ID pada peramban. Saat logout, sesi dihapus permanen melalui `$request->session()->invalidate()` dan token CSRF diperbarui via `regenerateToken()`.

### B. Proteksi Brute-Force & Rate Limiting
- **Lokasi Kode:** `routes/api.php` (baris 18 & 21).
- **Mekanisme:** Endpoint autentikasi publik `/api/v1/auth/login` dilindungi middleware `throttle:login` (maksimal 5 percobaan per menit). Seluruh endpoint API terproteksi dibatasi dengan middleware `throttle:api` (60 permintaan per menit).

### C. Keamanan Penyimpanan Kredensial (Password Hashing)
- **Lokasi Kode:** `app/Models/User.php` dan `app/Http/Controllers/UserController.php`.
- **Mekanisme:** Kata sandi diproteksi algoritma hashing satu arah (Bcrypt) melalui `Hash::make()` dan cast Eloquent `'password' => 'hashed'`, sehingga teks asli kata sandi tidak pernah tersimpan di database dalam bentuk *plaintext*.

### D. Server-Side Authorization vs UI Hiding
- Direktif Blade `@can` hanya difungsikan untuk kenyamanan antarmuka (UX) dengan menyembunyikan tombol aksi.
- Seluruh validasi hak akses tetap diwajibkan berjalan di sisi server melalui `Gate::authorize()` dan middleware route (`role:admin`, `role:dosen`, `role:mahasiswa`). Request manipulasi via cURL/Postman tetap akan ditolak dengan kode status `403 Forbidden`.

---

## 3. Kesimpulan

Strategi keamanan pada KampusLMS Kelompok 03 menerapkan prinsip pertahanan berlapis (*Defense in Depth*):
1. **Middleware Lapisan Rute (`auth`, `role`, `throttle`):** Menyaring tipe pengguna dan membatasi frekuensi request sebelum masuk ke Controller.
2. **Database Query Scoping (`CourseController@index`):** Mencegah kebocoran data massal pada level daftar koleksi (*Collection-Level IDOR*).
3. **Laravel Policy Engine:** Memastikan kepemilikan dan relasi antar entitas diverifikasi secara ketat sebelum aksi `view`, `create`, `update`, atau `delete` dieksekusi.
4. **Model Guarding (`$fillable`):** Mencegah modifikasi atribut sensitif melalui eksploitasi *Mass Assignment*.