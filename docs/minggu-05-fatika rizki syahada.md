### Nama : Fatika Rizki Syahada
#### NIM : 10241030

---
# READ 

1. Jalankan php artisan route:list --except-vendor. Salin keluarannya ke catatan.

Perintah yang digunakan:

```bash
php artisan route:list --except-vendor
```
```
GET|HEAD        / ........................................ dashboard › routes/web.php:9
  GET|HEAD        courses ........................ courses.index › CourseController@index
  POST            courses ........................ courses.store › CourseController@store
  GET|HEAD        courses/create ............... courses.create › CourseController@create
  GET|HEAD        courses/{course} ................. courses.show › CourseController@show
  PUT|PATCH       courses/{course} ............. courses.update › CourseController@update
  DELETE          courses/{course} ........... courses.destroy › CourseController@destroy
  GET|HEAD        courses/{course}/edit ............ courses.edit › CourseController@edit
  GET|HEAD        tentang ............................. tentang › TentangController@index
  GET|HEAD        users .............................. users.index › UserController@index
  POST            users .............................. users.store › UserController@store
  GET|HEAD        users/create ..................... users.create › UserController@create
  GET|HEAD        users/{user} ......................... users.show › UserController@show
  PUT|PATCH       users/{user} ..................... users.update › UserController@update
  DELETE          users/{user} ................... users.destroy › UserController@destroy
  GET|HEAD        users/{user}/edit .................... users.edit › UserController@edit
```
---

2. Tandai setiap route yang menerima parameter model ({course}, {assignment}, dst).

* `admin/courses/{course}`
* `admin/courses/{course}/edit`
* `admin/users/{user}`
* `admin/users/{user}/edit`
* `dosen/courses/{course}`
* `dosen/courses/{course}/edit`
* `dosen/courses/{course}/assignments`
* `dosen/courses/{course}/assignments/create`
* `dosen/assignments/{assignment}`
* `dosen/assignments/{assignment}/edit`
* `dosen/courses/{course}/materials`
* `dosen/courses/{course}/materials/create`
* `dosen/materials/{material}`
* `dosen/materials/{material}/edit`
* `mahasiswa/courses/{course}`

Parameter tersebut perlu diperhatikan karena pengguna dapat mengubah nilai parameter pada URL. Jika tidak ada pemeriksaan akses, pengguna dapat mencoba mengakses data yang bukan miliknya.

3. Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain?

Jawab:
Untuk setiap route yang memiliki parameter seperti {course}, {assignment}, atau {submission}, aksesnya dibatasi berdasarkan peran pengguna melalui middleware auth dan role. Admin hanya dapat mengakses route admin, dosen hanya dapat mengakses route dosen, dan mahasiswa hanya dapat mengakses route mahasiswa. Namun, pembatasan berdasarkan role saja belum cukup untuk mencegah IDOR, karena pengguna dengan role yang sama masih mungkin mengakses data milik pengguna lain. Oleh karena itu, beberapa route yang berhubungan dengan data tertentu masih memerlukan pemeriksaan kepemilikan menggunakan


4. Daftar Titik Rawan IDOR

IDOR (*Insecure Direct Object Reference*) adalah kondisi ketika pengguna dapat mengakses objek atau data tertentu dengan mengubah identifier pada URL atau request tanpa adanya pemeriksaan hak akses yang sesuai.

| No | Method    | Endpoint / Route        | Titik Rawan | Skenario Bahaya IDOR (Jika Tanpa Otorisasi)                                                                                                                                              |
| -- | --------- | ----------------------- | ----------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1  | GET       | `courses/{course}`      | `{course}`  | Pengguna mencoba mengganti ID mata kuliah pada URL dan dapat melihat detail mata kuliah yang seharusnya tidak bisa diakses, misalnya mata kuliah milik dosen lain atau yang masih draft. |
| 2  | GET       | `courses/{course}/edit` | `{course}`  | Pengguna mengganti ID pada URL dan berhasil membuka halaman edit mata kuliah yang bukan miliknya.                                                                                        |
| 3  | PUT/PATCH | `courses/{course}`      | `{course}`  | Dosen A mengubah ID mata kuliah menjadi milik Dosen B melalui Postman atau cURL, sehingga dapat mengubah data mata kuliah tersebut.                                                      |
| 4  | DELETE    | `courses/{course}`      | `{course}`  | Pengguna mencoba mengirim request DELETE dengan ID mata kuliah milik orang lain dan berhasil menghapusnya.                                                                               |
| 5  | GET       | `users/{user}`          | `{user}`    | Pengguna mengganti ID pada URL dan dapat melihat informasi pengguna lain yang seharusnya tidak dapat diakses.                                                                            |
| 6  | GET       | `users/{user}/edit`     | `{user}`    | Mahasiswa mengganti ID pengguna pada URL dan berhasil membuka halaman edit milik Admin atau Dosen.                                                                                       |
| 7  | PUT/PATCH | `users/{user}`          | `{user}`    | User A mengirim request dengan ID User B dan dapat mengubah data akun tersebut, seperti email, password, atau bahkan role pengguna.                                                      |
| 8  | DELETE    | `users/{user}`          | `{user}`    | Mahasiswa mencoba menggunakan ID akun Admin pada request DELETE dan berhasil menghapus akun tersebut.                                                                                    |
