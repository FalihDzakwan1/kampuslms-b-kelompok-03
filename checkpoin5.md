from pathlib import Path

content = r"""# CHECKPOINT MINGGU 6 — REST API, Sanctum, API Resource, dan Rate Limiting

Dokumen ini berisi jawaban lengkap untuk checkpoint Minggu 6 beserta langkah pengecekan langsung pada project Laravel.

---

## 1. Kenapa mengembalikan model mentah berbahaya? Peragakan kebocorannya.

### Penjelasan

Mengembalikan model mentah seperti:

```php
return response()->json(User::all());
```

berbahaya karena seluruh atribut model yang tidak disembunyikan dapat ikut dikirim ke client. Jika model memiliki field sensitif, field tersebut dapat ikut muncul di response API.

Contoh data sensitif yang berisiko ikut keluar:

- email
- hash password
- remember token
- token internal
- field database lain yang seharusnya tidak diketahui client

Karena itu API sebaiknya menggunakan **API Resource**. API Resource bekerja seperti whitelist. Hanya field yang ditulis di resource yang akan dikirim ke client.

---

### File yang dicek

Cek:

```text
app/Models/User.php
```

Cari bagian:

```php
protected $hidden = [
    'password',
    'remember_token',
];
```

Jika `password` berada di `$hidden`, maka hash password tidak akan tampil ketika model diubah menjadi JSON.

---

### Langkah pengujian kebocoran

Buka:

```text
routes/api.php
```

Tambahkan route uji sementara:

```php
use App\Models\User;

Route::get('/test-users', function () {
    return response()->json(User::all());
});
```

Jalankan server:

```bash
php artisan serve
```

Buka:

```text
http://127.0.0.1:8000/api/test-users
```

Jika `password` disembunyikan melalui `$hidden`, hash password mungkin tidak muncul.

Untuk demonstrasi lokal, paksa field password terlihat:

```php
Route::get('/test-users', function () {
    return response()->json(
        User::all()->makeVisible(['password'])
    );
});
```

Akses kembali:

```text
http://127.0.0.1:8000/api/test-users
```

Contoh hasil:

```json
[
    {
        "id": 1,
        "name": "Admin",
        "email": "admin@example.com",
        "password": "$2y$12$..."
    }
]
```

Setelah pengujian selesai, hapus endpoint uji tersebut.

---

### Solusi yang benar

Gunakan API Resource.

Buat resource:

```bash
php artisan make:resource UserResource
```

Cek file:

```text
app/Http/Resources/UserResource.php
```

Contoh:

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
    ];
}
```

Dengan cara ini hanya field yang dipilih yang akan keluar.

---

### Kesimpulan

Mengembalikan model mentah berbahaya karena dapat membocorkan field yang tidak seharusnya dikirim ke client. Walaupun Laravel dapat menyembunyikan field tertentu melalui `$hidden`, API Resource tetap lebih aman karena menentukan secara eksplisit data yang boleh keluar.

---

## 2. Apa beda 401 dan 403? Tunjukkan satu contoh masing-masing.

### Perbedaan

| Status | Arti | Kondisi |
|---|---|---|
| `401 Unauthorized` | User belum terautentikasi | Token tidak ada, salah, atau sudah tidak valid |
| `403 Forbidden` | User sudah terautentikasi tetapi tidak punya izin | Token valid tetapi role atau hak akses tidak sesuai |

---

## Contoh 401 Unauthorized

### File yang dicek

Buka:

```text
routes/api.php
```

Pastikan endpoint dilindungi:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/courses', [CourseController::class, 'index']);
});
```

### Langkah pengujian

Panggil endpoint tanpa token:

```bash
curl -i http://127.0.0.1:8000/api/v1/courses \
-H "Accept: application/json"
```

Hasil yang diharapkan:

```text
HTTP/1.1 401 Unauthorized
```

Body:

```json
{
    "message": "Unauthenticated."
}
```

### Kesimpulan 401

User belum berhasil membuktikan identitasnya karena tidak mengirim token valid.

---

## Contoh 403 Forbidden

### File yang dicek

Cek controller atau policy yang membatasi role.

Contoh lokasi:

```text
app/Http/Controllers/Api/AssignmentController.php
```

atau:

```text
app/Policies/AssignmentPolicy.php
```

Pastikan mahasiswa tidak boleh membuat assignment.

### Langkah pengujian

1. Login sebagai mahasiswa.
2. Ambil token Sanctum.
3. Panggil endpoint:

```bash
curl -i -X POST http://127.0.0.1:8000/api/v1/assignments \
-H "Accept: application/json" \
-H "Authorization: Bearer TOKEN_MAHASISWA"
```

Hasil yang diharapkan:

```text
HTTP/1.1 403 Forbidden
```

### Kesimpulan 403

Mahasiswa sudah login dan tokennya valid, tetapi tidak memiliki hak untuk membuat assignment.

---

## 3. Kenapa `routes/api.php` tidak ada secara default di Laravel 12? Bagaimana mengaktifkannya?

Pada Laravel 12, file `routes/api.php` tidak langsung tersedia pada instalasi awal. API perlu diaktifkan menggunakan perintah:

```bash
php artisan install:api
```

Perintah ini mengaktifkan dukungan API, membuat file `routes/api.php`, memasang Laravel Sanctum, dan memperbarui konfigurasi routing aplikasi.

---

### File yang dicek

Setelah menjalankan:

```bash
php artisan install:api
```

cek:

```text
bootstrap/app.php
```

Cari:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    );
```

Bagian penting:

```php
api: __DIR__.'/../routes/api.php',
```

Artinya Laravel sekarang membaca route dari:

```text
routes/api.php
```

---

### Cek file API

Pastikan file berikut tersedia:

```text
routes/api.php
```

Contoh route:

```php
use App\Http\Controllers\Api\CourseController;

Route::get('/v1/courses', [CourseController::class, 'index']);
```

Karena `routes/api.php` otomatis memiliki prefix `/api`, URL akhirnya menjadi:

```text
/api/v1/courses
```

---

### Verifikasi

Jalankan:

```bash
php artisan route:list --path=api
```

Cari:

```text
GET|HEAD   api/v1/courses
```

---

### Kesimpulan

`routes/api.php` tidak aktif secara default pada Laravel 12. API diaktifkan melalui `php artisan install:api`. Setelah itu Laravel menambahkan konfigurasi API di `bootstrap/app.php` sehingga route dalam `routes/api.php` dapat digunakan.

---

## 4. Apa fungsi `whenLoaded()`? Apa yang terjadi tanpanya?

### Penjelasan

`whenLoaded()` digunakan di API Resource untuk hanya menampilkan relasi apabila relasi tersebut sudah di-load sebelumnya oleh controller.

Contoh:

```php
'lecturer' => new UserResource(
    $this->whenLoaded('lecturer')
),
```

Artinya relasi `lecturer` hanya akan dimasukkan ke response jika controller sudah melakukan eager loading.

---

### File yang dicek

Cek resource:

```text
app/Http/Resources/CourseResource.php
```

Contoh:

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'code' => $this->code,
        'name' => $this->name,
        'lecturer' => new UserResource(
            $this->whenLoaded('lecturer')
        ),
    ];
}
```

Lalu cek controller:

```text
app/Http/Controllers/Api/CourseController.php
```

Contoh:

```php
$query = Course::query()
    ->with('lecturer');
```

Bagian:

```php
->with('lecturer')
```

melakukan eager loading.

---

### Pengujian dengan eager loading

Gunakan:

```php
Course::query()->with('lecturer');
```

Lalu panggil:

```text
GET /api/v1/courses
```

Cek query menggunakan Telescope atau Debugbar.

Query biasanya lebih efisien, misalnya:

```text
select * from courses;
select * from users where id in (...);
```

---

### Pengujian tanpa eager loading

Ubah sementara:

```php
Course::query()->with('lecturer');
```

menjadi:

```php
Course::query();
```

Kemudian akses lagi endpoint daftar course.

Jika relasi dipanggil secara langsung tanpa pengamanan yang tepat, dapat terjadi query tambahan untuk setiap course:

```text
select * from courses;
select * from users where id = 1;
select * from users where id = 2;
select * from users where id = 3;
```

Kondisi tersebut disebut **N+1 Query Problem**.

`whenLoaded()` membantu mencegah resource memicu pemanggilan relasi yang belum dimuat.

---

### Kesimpulan

`whenLoaded()` memastikan relasi hanya dimasukkan ke response jika sudah di-load. Jika eager loading tidak digunakan dan relasi tetap diakses berulang, aplikasi dapat mengalami N+1 query yang meningkatkan jumlah query database dan menurunkan performa.

---

## 5. Kenapa pesan gagal login tidak boleh membedakan email salah dan password salah?

Jika sistem memberikan pesan berbeda seperti:

```text
Email tidak terdaftar
```

dan:

```text
Password salah
```

penyerang dapat mengetahui apakah suatu email terdaftar.

Teknik ini disebut **user enumeration**.

---

### Contoh kondisi berbahaya

```php
if (! $user) {
    return response()->json([
        'message' => 'Email tidak terdaftar'
    ], 422);
}

if (! Hash::check($request->password, $user->password)) {
    return response()->json([
        'message' => 'Password salah'
    ], 422);
}
```

Jika seseorang mencoba:

```text
admin@kampus.test
```

dan sistem menjawab:

```text
Password salah
```

maka orang tersebut tahu bahwa email tersebut memang terdaftar.

---

### File yang dicek

Cek:

```text
app/Http/Controllers/Api/AuthController.php
```

atau lokasi controller login pada project.

Cari method:

```php
login()
```

Implementasi yang lebih aman:

```php
$user = User::where('email', $request->email)->first();

if (! $user || ! Hash::check($request->password, $user->password)) {
    throw ValidationException::withMessages([
        'email' => ['Email atau kata sandi salah.'],
    ]);
}
```

---

### Langkah pengujian

#### Tes 1: email salah

Kirim:

```text
email=tidakada@test.com
password=salah
```

#### Tes 2: email benar, password salah

Kirim:

```text
email=dosen@kampuslms.test
password=salah
```

Kedua kondisi harus menghasilkan pesan yang sama:

```json
{
    "message": "Email atau kata sandi salah."
}
```

---

### Kesimpulan

Pesan login tidak boleh membedakan email salah dan password salah karena dapat membocorkan keberadaan akun. Menggunakan pesan yang sama mengurangi risiko user enumeration.

---

## 6. Kenapa endpoint login wajib di-throttle? Berapa nilai yang dipakai dan mengapa?

### Penjelasan

Endpoint login harus dibatasi karena login merupakan target utama serangan brute force.

Tanpa rate limiting, seseorang dapat mencoba banyak kombinasi email dan password dalam waktu singkat.

Contoh:

```text
percobaan 1
percobaan 2
percobaan 3
...
percobaan 1000
```

Jika tidak ada pembatasan, server akan tetap memproses seluruh percobaan.

---

### File yang dicek

Buka:

```text
routes/api.php
```

Cari route login.

Contoh:

```php
Route::post('/v1/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');
```

Arti:

```text
5 request dalam 1 menit
```

---

### Nilai yang digunakan

Untuk endpoint login:

```php
throttle:5,1
```

Artinya maksimal 5 request per menit.

Untuk endpoint API umum dapat menggunakan:

```php
throttle:60,1
```

Artinya maksimal 60 request per menit.

---

### Kenapa login lebih ketat?

Karena endpoint login menerima password dan berisiko digunakan untuk brute force. Oleh karena itu login diberi batas lebih kecil dibanding endpoint API biasa.

---

### Langkah pengujian

Jika throttle aktif:

```bash
for i in {1..10}; do
  curl -i -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -d "email=test@test.com" \
  -d "password=salah"
done
```

Setelah melewati batas, hasil yang diharapkan:

```text
429 Too Many Requests
```

---

### Pengujian tanpa throttle

Hapus sementara:

```php
->middleware('throttle:5,1')
```

Lalu ulangi percobaan login.

Jika semua request tetap diproses tanpa `429`, berarti brute force tidak dibatasi.

Setelah pengujian, pasang kembali throttle.

---

### Kesimpulan

Endpoint login wajib menggunakan rate limiting untuk membatasi percobaan autentikasi. Nilai yang digunakan adalah `5 request per menit` untuk login karena endpoint login memiliki risiko brute force lebih tinggi. Endpoint API umum dapat menggunakan batas yang lebih longgar seperti `60 request per menit`.

---

# Ringkasan Checkpoint

| No | Topik | Inti Jawaban |
|---|---|---|
| 1 | Model mentah | Berisiko membocorkan field sensitif. Gunakan API Resource |
| 2 | 401 vs 403 | 401 = belum login/token invalid, 403 = sudah login tetapi tidak berhak |
| 3 | `routes/api.php` | Diaktifkan melalui `php artisan install:api` |
| 4 | `whenLoaded()` | Hanya menyertakan relasi yang sudah di-load dan membantu mencegah N+1 |
| 5 | Pesan login | Harus sama agar tidak terjadi user enumeration |
| 6 | Throttle login | Membatasi brute force, contoh `throttle:5,1` |

---

# Kuis 6

## 1. Apa itu REST?

REST adalah pendekatan arsitektur untuk membangun komunikasi antara client dan server melalui HTTP.

Resource biasanya direpresentasikan melalui URL.

Contoh:

```text
GET /api/v1/courses
```

digunakan untuk mengambil daftar course.

Contoh method umum:

| Method | Fungsi |
|---|---|
| `GET` | Mengambil data |
| `POST` | Membuat data |
| `PUT/PATCH` | Memperbarui data |
| `DELETE` | Menghapus data |

---

## 2. Status Code

| Status | Arti |
|---|---|
| `200 OK` | Request berhasil |
| `201 Created` | Data berhasil dibuat |
| `204 No Content` | Berhasil tetapi tidak ada body response |
| `401 Unauthorized` | Belum terautentikasi |
| `403 Forbidden` | Sudah login tetapi tidak memiliki izin |
| `404 Not Found` | Data atau endpoint tidak ditemukan |
| `422 Unprocessable Entity` | Validasi gagal |
| `429 Too Many Requests` | Terlalu banyak request |

---

## 3. Apa itu API Resource?

API Resource adalah fitur Laravel untuk mengatur bentuk data yang dikirim melalui API.

Contoh:

```php
return [
    'id' => $this->id,
    'code' => $this->code,
    'name' => $this->name,
];
```

Keuntungan:

- mengontrol field yang keluar
- mengurangi risiko kebocoran data
- membuat response konsisten
- mempermudah pengelolaan relasi

---

## 4. Apa itu Sanctum?

Laravel Sanctum adalah sistem autentikasi untuk API.

Saat login, password tetap diperiksa menggunakan hash.

Contoh:

```php
Hash::check($request->password, $user->password)
```

Jika login berhasil, Sanctum membuat token.

Contoh:

```php
$user->createToken('api')->plainTextToken;
```

Token kemudian digunakan pada request berikutnya:

```text
Authorization: Bearer TOKEN
```

Jadi:

```text
Login
↓
Cek email dan password
↓
Password cocok
↓
Buat token Sanctum
↓
Request API berikutnya menggunakan token
```

Route dapat dilindungi:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/courses', [CourseController::class, 'index']);
});
```

---

## 5. Apa itu Rate Limiting?

Rate limiting membatasi jumlah request yang dapat dilakukan client dalam jangka waktu tertentu.

Contoh:

```php
throttle:5,1
```

berarti maksimal 5 request dalam 1 menit.

Jika melewati batas:

```text
429 Too Many Requests
```

Rate limiting penting untuk:

- mengurangi brute force
- mengurangi spam request
- menjaga beban server
- melindungi endpoint sensitif seperti login

---

# Checklist Pengecekan Project

## Routing API

Cek:

```text
bootstrap/app.php
```

Pastikan:

```php
api: __DIR__.'/../routes/api.php',
```

Cek:

```text
routes/api.php
```

Kemudian jalankan:

```bash
php artisan route:list --path=api
```

---

## Authentication Sanctum

Cek:

```text
routes/api.php
```

Pastikan endpoint penting menggunakan:

```php
auth:sanctum
```

Cek token melalui proses login dan request menggunakan:

```text
Authorization: Bearer TOKEN
```

---

## Course API

Cek:

```text
app/Http/Controllers/Api/CourseController.php
```

Pastikan:

```php
Course::query()
```

menggunakan eager loading jika membutuhkan relasi:

```php
->with('lecturer')
```

Cek:

```text
app/Http/Resources/CourseResource.php
```

Pastikan relasi menggunakan:

```php
$this->whenLoaded('lecturer')
```

---

## Login

Cek:

```text
app/Http/Controllers/Api/AuthController.php
```

Pastikan pesan gagal login tidak membedakan email salah dan password salah.

Contoh:

```php
'Email atau kata sandi salah.'
```

Cek:

```text
routes/api.php
```

Pastikan route login memiliki:

```php
throttle:5,1
```

---

## HTTP Status

Pastikan API menggunakan status sesuai kondisi:

```text
200 = berhasil
201 = berhasil membuat data
204 = berhasil tanpa isi
401 = belum terautentikasi
403 = tidak memiliki izin
404 = tidak ditemukan
422 = validasi gagal
429 = terlalu banyak request
```

---

# Kesimpulan Akhir

API yang baik tidak hanya harus dapat mengembalikan data. API juga harus mengatur keamanan, format response, autentikasi, otorisasi, performa query, dan pembatasan request.

Pada implementasi Laravel, hal tersebut dapat dilakukan dengan:

- API Resource untuk mengontrol response
- Sanctum untuk autentikasi token
- `auth:sanctum` untuk melindungi endpoint
- status code yang tepat untuk membedakan jenis kegagalan
- eager loading dan `whenLoaded()` untuk menghindari query yang tidak efisien
- pesan login generik untuk menghindari user enumeration
- rate limiting untuk mengurangi brute force
"""

path = Path("/mnt/data/checkpoint_minggu_6_api.md")
path.write_text(content, encoding="utf-8")
print(path)
