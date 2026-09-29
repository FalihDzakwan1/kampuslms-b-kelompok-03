#### Nama: Falih Dzakwan
#### NIM : 10241028

READ

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
