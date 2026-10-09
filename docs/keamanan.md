# Analisis Keamanan dan Pencegahan IDOR
**KampusLMS - Kelompok 03**

Dokumen ini memetakan titik-titik rawan Insecure Direct Object Reference (IDOR) dan kerentanan otorisasi lainnya pada sistem KampusLMS, serta tindakan mitigasi yang telah kami terapkan pada kode (melalui Query dan Policy).

## Tabel Titik Rawan IDOR & Otorisasi

| Endpoint (Aksi) | Skenario Ancaman (IDOR / Privilege Escalation) | Mitigasi (Policy / Query yang Menutupnya) | Status |
|---|---|---|---|
| `GET /courses` (Katalog MK) | Mahasiswa bisa melihat daftar seluruh mata kuliah di kampus meskipun ia tidak terdaftar di MK tersebut (Collection-level IDOR). | **Query di CourseController@index**: Ditambahkan `when($role === 'mahasiswa')` dengan `whereHas('students')` agar query hanya menarik MK miliknya. | ✅ Aman |
| `GET /courses/{course}` | Mahasiswa/Dosen menebak ID mata kuliah di URL untuk melihat isi MK milik dosen lain atau yang belum ia ikuti. | **CoursePolicy@view**: Memeriksa `course->lecturer_id === user->id` untuk dosen, dan `students()->exists()` untuk mahasiswa. Dipanggil via `Gate::authorize()`. | ✅ Aman |
| `GET /materials/{material}` <br> (Unduh Materi) | Mahasiswa menyalin link unduh materi dari MK A dan membagikannya ke Mahasiswa di luar MK A. | **MaterialPolicy@view**: Memeriksa apakah user yang mengakses terdaftar di `material->course->students()`. | ✅ Aman |
| `GET /submissions/{submission}` | Mahasiswa menebak ID URL submission (misal `/submissions/5`) untuk mengintip, menyalin, atau mencuri file tugas milik teman sekelasnya (IDOR murni). | **SubmissionPolicy@view**: Secara ketat membandingkan `submission->user_id === auth()->id()`. Jika tidak cocok, ditolak (403). | ✅ Aman |
| `POST /assignments/{id}/submissions` | Mahasiswa mencoba mengumpulkan tugas berulang kali (spam) dengan menebak ID assignment. | **SubmissionPolicy@create**: Mengecek dua syarat sekaligus: mahasiswa terdaftar di MK, **dan** mahasiswa belum pernah punya record submission untuk assignment tersebut. | ✅ Aman |
| `PUT /users/{user}` <br> (Edit Profil) | Pengguna awam (Mahasiswa) melakukan inspeksi form edit profil, lalu menyisipkan *payload* `{"role": "admin"}` saat menyimpan data untuk menaikkan hak aksesnya (Privilege Escalation). | **UserController@update**: Filter ketat *hardcode*. Field `role` hanya dieksekusi jika dan hanya jika `$authUser->role === 'admin'`. Jika bukan admin, input role diabaikan. | ✅ Aman |
| `GET /grades` | Mahasiswa mencoba melihat seluruh daftar nilai kelas melalui endpoint API tersembunyi. | **GradePolicy@viewAny**: Mengembalikan `false` secara default untuk Mahasiswa. | ✅ Aman |

## Kesimpulan
Pendekatan keamanan utama di KampusLMS menggunakan:
1. **Penyaringan Level Query (Controller):** Mencegah bocornya data dalam bentuk daftar/koleksi (Collection IDOR).
2. **Laravel Authorization (Policy):** Mencegah akses, manipulasi, atau tindakan destruktif pada satu objek spesifik berdasarkan relasi kepemilikannya.