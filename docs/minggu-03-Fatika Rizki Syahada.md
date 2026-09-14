### Nama : Fatika Rizki Syahada
#### NIM : 10241030

---
## READ

1. ERD 

<img src="image/ERD.jpeg" width="500">

2. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.

## `onDelete` setiap Foreign Key

| Foreign Key | Relasi | `onDelete` | Alasan |
|---|---|---|---|
| `courses.lecturer_id → users.id` | Dosen mengajar course | `restrict` | Dosen tidak bisa langsung dihapus jika masih mengajar suatu course. Hal ini untuk mencegah data course dan riwayat akademik ikut bermasalah. Course dapat dipindahkan terlebih dahulu ke dosen lain. |
| `course_user.course_id → courses.id` | Course memiliki peserta | `cascade` | Jika course dihapus, data peserta yang terdaftar di course tersebut juga ikut dihapus karena data tersebut sudah tidak diperlukan. |
| `course_user.user_id → users.id` | User terdaftar dalam course | `cascade` | Jika user dihapus, data pendaftarannya pada course juga ikut dihapus karena hubungan user dengan course tersebut sudah tidak diperlukan. |
| `materials.course_id → courses.id` | Course memiliki materi | `cascade` | Materi merupakan bagian dari course. Jika course dihapus, materi yang ada di dalamnya juga ikut dihapus agar tidak ada materi tanpa course. |
| `materials.uploaded_by → users.id` | User mengunggah materi | `set null` | Jika user yang mengunggah materi dihapus, materi tetap disimpan karena masih bisa digunakan. Hanya data pengunggahnya yang dikosongkan menjadi `NULL`. |
| `assignments.course_id → courses.id` | Course memiliki tugas | `cascade` | Tugas merupakan bagian dari course. Jika course dihapus, tugas yang ada di dalam course tersebut juga ikut dihapus. |
| `assignments.created_by → users.id` | User membuat tugas | `restrict` | User tidak bisa langsung dihapus jika masih tercatat sebagai pembuat tugas. Hal ini agar informasi pembuat tugas tetap jelas dan tidak hilang dari riwayat. |
| `submissions.assignment_id → assignments.id` | Assignment memiliki submission | `cascade` | Submission merupakan jawaban dari suatu tugas. Jika tugas dihapus, submission yang terkait juga ikut dihapus karena sudah tidak memiliki tugas yang menjadi acuannya. |
| `submissions.user_id → users.id` | User mengumpulkan tugas | `restrict` | User tidak bisa langsung dihapus jika masih memiliki data submission. Hal ini untuk menjaga riwayat pengumpulan tugas mahasiswa. |
| `grades.submission_id → submissions.id` | Submission memiliki nilai | `cascade` | Nilai berasal dari submission tertentu. Jika submission dihapus, nilai yang berkaitan dengannya juga ikut dihapus. |
| `grades.graded_by → users.id` | User memberikan nilai | `restrict` | User tidak bisa langsung dihapus jika masih tercatat sebagai pemberi nilai. Aar riwayat siapa yang memberikan nilai tetap jelas. |

3. Kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?

Jika seorang dosen masih terhubung dengan suatu mata kuliah, dosen tersebut tidak bisa langsung dihapus karena menggunakan `onDelete: restrict`. Mata kuliahnya tetap ada karena masih memiliki data seperti peserta, materi, tugas, submission, dan nilai yang perlu dipertahankan.

Jika dosen sudah tidak mengajar, mata kuliah sebaiknya dipindahkan terlebih dahulu ke dosen lain sebelum akun dosen dihapus. Sebenarnya bisa juga menggunakan `set null`, sehingga dosen dapat dihapus tetapi `lecturer_id` menjadi `NULL` dan mata kuliahnya tetap ada. Namun, pada rancangan ini `restrict` dipilih agar setiap mata kuliah yang masih digunakan tetap memiliki dosen yang jelas dan data akademiknya tetap aman.

4. Kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?

Kolom `grades.submission_id` menggunakan constraint **`unique`** karena satu submission hanya boleh memiliki satu nilai. Artinya, setiap mahasiswa yang mengumpulkan satu tugas hanya boleh memiliki satu data nilai untuk submission tersebut. Oleh karena itu, hubungan antara tabel `submissions` dan `grades` adalah **one-to-one (1:1)**.

Kalau hanya menggunakan **index biasa**, database masih memungkinkan satu submission memiliki beberapa data nilai. Misalnya, satu submission yang sama bisa memiliki nilai 80 dan 90. Hal tersebut dapat membuat data menjadi tidak konsisten karena satu submission seharusnya hanya memiliki satu nilai.

Dengan menggunakan **`unique`**, database akan memastikan bahwa setiap `submission_id` hanya dapat digunakan satu kali pada tabel `grades`. Jadi, satu submission hanya bisa memiliki maksimal satu data nilai.

Selain membantu mempercepat pencarian data, `unique` juga berfungsi sebagai aturan yang langsung diterapkan oleh database. Jadi, meskipun terjadi kesalahan pada aplikasi atau ada data yang dikirim secara tidak sengaja, database tetap mencegah satu submission memiliki lebih dari satu nilai.

---
## BREAK — Lima kerusakan (45 menit)

1. Hapus unique(['course_id','user_id']) dari course_user, lalu daftarkan mahasiswa yang sama dua kali

Ketika menghapus table unik dibawah ini : 
```php
$table->unique([
    'course_id',
    'user_id'
])
```

dan kita menambahkan course_id : 2 dan juga user_id : 3, maka hasilnya adalah true 

<img src="image/unique_constraint.png" width="500">

Pada percobaan pertama, constraint unique(['course_id', 'user_id']) pada tabel course_user dihapus. 

Constraint tersebut sebenarnya berfungsi untuk mencegah mahasiswa yang sama terdaftar lebih dari satu kali pada mata kuliah yang sama.


Setelah constraint dihapus, saya mencoba memasukkan data mahasiswa dengan user_id = 3 ke mata kuliah dengan course_id = 2 sebanyak dua kali.

 Kedua data tersebut berhasil disimpan oleh database tanpa menghasilkan error.
Kemudian dilakukan pengecekan jumlah data menggunakan query:

```
DB::table('course_user')
    ->where('course_id', 2)
    ->where('user_id', 3)
    ->count();
```
Hasilnya
= 2

Hasil tersebut menunjukkan bahwa data mahasiswa dan mata kuliah yang sama dapat tercatat dua kali. Hal ini membuktikan bahwa ketika unique constraint dihapus, database tidak lagi mencegah terjadinya data duplikat pada relasi mahasiswa dan mata kuliah.

2. Tambahkan role ke $fillable model User, lalu kirim request pembuatan user dengan role=admin lewat form yang tidak punya field role

Ketika kita menambah role kedalam fillable, maka hasilnya akan seperti berikut : 
```php
protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];
```

Hasilnya menunjukkan bahwa nilai admin berhasil masuk ke database. Hal ini terjadi karena role sudah dimasukkan ke $fillable, sehingga Laravel mengizinkan atribut tersebut diisi melalui mass assignment.

Percobaan ini menunjukkan bahwa memasukkan atribut sensitif seperti role ke dalam $fillable dapat menjadi masalah keamanan karena pengguna dapat mengirimkan nilai yang seharusnya hanya dapat ditentukan oleh sistem atau administrator.

3. Ganti seluruh $fillable dengan protected $guarded = []; lalu ulangi nomor 2

sebelumnya menggunakan : 
```php 
protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];
```

lalu diubah menjadi : 
```php
protected $guarded = [];
```

Hasilnya, atribut role tetap dapat diisi melalui mass assignment tanpa harus didaftarkan secara khusus ke $fillable.

Hal ini menunjukkan bahwa $guarded = [ ] membuat seluruh atribut pada model tidak memiliki perlindungan dari mass assignment. Kondisi tersebut berbahaya apabila terdapat atribut penting seperti role, is_admin, atau atribut lain yang seharusnya tidak dapat diubah langsung oleh pengguna.

Dari percobaan ini $fillable lebih aman karena developer dapat menentukan secara jelas atribut mana saja yang boleh diisi melalui mass assignment.

4. Kosongkan isi down() di satu migrasi, lalu jalankan php artisan migrate:refresh

awalnya : 
```php 
public function down(): void
    {
        Schema::dropIfExists('courses');
    }
```

isi down di kosongkan, menjadi : 
```php 
public function down(): void
    {
        
    }
```

Isi method down() pada migration dihapus. Setelah itu dijalankan:
php artisan migrate:refresh

Method down() digunakan untuk mengembalikan perubahan yang dibuat oleh migration.

Jika down() dikosongkan, Laravel tidak memiliki perintah untuk mengembalikan perubahan tersebut. Akibatnya, proses rollback atau refresh migration dapat mengalami masalah.
Dari percobaan ini dapat diketahui bahwa method down() penting dan sebaiknya dibuat agar migration dapat dikembalikan dengan benar.

5. Ubah restrictOnDelete pada lecturer_id menjadi cascadeOnDelete, lalu hapus satu dosen

awalnya lecturer_id : 
```php 
$table->foreignId('lecturer_id')
    ->constrained('users')
    ->restrictOnDelet();
```

diubah menjadi : 
```php 
$table->foreignId('lecturer_id')
    ->constrained('users')
    ->cascadeOnDelete();
```
Dengan cascadeOnDelete(), ketika dosen dihapus, data course yang berhubungan dengan dosen tersebut juga ikut terhapus.
Sedangkan restrictOnDelete() akan mencegah dosen dihapus jika masih digunakan oleh data lain.

Percobaan ini menunjukkan bahwa cascadeOnDelete() harus digunakan dengan hati-hati karena dapat menyebabkan data yang berhubungan ikut terhapus.

--- 
## BUILD — Milestone M1 (tugas terstruktur + belajar mandiri)


1. Seluruh migrasi sesuai Bagian 4 spesifikasi, termasuk semua constraint dan index. Reversible.

jawab:

Pada tahap ini dibuat seluruh migration berdasarkan struktur database yang sudah ditentukan. Setiap tabel dibuat dengan memperhatikan hubungan antar tabel, penggunaan constraint dan index, serta aturan ketika data yang saling berhubungan dihapus. Hal ini dilakukan agar struktur database dapat berjalan sesuai dengan kebutuhan sistem.

Di dalam migration digunakan **foreign key** untuk menghubungkan data antar tabel, **unique constraint** untuk mencegah data yang sama tersimpan lebih dari satu kali, dan **index** untuk membantu proses pencarian data agar lebih optimal. Salah satu contohnya adalah hubungan antara tabel mata kuliah dengan tabel pengguna yang berperan sebagai dosen.

```php
$table->foreignId('lecturer_id')
    ->constrained('users')
    ->restrictOnDelete();
```

Penggunaan `restrictOnDelete()` bertujuan untuk mencegah data dosen dihapus apabila dosen tersebut masih terhubung dengan mata kuliah. Dengan begitu, data mata kuliah tidak kehilangan dosen yang masih tercatat dan hubungan antar data tetap terjaga.

Selain membuat tabel dan relasinya, setiap migration juga dibuat **reversible**, yaitu dapat dibatalkan kembali. Hal ini dilakukan dengan menyediakan fungsi `down()`. Fungsi tersebut digunakan untuk mengembalikan perubahan database ketika proses rollback dilakukan.

Contoh:

```php
public function down(): void
{
    Schema::dropIfExists('courses');
}
```

Pengujian migration dilakukan menggunakan perintah:

```bash
php artisan migrate:fresh --seed
```

dan:

```bash
php artisan migrate:refresh
```

Dari hasil pengujian, seluruh migration dapat dijalankan dengan baik dan proses rollback juga berhasil dilakukan. Dengan demikian, migration yang dibuat sudah bersifat reversible.



2. Seluruh model dengan relasi lengkap sesuai Bagian 4.3, $fillable yang ketat, casts() sebagai method.
Pada tahap ini dibuat seluruh model berdasarkan tabel yang sudah dibuat pada database. Setiap model diberikan relasi yang sesuai dengan hubungan antar tabel menggunakan fitur **relationship** yang tersedia di Laravel.

Relasi antara mata kuliah dengan dosen menggunakan `belongsTo` karena setiap mata kuliah memiliki satu dosen yang bertanggung jawab.

Contoh:

```php
public function lecturer()
{
    return $this->belongsTo(User::class, 'lecturer_id');
}
```

Sedangkan hubungan antara mahasiswa dengan mata kuliah menggunakan `belongsToMany` karena satu mahasiswa dapat mengikuti banyak mata kuliah dan satu mata kuliah juga dapat diikuti oleh banyak mahasiswa. Hubungan tersebut menggunakan tabel pivot sebagai penghubung.

Contoh:

```php
public function courses()
{
    return $this->belongsToMany(Course::class);
}
```

Setiap model juga menggunakan `$fillable` untuk menentukan atribut apa saja yang boleh diisi melalui **mass assignment**. Hal ini dilakukan agar data yang tidak seharusnya diubah tidak dapat diisi secara sembarangan.

Contoh:

```php
protected $fillable = [
    'name',
    'email',
    'password',
];
```

Pada model `User`, atribut `role` tidak dimasukkan ke dalam `$fillable`. Hal ini karena `role` menentukan hak akses pengguna. Jika `role` dapat diisi melalui mass assignment, pengguna berpotensi mengubah role mereka sendiri tanpa melalui proses yang dikontrol oleh aplikasi.

Selain itu, model juga menggunakan method `casts()` untuk mengatur bagaimana tipe data tertentu diproses oleh Laravel secara otomatis.

Contoh:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

Dengan pengaturan tersebut, setiap model memiliki relasi yang sesuai dengan struktur database, serta memiliki pengaturan `$fillable` dan `casts()` untuk membantu menjaga keamanan dan pengelolaan data.

3. Factory + seeder yang memenuhi Bagian 4.4, termasuk 3 akun demo.

Jawab: 

Pada tahap ini dibuat **Factory** dan **Seeder** untuk membantu menyediakan data awal pada database. Factory digunakan untuk membuat data secara otomatis, terutama data dummy, sedangkan Seeder digunakan untuk memasukkan data awal yang memang dibutuhkan oleh aplikasi.

Seeder dibuat untuk menghasilkan tiga akun demo, yaitu akun **admin, dosen, dan mahasiswa**.

Contoh pembuatan akun:

```php
User::factory()->create([
    'name' => 'Admin Demo',
    'email' => 'admin@mail.com',
    'role' => 'admin',
]);
```

Selain membuat akun demo, Seeder juga digunakan untuk membuat data pendukung lainnya, seperti data mata kuliah dan hubungan antara mahasiswa dengan mata kuliah melalui tabel pivot.

Pengujian Seeder dilakukan menggunakan perintah:

```bash
php artisan migrate:fresh --seed
```

Dari hasil pengujian, database berhasil dibuat ulang dan data demo berhasil dimasukkan sesuai dengan data yang telah ditentukan pada Seeder.

4. CRUD Mata Kuliah berfungsi penuh (index, create, store, show, edit, update, destroy).

Pada tahap ini dibuat fitur **CRUD Mata Kuliah** untuk mengelola data mata kuliah. CRUD yang dibuat mencakup proses menampilkan data, menambahkan data, melihat detail data, mengubah data, memperbarui data, dan menghapus data.

Route untuk CRUD Mata Kuliah dibuat menggunakan:

```php
Route::resource('courses', CourseController::class);
```

Dengan menggunakan `Route::resource`, route yang dibutuhkan untuk proses CRUD dapat dibuat berdasarkan method yang tersedia pada `CourseController`.

Controller Mata Kuliah digunakan untuk menangani seluruh proses tersebut. Prosesnya dimulai dari menampilkan daftar mata kuliah pada halaman `index`, menampilkan form untuk menambahkan data, menyimpan data baru ke database, menampilkan detail mata kuliah, menampilkan form edit, memperbarui data, hingga menghapus data mata kuliah.

Dari hasil implementasi dan pengujian, seluruh fungsi CRUD Mata Kuliah dapat dijalankan sesuai dengan kebutuhan sistem.

5. CRUD Pengguna berfungsi penuh, dengan role tidak di $fillable melainkan diisi eksplisit di controller.


Pada tahap ini dibuat fitur **CRUD Pengguna** dengan tetap memperhatikan keamanan pada atribut `role`. Atribut `role` tidak dimasukkan ke dalam `$fillable` karena berkaitan dengan hak akses pengguna.

Model `User` menggunakan:

```php
protected $fillable = [
    'name',
    'email',
    'password',
];
```

Atribut `role` sengaja tidak dimasukkan ke dalam `$fillable`. Jika `role` dimasukkan ke dalam `$fillable`, nilai tersebut dapat diisi melalui mass assignment. Hal ini dapat menjadi masalah karena pengguna bisa saja mengirim nilai `role` yang tidak seharusnya mereka miliki, misalnya mengubah role menjadi `admin`.

Oleh karena itu, pengisian `role` dilakukan secara eksplisit melalui controller.

Contoh:

```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => $request->password,
]);

$user->role = $request->role;
$user->save();
```

Dengan cara tersebut, pengisian `role` dilakukan melalui proses yang sudah ditentukan di dalam aplikasi dan tidak dilakukan melalui `$fillable`.

CRUD Pengguna yang dibuat mencakup fungsi untuk menampilkan daftar pengguna, membuat pengguna baru, menyimpan data pengguna, melihat detail pengguna, mengubah data pengguna, memperbarui data pengguna, serta menghapus pengguna.


6. CI GitHub Actions aktif dan hijau: migrate:fresh --seed sukses, migrate:refresh sukses, .env tidak ter-commit.

Pada tahap ini dibuat **workflow GitHub Actions** untuk melakukan pengujian project secara otomatis. Workflow ini membantu memastikan bahwa project dapat menjalankan proses instalasi dan pengujian database dengan baik.

File workflow dibuat pada:

```text
.github/workflows/laravel.yml
```

Workflow tersebut digunakan untuk melakukan beberapa proses, seperti menginstal dependency Laravel, membuat konfigurasi environment, menyiapkan database, menjalankan migration, serta menjalankan Seeder.

Pengujian dilakukan menggunakan perintah:

```bash
php artisan migrate:fresh --seed
```

dan:

```bash
php artisan migrate:refresh
```

Hasil pengujian menunjukkan bahwa GitHub Actions dapat berjalan tanpa error. Perintah `migrate:fresh --seed` berhasil membuat ulang database sekaligus menjalankan Seeder. Sementara itu, `migrate:refresh` berhasil melakukan rollback migration dan menjalankan migration kembali.

Selain itu, file `.env` tidak dimasukkan ke dalam repository karena sudah dicantumkan di dalam `.gitignore`. Hal ini dilakukan untuk mencegah konfigurasi lokal dan informasi yang bersifat sensitif ikut tersimpan di repository GitHub.
