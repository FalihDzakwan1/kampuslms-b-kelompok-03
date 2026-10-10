#### Nama: Falih Dzakwan
#### NIM : 10241028

## READ

1. Jalankan `php artisan route:list --except-vendor` . Salin keluarannya ke catatan.

```
  GET|HEAD        / ............................................................................................ dashboard › routes/web.php:8
  GET|HEAD        courses ............................................................................ courses.index › CourseController@index
  POST            courses ............................................................................ courses.store › CourseController@store
  GET|HEAD        courses/create ................................................................... courses.create › CourseController@create
  GET|HEAD        courses/{course} ..................................................................... courses.show › CourseController@show
  PUT|PATCH       courses/{course} ................................................................. courses.update › CourseController@update
  DELETE          courses/{course} ............................................................... courses.destroy › CourseController@destroy
  GET|HEAD        courses/{course}/edit ................................................................ courses.edit › CourseController@edit
  GET|HEAD        tentang ................................................................................. tentang › TentangController@index
  GET|HEAD        users .................................................................................. users.index › UserController@index
  POST            users .................................................................................. users.store › UserController@store
  GET|HEAD        users/create ......................................................................... users.create › UserController@create
  GET|HEAD        users/{user} ............................................................................. users.show › UserController@show
  PUT|PATCH       users/{user} ......................................................................... users.update › UserController@update
  DELETE          users/{user} ....................................................................... users.destroy › UserController@destroy
  GET|HEAD        users/{user}/edit ........................................................................ users.edit › UserController@edit
  ```

2. Tandai setiap route yang menerima parameter model ({course}, {assignment}, dst).
```
  GET|HEAD        courses/{course} ..................................................................... courses.show › CourseController@show
  PUT|PATCH       courses/{course} ................................................................. courses.update › CourseController@update
  DELETE          courses/{course} ............................................................... courses.destroy › CourseController@destroy
  GET|HEAD        courses/{course}/edit ................................................................ courses.edit › CourseController@edit
  GET|HEAD        users/{user} ............................................................................. users.show › UserController@show
  PUT|PATCH       users/{user} ......................................................................... users.update › UserController@update
  DELETE          users/{user} ....................................................................... users.destroy › UserController@destroy
  GET|HEAD        users/{user}/edit ........................................................................ users.edit › UserController@edit
```
3. Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain? Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7.

4. Buat tabel di docs/minggu-05-<nama>.md berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.

    Dari hasil `route:list`, titik yang rawan terkena serangan IDOR adalah semua rute yang menerima parameter spesifik (seperti `{course}` atau `{user}`). Berikut adalah daftarnya:
    
| No | Method | Endpoint / Route | Titik Rawan | Skenario Bahaya IDOR (Jika Tanpa Otorisasi) |
|---|---|---|---|---|
| 1 | GET | `courses/{course}` | `{course}` | Mahasiswa biasa menebak ID mata kuliah di URL dan berhasil melihat detail mata kuliah yang masih berstatus *draft* atau rahasia milik dosen lain. |
| 2 | GET | `courses/{course}/edit` | `{course}` | User biasa mengakses URL form edit mata kuliah milik orang lain, sehingga bisa melihat data mentah form sebelum diubah. |
| 3 | PUT/PATCH | `courses/{course}` | `{course}` | Dosen A "menembak" endpoint ini (via Postman/cURL) dengan ID mata kuliah Dosen B, sehingga bisa mengubah nama/SKS mata kuliah orang lain. |
| 4 | DELETE | `courses/{course}` | `{course}` | Mahasiswa "menembak" endpoint ini untuk menghapus mata kuliah secara paksa dengan menebak ID-nya. |
| 5 | GET | `users/{user}` | `{user}` | Seseorang mengganti ID di URL profil untuk mengintip data pribadi (seperti NIP/NIM/Email) milik pengguna lain yang seharusnya disembunyikan. |
| 6 | GET | `users/{user}/edit` | `{user}` | Mahasiswa mengganti ID di URL untuk mengakses halaman edit profil milik Admin atau Dosen. |
| 7 | PUT/PATCH | `users/{user}` | `{user}` | User A mengirim request PUT dengan ID User B untuk meretas akun (mengganti password, email, atau mengubah role-nya sendiri menjadi Admin). |
| 8 | DELETE | `users/{user}` | `{user}` | Mahasiswa menebak ID Admin, mengirim request DELETE, dan menghapus akun Admin dari sistem. |

## BREAK

| # | Yang Dicoba | Yang Terjadi | Hasil | Kesimpulan |
|---|---|---|---|---|
| 1 | Login sebagai **mahasiswa A**, lalu buka submission milik **mahasiswa B** dengan mengubah angka ID di URL browser. | Mahasiswa A berhasil masuk dan melihat halaman tugas beserta nilai milik Mahasiswa B. | Terjadi celah keamanan **IDOR (Insecure Direct Object Reference)** nyata di aplikasi. | Hanya dengan menyembunyikan tombol di antarmuka saja tidak cukup, perlu adanya validasi kepemilikan data pada level backend karena peretas bisa menebak ID di URL. |
| 2 | Buka URL nested route `/courses/1/assignments/99` (di mana tugas ID 99 sebenarnya milik mata kuliah lain, bukan MK 1). | Halaman tugas 99 tetap terbuka normal dan secara visual seolah-olah menjadi bagian dari Mata Kuliah 1. | Terjadi inkonsistensi *Nested Route* tanpa scoping. | Secara default, Laravel mem-*fetch* model Assignment murni berdasarkan ID-nya saja, tanpa mempedulikan apakah ia benar-benar anak (*child*) dari Course 1 (*parent*). |
| 3 | Aktifkan `Route::scopeBindings()` (atau `scoped()`) pada rute grup, lalu ulangi langkah nomor 2. | Halaman menolak untuk dimuat dan menampilkan pesan error **404 Not Found**. | Sistem aman dari manipulasi relasi URL. | Fitur *Scoped Bindings* wajib diaktifkan pada rute bersarang *nested route* ksrena Fitur ini memaksa Laravel untuk memverifikasi ulang apakah *child* tersebut benar-benar memiliki *foreign key* yang sesuai dengan *parent*-nya. |
| 4 | Mencoba mendaftarkan middleware kustom di file `app/Http/Kernel.php` seperti instruksi pada tutorial-tutorial lama di internet. | File tersebut tidak ditemukan di struktur direktori proyek. | Arsitektur framework telah berubah (kenali gejalanya). | Pada Laravel 11 (dan 12), file `Kernel.php` sudah dihilangkan. Registrasi middleware, alias, dan konfigurasi request kini dipusatkan di file `bootstrap/app.php` melalui method `withMiddleware()`. |
| 5 | Memasang middleware `role:admin` pada grup rute `/admin`, lalu mencoba mengakses rute tersebut menggunakan akun **Dosen**. | Tampil halaman error merah bertuliskan **403 Forbidden / Akses Ditolak**. | Middleware berhasil bekerja memblokir masuknya user asing. | Middleware sangat efektif digunakan untuk keamanan otorisasi makro. Ini bertugas menyaring request berdasarkan tipe atau grup Role pengguna secara global. |
| 6 | Login sebagai **Dosen A**, lalu tembak URL untuk masuk ke halaman Edit mata kuliah milik **Dosen B**. (Keduanya sama-sama lolos middleware `role:dosen`). | Dosen A berhasil masuk ke halaman Edit dan bisa memodifikasi data milik Dosen B. | Terjadi pelanggaran data lintas pengguna walau role sama. | Middleware `role` saja tidak cukup karena Middleware hanya peduli "Apakah dia Dosen?". Untuk mengecek "Apakah dia Dosen **pemilik kelas ini**?", maka wajib menggunakan perlindungan tingkat objek, yaitu **Policy / Gate**. |