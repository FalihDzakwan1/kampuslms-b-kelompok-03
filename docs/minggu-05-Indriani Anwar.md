### Nama : Indriani Anwar
#### NIM : 10241036
---
READ — Peta route Anda sendiri (30 menit)
1. Jalankan php artisan route:list --except-vendor. Salin keluarannya ke catatan.
Menjalankan kode ```php artisan route:list --except-vendor``` akan menampilkan daftar route yang dibuat oleh aplikasi, tanpa menampilkan route dari package/vendor Laravel. : 
```
GET|HEAD        / ................................................................ dashboard › routes/web.php:42
  GET|HEAD        admin/courses ..................................... admin.courses.index › CourseController@index
  POST            admin/courses ..................................... admin.courses.store › CourseController@store
  GET|HEAD        admin/courses/create ............................ admin.courses.create › CourseController@create
  GET|HEAD        admin/courses/{course} .............................. admin.courses.show › CourseController@show
  PUT|PATCH       admin/courses/{course} .......................... admin.courses.update › CourseController@update
  DELETE          admin/courses/{course} ........................ admin.courses.destroy › CourseController@destroy
  GET|HEAD        admin/courses/{course}/edit ......................... admin.courses.edit › CourseController@edit
  GET|HEAD        admin/users ........................................... admin.users.index › UserController@index
  POST            admin/users ........................................... admin.users.store › UserController@store
  GET|HEAD        admin/users/create .................................. admin.users.create › UserController@create
  GET|HEAD        admin/users/{user} ...................................... admin.users.show › UserController@show
  PUT|PATCH       admin/users/{user} .................................. admin.users.update › UserController@update
  DELETE          admin/users/{user} ................................ admin.users.destroy › UserController@destroy
  GET|HEAD        admin/users/{user}/edit ................................. admin.users.edit › UserController@edit
  GET|HEAD        dosen/assignments/{assignment} .............. dosen.assignments.show › AssignmentController@show
  PUT|PATCH       dosen/assignments/{assignment} .......... dosen.assignments.update › AssignmentController@update
  DELETE          dosen/assignments/{assignment} ........ dosen.assignments.destroy › AssignmentController@destroy
  GET|HEAD        dosen/assignments/{assignment}/edit ......... dosen.assignments.edit › AssignmentController@edit
  GET|HEAD        dosen/courses ..................................... dosen.courses.index › CourseController@index
  POST            dosen/courses ..................................... dosen.courses.store › CourseController@store
  GET|HEAD        dosen/courses/create ............................ dosen.courses.create › CourseController@create
  GET|HEAD        dosen/courses/{course} .............................. dosen.courses.show › CourseController@show
  PUT|PATCH       dosen/courses/{course} .......................... dosen.courses.update › CourseController@update
  DELETE          dosen/courses/{course} ........................ dosen.courses.destroy › CourseController@destroy
  GET|HEAD        dosen/courses/{course}/assignments dosen.courses.assignments.index › AssignmentController@index
  POST            dosen/courses/{course}/assignments dosen.courses.assignments.store › AssignmentController@store
  GET|HEAD        dosen/courses/{course}/assignments/create dosen.courses.assignments.create › AssignmentControll…
  GET|HEAD        dosen/courses/{course}/edit ......................... dosen.courses.edit › CourseController@edit
  GET|HEAD        dosen/courses/{course}/materials ...... dosen.courses.materials.index › MaterialController@index
  POST            dosen/courses/{course}/materials ...... dosen.courses.materials.store › MaterialController@store
  GET|HEAD        dosen/courses/{course}/materials/create dosen.courses.materials.create › MaterialController@cre…
  GET|HEAD        dosen/materials/{material} ...................... dosen.materials.show › MaterialController@show
  PUT|PATCH       dosen/materials/{material} .................. dosen.materials.update › MaterialController@update
  DELETE          dosen/materials/{material} ................ dosen.materials.destroy › MaterialController@destroy
  GET|HEAD        dosen/materials/{material}/edit ................. dosen.materials.edit › MaterialController@edit
  GET|HEAD        login ............................................................ login › LoginController@index
  POST            login .................................................................... LoginController@login
  POST            logout ......................................................... logout › LoginController@logout
  GET|HEAD        mahasiswa/courses ............................. mahasiswa.courses.index › CourseController@index
  GET|HEAD        mahasiswa/courses/{course} ...................... mahasiswa.courses.show › CourseController@show
  GET|HEAD        mahasiswa/submissions ................. mahasiswa.submissions.index › SubmissionController@index
  POST            mahasiswa/submissions ................. mahasiswa.submissions.store › SubmissionController@store
  GET|HEAD        mahasiswa/submissions/{submission} ...... mahasiswa.submissions.show › SubmissionController@show
  GET|HEAD        tentang ...................................................... tentang › TentangController@index

                                                                                               Showing [45] routes
```

2. Tandai setiap route yang menerima parameter model ({course}, {assignment}, dst).

- Parameter {course} 
```
  GET|HEAD        admin/courses/{course} .............................. admin.courses.show › CourseController@show
  PUT|PATCH       admin/courses/{course} .......................... admin.courses.update › CourseController@update
  DELETE          admin/courses/{course} ........................ admin.courses.destroy › CourseController@destroy
  GET|HEAD        admin/courses/{course}/edit ......................... admin.courses.edit › CourseController@edit
  GET|HEAD        dosen/courses/{course} .............................. dosen.courses.show › CourseController@show
  PUT|PATCH       dosen/courses/{course} .......................... dosen.courses.update › CourseController@update
  DELETE          dosen/courses/{course} ........................ dosen.courses.destroy › CourseController@destroy
  GET|HEAD        dosen/courses/{course}/assignments dosen.courses.assignments.index › AssignmentController@index
  POST            dosen/courses/{course}/assignments dosen.courses.assignments.store › AssignmentController@store
  GET|HEAD        dosen/courses/{course}/assignments/create dosen.courses.assignments.create › AssignmentControll…
  GET|HEAD        dosen/courses/{course}/edit ......................... dosen.courses.edit › CourseController@edit
  GET|HEAD        dosen/courses/{course}/materials ...... dosen.courses.materials.index › MaterialController@index
  POST            dosen/courses/{course}/materials ...... dosen.courses.materials.store › MaterialController@store
  GET|HEAD        dosen/courses/{course}/materials/create dosen.courses.materials.create › MaterialController@cre…
  GET|HEAD        mahasiswa/courses/{course} ...................... mahasiswa.courses.show › CourseController@show
```
  - Parameter {user}
```
  GET|HEAD        admin/users/{user} ...................................... admin.users.show › UserController@show
  PUT|PATCH       admin/users/{user} .................................. admin.users.update › UserController@update
  DELETE          admin/users/{user} ................................ admin.users.destroy › UserController@destroy
  GET|HEAD        admin/users/{user}/edit ................................. admin.users.edit › UserController@edit
```

  - Parameter {assignment}
```
  GET|HEAD        dosen/assignments/{assignment} .............. dosen.assignments.show › AssignmentController@show
  PUT|PATCH       dosen/assignments/{assignment} .......... dosen.assignments.update › AssignmentController@update
  DELETE          dosen/assignments/{assignment} ........ dosen.assignments.destroy › AssignmentController@destroy
  GET|HEAD        dosen/assignments/{assignment}/edit ......... dosen.assignments.edit › AssignmentController@edit
```

  - Parameter {material}
```
  GET|HEAD        dosen/materials/{material} ...................... dosen.materials.show › MaterialController@show
  PUT|PATCH       dosen/materials/{material} .................. dosen.materials.update › MaterialController@update
  DELETE          dosen/materials/{material} ................ dosen.materials.destroy › MaterialController@destroy
  GET|HEAD        dosen/materials/{material}/edit ................. dosen.materials.edit › MaterialController@edit
``` 
  - Parameter {submission}
```
  GET|HEAD        mahasiswa/submissions/{submission} ...... mahasiswa.submissions.show › SubmissionController@show
```

3. Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain? Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7.

Jawab : 

Berdasarkan hasil pemeriksaan `route:list`, terdapat beberapa route yang menggunakan parameter model seperti `{course}`, `{user}`, `{assignment}`, `{material}`, dan `{submission}`. Route yang memiliki parameter tersebut menggunakan konsep **Route Model Binding**, yaitu Laravel secara otomatis mengambil data berdasarkan parameter pada URL dan mengubahnya menjadi object model pada controller. Namun, Route Model Binding hanya bertugas mengambil data dan tidak secara otomatis memberikan batasan hak akses terhadap data tersebut. Oleh karena itu, diperlukan pemeriksaan authorization untuk memastikan pengguna hanya dapat mengakses data yang memang menjadi haknya.

Pada route **admin/courses/{course}**, pengguna yang seharusnya memiliki akses adalah admin karena route tersebut berada di dalam grup `admin` yang menggunakan middleware `role:admin`. Saat ini akses sudah dibatasi oleh middleware tersebut sehingga pengguna dengan role selain admin tidak dapat masuk ke route ini. Namun, pengecekan tambahan tetap diperlukan jika nantinya terdapat perubahan kebutuhan akses atau pembagian hak akses yang lebih kompleks.

Pada route **dosen/courses/{course}**, pengguna yang seharusnya dapat mengakses adalah dosen yang memiliki mata kuliah tersebut serta admin apabila diberikan hak akses penuh. Saat ini route hanya dilindungi oleh middleware `role:dosen`, yang berarti siapa pun yang memiliki role dosen dapat melewati pemeriksaan tersebut. Middleware tersebut belum dapat membedakan antara Dosen A dan Dosen B. Oleh karena itu, dosen A masih berpotensi mencoba mengakses mata kuliah milik dosen B melalui perubahan ID pada URL. Pencegahan masalah ini dilakukan dengan menambahkan pemeriksaan kepemilikan data menggunakan `abort_unless()` atau nantinya menggunakan Policy pada minggu 7.

Pada route **admin/users/{user}**, pengguna yang seharusnya memiliki akses adalah admin karena data pengguna merupakan bagian dari pengelolaan sistem. Saat ini route sudah memiliki perlindungan melalui middleware `role:admin`, sehingga pengguna dengan role dosen maupun mahasiswa tidak dapat mengakses halaman tersebut. Route ini tidak membutuhkan pengecekan kepemilikan seperti course karena pengelolaan user memang berada pada tanggung jawab admin.

Pada route **dosen/assignments/{assignment}**, pengguna yang seharusnya dapat mengakses adalah dosen yang memiliki assignment tersebut melalui mata kuliah yang dia kelola. Saat ini route telah menggunakan middleware `role:dosen`, sehingga hanya dosen yang dapat masuk. Selain itu, penggunaan `Route::scopeBindings()` membantu memastikan hubungan antara parent dan child model, yaitu assignment harus berada pada course yang sesuai. Namun, pengecekan apakah assignment tersebut benar-benar milik dosen yang sedang login masih membutuhkan authorization tambahan pada controller atau Policy.

Pada route **dosen/materials/{material}**, pengguna yang seharusnya dapat mengakses adalah dosen yang mengelola mata kuliah tempat material tersebut berada. Saat ini pembatasan yang tersedia hanya middleware `role:dosen`, sehingga sistem hanya mengetahui bahwa pengguna tersebut adalah dosen, tetapi belum mengetahui apakah material tersebut berasal dari course miliknya. Oleh karena itu, route ini masih membutuhkan pengecekan kepemilikan data agar dosen tidak dapat mengakses atau mengubah material milik dosen lain.

Pada route **mahasiswa/submissions/{submission}**, pengguna yang seharusnya dapat mengakses adalah mahasiswa yang membuat submission tersebut. Saat ini route hanya dibatasi oleh middleware `role:mahasiswa`, sehingga semua mahasiswa dapat melewati pemeriksaan tersebut. Jika seorang mahasiswa mengetahui ID submission mahasiswa lain, maka terdapat kemungkinan akses terhadap data yang bukan miliknya. Oleh karena itu, diperlukan pengecekan kepemilikan submission menggunakan `abort_unless()` atau Policy agar mahasiswa hanya dapat melihat submission miliknya sendiri.

Secara keseluruhan, middleware seperti `role:admin`, `role:dosen`, dan `role:mahasiswa` hanya melakukan pengecekan berdasarkan **jenis pengguna**, bukan berdasarkan **kepemilikan data tertentu**. Perlindungan terhadap IDOR membutuhkan pemeriksaan tambahan yang memastikan hubungan antara pengguna yang login dengan objek yang sedang diakses. Implementasi sementara dapat dilakukan menggunakan `abort_unless()`, kemudian pada minggu 7 dapat dirapikan menggunakan Laravel Policy agar aturan authorization lebih terstruktur.

4. Buat tabel di docs/minggu-05-<nama>.md berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.

Daftar Titik Rawan IDOR : 
| No | Route | Parameter Model | Data yang Diakses | Pengguna yang Seharusnya Boleh Mengakses | Risiko IDOR | Perlindungan Saat Ini | Perbaikan yang Direncanakan |
|----|-------|----------------|-------------------|------------------------------------------|------------|----------------------|-----------------------------|
| 1 | `/dosen/courses/{course}/edit` | `{course}` | Data mata kuliah | Dosen pemilik mata kuliah dan admin | Dosen A dapat mencoba mengedit mata kuliah milik Dosen B dengan mengganti ID course pada URL | Middleware `role:dosen` hanya mengecek role pengguna, belum mengecek kepemilikan course | Menambahkan pengecekan kepemilikan menggunakan `abort_unless()` dan refactor menjadi `CoursePolicy` |
| 2 | `/dosen/courses/{course}` | `{course}` | Detail mata kuliah | Dosen pemilik course, admin, dan mahasiswa sesuai kebutuhan akses | Pengguna dapat melihat detail course yang bukan haknya jika hanya mengandalkan ID pada URL | Route Model Binding hanya mengambil data berdasarkan ID | Menambahkan authorization berdasarkan relasi user dengan course |
| 3 | `/dosen/courses/{course}/assignments/{assignment}` | `{course}`, `{assignment}` | Data assignment pada suatu course | Dosen pengelola course | Assignment dapat diakses melalui manipulasi parameter jika tidak dicek kepemilikannya | Menggunakan `Route::scopeBindings()` untuk menjaga relasi parent-child, tetapi belum menggantikan authorization | Menambahkan Policy untuk memastikan dosen hanya mengelola assignment miliknya |
| 4 | `/dosen/assignments/{assignment}` | `{assignment}` | Detail assignment | Dosen pemilik assignment/course | Dosen dapat mencoba membuka assignment milik dosen lain jika mengetahui ID | Middleware `role:dosen` hanya memeriksa jenis pengguna | Menambahkan pengecekan ownership pada controller atau Policy |
| 5 | `/dosen/materials/{material}` | `{material}` | Data materi pembelajaran | Dosen pemilik course tempat materi berada | Dosen dapat mengubah atau menghapus materi milik dosen lain | Middleware `role:dosen` belum memeriksa hubungan material dengan dosen | Menambahkan authorization berdasarkan relasi material-course-dosen |
| 6 | `/dosen/courses/{course}/materials/{material}` | `{course}`, `{material}` | Materi pada course tertentu | Dosen pengelola course | Manipulasi parameter dapat digunakan untuk mengakses material dari course lain | `Route::scopeBindings()` membantu validasi hubungan model, tetapi belum mengecek user pemilik | Menambahkan Policy untuk pengecekan hak akses |
| 7 | `/mahasiswa/submissions/{submission}` | `{submission}` | Data pengumpulan tugas mahasiswa | Mahasiswa pemilik submission | Mahasiswa dapat melihat submission mahasiswa lain dengan mengganti ID submission | Middleware `role:mahasiswa` hanya mengecek role pengguna | Menambahkan pengecekan `submission.user_id === auth()->id()` menggunakan Policy |
| 8 | `/admin/users/{user}` | `{user}` | Data pengguna | Admin | Risiko perubahan data user jika terdapat kesalahan konfigurasi akses | Middleware `role:admin` sudah membatasi akses admin | Tetap menggunakan middleware dan dapat ditambahkan Policy untuk aturan lebih detail |


Maka titik rawan IDOR pada aplikasi KampusLMS terutama terdapat pada route yang menggunakan parameter model seperti `{course}`, `{assignment}`, `{material}`, dan `{submission}`.

Route Model Binding hanya memastikan Laravel dapat mengambil data berdasarkan identifier pada URL, tetapi tidak memastikan pengguna memiliki hak akses terhadap data tersebut.

Pencegahan sementara dilakukan menggunakan `abort_unless()`, sedangkan implementasi yang lebih terstruktur akan dilakukan menggunakan Laravel Policy pada minggu 7.

---
BREAK — Enam kerusakan (45 menit)


| No | Skenario Pengujian | Hasil yang Diharapkan | Hasil Pengamatan | Status | Kesimpulan |
|----|--------------------|-----------------------|------------------|--------|------------|
| 1 | Login sebagai mahasiswa A, kemudian mengakses submission milik mahasiswa B dengan mengganti ID pada URL | Sistem seharusnya menolak akses karena submission bukan milik mahasiswa yang login | Submission mahasiswa B masih dapat dibuka jika hanya menggunakan ID pada URL | Berhasil menemukan celah | Terjadi IDOR karena sistem belum melakukan pengecekan kepemilikan submission |
| 2 | Membuka `/courses/1/assignments/99` ketika assignment 99 sebenarnya milik course lain | Sistem seharusnya menolak karena assignment tidak memiliki hubungan dengan course tersebut | Assignment tetap dapat ditemukan jika hanya menggunakan ID assignment | Berhasil menemukan celah | Nested route belum melakukan validasi hubungan parent-child |
| 3 | Mengaktifkan `Route::scopeBindings()` kemudian mengulangi pengujian nomor 2 | Sistem seharusnya memastikan assignment benar-benar berada dalam course yang dipanggil | Laravel menolak request karena assignment tidak berada pada course tersebut | Berhasil | `scopeBindings()` mencegah akses child model yang tidak sesuai dengan parent model |
| 4 | Mendaftarkan middleware pada `app/Http/Kernel.php` seperti tutorial Laravel lama | Middleware seharusnya dapat didaftarkan pada file tersebut | File `app/Http/Kernel.php` tidak ditemukan pada Laravel 12 | Berhasil menemukan perbedaan struktur | Laravel 12 menggunakan `bootstrap/app.php` untuk konfigurasi middleware |
| 5 | Menambahkan `role:admin` pada grup route, lalu mengakses menggunakan akun dosen | Sistem seharusnya menolak akses karena role tidak sesuai | Akun dosen mendapatkan response `403 Forbidden` sebelum masuk controller | Berhasil | Middleware role berhasil membatasi akses berdasarkan jenis pengguna |
| 6 | Login sebagai dosen A, kemudian mencoba mengedit mata kuliah milik dosen B | Sistem seharusnya menolak walaupun keduanya memiliki role dosen | Dosen A masih dapat melewati middleware `role:dosen`, tetapi ditolak setelah pengecekan ownership menggunakan `abort_unless()` | Berhasil menemukan kelemahan | Middleware role tidak cukup untuk mencegah IDOR karena hanya mengecek role, bukan kepemilikan data |


Dari hasil pengujian, ditemukan bahwa pembatasan akses berdasarkan role seperti `role:dosen` atau `role:mahasiswa` belum cukup untuk mencegah IDOR. Middleware hanya memastikan jenis pengguna, tetapi tidak memastikan apakah pengguna tersebut memiliki hak terhadap objek tertentu.

Pencegahan IDOR membutuhkan pengecekan kepemilikan data menggunakan authorization seperti `abort_unless()` atau Laravel Policy. Pengujian nomor 6 menunjukkan bahwa dua pengguna dengan role yang sama tetap dapat memiliki hak akses berbeda terhadap data tertentu.


