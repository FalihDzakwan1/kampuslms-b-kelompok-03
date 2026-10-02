# Catatan Individu Minggu - 05 - Elsya Nur Aulia Handayani (10241026)

## 5. READ - BREAK - FIX - BUILD

### 5.1 READ — Penelusuran Alur Middleware, Otorisasi, dan Route Model Binding


1. #### Langkah 1: Jalankan `php artisan route:list --except-vendor`
   
<img src="image/read 5 elsya.png" width="500" alt="Hasil php artisan route:list">


 GET|HEAD        / ................................................................................ dashboard › routes/web.php:42
  GET|HEAD        admin/courses ..................................................... admin.courses.index › CourseController@index
  POST            admin/courses ..................................................... admin.courses.store › CourseController@store
  GET|HEAD        admin/courses/create ............................................ admin.courses.create › CourseController@create
  GET|HEAD        admin/courses/{course} .............................................. admin.courses.show › CourseController@show
  PUT|PATCH       admin/courses/{course} .......................................... admin.courses.update › CourseController@update
  DELETE          admin/courses/{course} ........................................ admin.courses.destroy › CourseController@destroy
  GET|HEAD        admin/courses/{course}/edit ......................................... admin.courses.edit › CourseController@edit
  GET|HEAD        admin/users ........................................................... admin.users.index › UserController@index
  POST            admin/users ........................................................... admin.users.store › UserController@store
  GET|HEAD        admin/users/create .................................................. admin.users.create › UserController@create
  GET|HEAD        admin/users/{user} ...................................................... admin.users.show › UserController@show
  PUT|PATCH       admin/users/{user} .................................................. admin.users.update › UserController@update
  DELETE          admin/users/{user} ................................................ admin.users.destroy › UserController@destroy
  GET|HEAD        admin/users/{user}/edit ................................................. admin.users.edit › UserController@edit
  POST            api/v1/assignments .............................................................. Api\AssignmentController@store
  PUT             api/v1/assignments/{assignment} ................................................ Api\AssignmentController@update
  PATCH           api/v1/assignments/{assignment} ................................................ Api\AssignmentController@update
  DELETE          api/v1/assignments/{assignment} ............................................... Api\AssignmentController@destroy
  GET|HEAD        api/v1/assignments/{assignment}/submissions ............................... Api\AssignmentController@submissions
  POST            api/v1/assignments/{assignment}/submissions ..................................... Api\SubmissionController@store
  POST            api/v1/auth/login ..................................................................... Api\AuthController@login
  POST            api/v1/auth/logout ................................................................... Api\AuthController@logout
  GET|HEAD        api/v1/courses ...................................................................... Api\CourseController@index
  GET|HEAD        api/v1/courses/{course} .............................................................. Api\CourseController@show
  GET|HEAD        api/v1/courses/{course}/assignments ........................................... Api\CourseController@assignments
  GET|HEAD        api/v1/courses/{course}/materials ............................................... Api\CourseController@materials
  GET|HEAD        api/v1/me ................................................................................ Api\AuthController@me
  GET|HEAD        api/v1/notifications .......................................................... Api\NotificationController@index
  POST            api/v1/notifications/{id}/read ................................................. Api\NotificationController@read
  PUT             api/v1/submissions/{submission}/grade ............................................... Api\GradeController@upsert
  GET|HEAD        dosen/assignments/{assignment} .............................. dosen.assignments.show › AssignmentController@show
  PUT|PATCH       dosen/assignments/{assignment} .......................... dosen.assignments.update › AssignmentController@update
  DELETE          dosen/assignments/{assignment} ........................ dosen.assignments.destroy › AssignmentController@destroy
  GET|HEAD        dosen/assignments/{assignment}/edit ......................... dosen.assignments.edit › AssignmentController@edit
  GET|HEAD        dosen/courses ..................................................... dosen.courses.index › CourseController@index
  POST            dosen/courses ..................................................... dosen.courses.store › CourseController@store
  GET|HEAD        dosen/courses/create ............................................ dosen.courses.create › CourseController@create
  GET|HEAD        dosen/courses/{course} .............................................. dosen.courses.show › CourseController@show
  PUT|PATCH       dosen/courses/{course} .......................................... dosen.courses.update › CourseController@update
  DELETE          dosen/courses/{course} ........................................ dosen.courses.destroy › CourseController@destroy
  GET|HEAD        dosen/courses/{course}/assignments ................ dosen.courses.assignments.index › AssignmentController@index
  POST            dosen/courses/{course}/assignments ................ dosen.courses.assignments.store › AssignmentController@store
  GET|HEAD        dosen/courses/{course}/assignments/create ....... dosen.courses.assignments.create › AssignmentController@create
  GET|HEAD        dosen/courses/{course}/edit ......................................... dosen.courses.edit › CourseController@edit
  GET|HEAD        dosen/courses/{course}/materials ...................... dosen.courses.materials.index › MaterialController@index
  POST            dosen/courses/{course}/materials ...................... dosen.courses.materials.store › MaterialController@store
  GET|HEAD        dosen/courses/{course}/materials/create ............. dosen.courses.materials.create › MaterialController@create
  GET|HEAD        dosen/materials/{material} ...................................... dosen.materials.show › MaterialController@show
  PUT|PATCH       dosen/materials/{material} .................................. dosen.materials.update › MaterialController@update
  DELETE          dosen/materials/{material} ................................ dosen.materials.destroy › MaterialController@destroy
  GET|HEAD        dosen/materials/{material}/edit ................................. dosen.materials.edit › MaterialController@edit
  GET|HEAD        dosen/submissions/{submission} .............................. dosen.submissions.show › SubmissionController@show
  GET|HEAD        login ............................................................................ login › LoginController@index
  POST            login .................................................................................... LoginController@login
  POST            logout ......................................................................... logout › LoginController@logout
  GET|HEAD        mahasiswa/assignments/{assignment} ...................... mahasiswa.assignments.show › AssignmentController@show
  POST            mahasiswa/assignments/{assignment}/submissions mahasiswa.assignments.submissions.store › SubmissionController@s…
  GET|HEAD        mahasiswa/courses ............................................. mahasiswa.courses.index › CourseController@index
  GET|HEAD        mahasiswa/courses/{course} ...................................... mahasiswa.courses.show › CourseController@show
  GET|HEAD        mahasiswa/courses/{course}/assignments ........ mahasiswa.courses.assignments.index › AssignmentController@index
  GET|HEAD        mahasiswa/courses/{course}/materials .............. mahasiswa.courses.materials.index › MaterialController@index
  GET|HEAD        mahasiswa/materials/{material} .............................. mahasiswa.materials.show › MaterialController@show
  GET|HEAD        mahasiswa/submissions ................................. mahasiswa.submissions.index › SubmissionController@index
  GET|HEAD        mahasiswa/submissions/{submission} ...................... mahasiswa.submissions.show › SubmissionController@show
  DELETE          mahasiswa/submissions/{submission} ................ mahasiswa.submissions.destroy › SubmissionController@destroy
  GET|HEAD        tentang ...................................................................... tentang › TentangController@index


#### Langkah 2: Tandai Setiap Route yang Menerima Parameter Model
Rute-rute yang menerima parameter model (`{course}`, `{user}`, `{assignment}`, `{material}`) ditandai dengan label [BERTANDA]:

admin/courses/{course}
admin/users/{user}

api/v1/assignments/{assignment}
api/v1/assignments/{assignment}/submissions
api/v1/courses/{course}
api/v1/courses/{course}/assignments
api/v1/courses/{course}/materials
api/v1/submissions/{submission}/grade

dosen/assignments/{assignment}
dosen/courses/{course}
dosen/courses/{course}/assignments
dosen/courses/{course}/materials
dosen/materials/{material}
dosen/submissions/{submission}

mahasiswa/assignments/{assignment}
mahasiswa/assignments/{assignment}/submissions
mahasiswa/courses/{course}
mahasiswa/courses/{course}/assignments
mahasiswa/courses/{course}/materials
mahasiswa/materials/{material}
mahasiswa/submissions/{submission}


#### Langkah 3: Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain? Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7. langkah 4: Buat tabel di docs/minggu-05-<nama>.md berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview. 

| Route Bertanda | Yang Seharusnya Bisa Akses | Yang Mencegah Orang Lain |
|------------|------------|------------|
| `admin/courses/{course}` | Admin | Middleware role admin |
| `admin/users/{user}` | Admin | Middleware role admin |
| `dosen/courses/{course}` | Dosen | Middleware role dosen |
| `dosen/assignments/{assignment}` | Dosen | Middleware role dosen |
| `dosen/materials/{material}` | Dosen | Middleware role dosen |
| `dosen/submissions/{submission}` | Dosen | Middleware role dosen |
| `mahasiswa/courses/{course}` | Mahasiswa | Middleware role mahasiswa |
| `mahasiswa/assignments/{assignment}` | Mahasiswa | Middleware role mahasiswa |
| `mahasiswa/materials/{material}` | Mahasiswa | Middleware role mahasiswa |
| `mahasiswa/submissions/{submission}` | Mahasiswa | Middleware role mahasiswa |
| `api/v1/courses/{course}` | User yang login | Sanctum / token |
| `api/v1/assignments/{assignment}` | User yang login | Sanctum / token |
| `api/v1/submissions/{submission}` | User yang login | Sanctum / token |


```
BREAK — Enam kerusakan (45 menit)


| No | Skenario Pengujian | Hasil yang Diharapkan | Hasil Pengamatan | Status | Kesimpulan |
|----|--------------------|-----------------------|------------------|--------|------------|
| 1 | Login sebagai mahasiswa A, kemudian mengakses submission milik mahasiswa B dengan mengganti ID pada URL | Sistem seharusnya menolak akses karena submission bukan milik mahasiswa yang login | Submission mahasiswa B masih dapat dibuka jika hanya menggunakan ID pada URL | Berhasil menemukan celah | Terjadi IDOR karena sistem belum melakukan pengecekan kepemilikan submission |
| 2 | Membuka `/courses/1/assignments/99` ketika assignment 99 sebenarnya milik course lain | Sistem seharusnya menolak karena assignment tidak memiliki hubungan dengan course tersebut | Assignment tetap dapat ditemukan jika hanya menggunakan ID assignment | Berhasil menemukan celah | Nested route belum melakukan validasi hubungan parent-child |
| 3 | Mengaktifkan `Route::scopeBindings()` kemudian mengulangi pengujian nomor 2 | Sistem seharusnya memastikan assignment benar-benar berada dalam course yang dipanggil | Laravel menolak request karena assignment tidak berada pada course tersebut | Berhasil | `scopeBindings()` mencegah akses child model yang tidak sesuai dengan parent model |
| 4 | Mendaftarkan middleware pada `app/Http/Kernel.php` seperti tutorial Laravel lama | Middleware seharusnya dapat didaftarkan pada file tersebut | File `app/Http/Kernel.php` tidak ditemukan pada Laravel 12 | Berhasil menemukan perbedaan struktur | Laravel 12 menggunakan `bootstrap/app.php` untuk konfigurasi middleware |
| 5 | Menambahkan `role:admin` pada grup route, lalu mengakses menggunakan akun dosen | Sistem seharusnya menolak akses karena role tidak sesuai | Akun dosen mendapatkan response `403 Forbidden` sebelum masuk controller | Berhasil | Middleware role berhasil membatasi akses berdasarkan jenis pengguna |
| 6 | Login sebagai dosen A, kemudian mencoba mengedit mata kuliah milik dosen B | Sistem seharusnya menolak walaupun keduanya memiliki role dosen | Dosen A masih dapat melewati middleware `role:dosen`, tetapi ditolak setelah pengecekan ownership menggunakan `abort_unless()` | Berhasil menemukan kelemahan | Middleware role tidak cukup untuk mencegah IDOR karena hanya mengecek role, bukan kepemilikan data |


``` 
Dari pengujian yang dilakukan, terlihat bahwa pembatasan akses berdasarkan role saja masih belum cukup untuk mengamankan data. Misalnya, meskipun pengguna sudah dibatasi sebagai dosen atau mahasiswa, mereka masih berpotensi mengakses data yang bukan miliknya jika tidak ada pengecekan tambahan.
Karena itu, sistem juga perlu memeriksa apakah data yang diakses memang benar milik pengguna tersebut. Pengecekan ini bisa dilakukan menggunakan authorization seperti abort_unless() atau Laravel Policy.
Pada pengujian nomor 6, terlihat bahwa dosen A dan dosen B memiliki role yang sama, yaitu dosen. Namun dosen A seharusnya tidak bisa mengubah mata kuliah milik dosen B. Jika hanya mengandalkan middleware role:dosen, akses masih bisa lolos. Setelah ditambahkan pengecekan ownership, barulah sistem dapat menolak akses yang tidak sesuai.
Jadi, role digunakan untuk menentukan jenis pengguna, sedangkan authorization digunakan untuk memastikan apakah pengguna tersebut memang berhak mengakses data tertentu. Keduanya perlu digunakan bersama agar celah seperti IDOR dapat dicegah.