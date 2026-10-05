# Jawaban dan Pengecekan Checkpoint Minggu 4

## Pengelolaan State: Session, Validasi, dan Data yang Banyak

Dokumen ini berisi jawaban Checkpoint Minggu 4 sekaligus cara melakukan pengecekan langsung pada project Laravel 12 KampusLMS.

---

## 1. Kenapa validasi di JavaScript tidak dianggap keamanan? Peragakan cara melewatinya.

### Jawaban

Validasi JavaScript tidak dianggap sebagai mekanisme keamanan utama karena JavaScript berjalan di sisi client atau browser pengguna. Pengguna memiliki kendali terhadap browser sehingga validasi tersebut dapat diubah, dinonaktifkan, atau dilewati.

JavaScript tetap berguna untuk memberikan feedback secara langsung dan mencegah pengguna biasa mengirim input yang salah. Namun, server tetap harus melakukan validasi sendiri karena request dapat dikirim tanpa melalui JavaScript.

Contohnya, apabila input SKS hanya boleh bernilai 1 sampai 6:

```html
<input type="number" name="sks" min="1" max="6">
```

Browser biasanya akan mencegah pengguna mengirim nilai seperti `99`. Akan tetapi, pembatasan tersebut dapat dilewati menggunakan DevTools atau dengan mengirim request langsung.

### File yang dicek

Untuk validasi frontend, periksa form mata kuliah, misalnya:

```text
resources/views/courses/create.blade.php
resources/views/courses/edit.blade.php
```

Untuk validasi server, periksa:

```text
app/Http/Requests/StoreCourseRequest.php
app/Http/Requests/UpdateCourseRequest.php
```

Pastikan terdapat aturan seperti:

```php
'sks' => ['required', 'integer', 'between:1,6'],
```

### Cara mengecek dengan DevTools

1. Jalankan aplikasi Laravel.
2. Buka halaman tambah mata kuliah.
3. Tekan `F12` atau buka **Developer Tools**.
4. Pilih tab **Elements**.
5. Cari elemen input `sks`.
6. Jika terdapat `max="6"`, ubah menjadi nilai yang lebih besar atau hapus atribut tersebut.
7. Masukkan `99` pada input SKS.
8. Kirim form.

Jika validasi Laravel benar, meskipun validasi browser berhasil dilewati, server tetap menolak `sks=99`.

### Cara mengecek dengan curl

Request juga dapat dikirim tanpa menggunakan form dan JavaScript sama sekali. Pada aplikasi yang menggunakan proteksi CSRF, token dan cookie session yang sesuai tetap diperlukan. Contoh konsep request-nya:

```bash
curl -X POST http://kampuslms.test/courses \
  -H "X-CSRF-TOKEN: <token>" \
  -b cookies.txt \
  -d "code=IF101" \
  -d "name=Pemrograman Web" \
  -d "sks=99" \
  -d "lecturer_id=1" \
  -d "status=active"
```

### Hasil yang diharapkan

Jika validasi server sudah benar, Laravel harus menolak nilai `99` karena aturan:

```php
between:1,6
```

Kesimpulannya, JavaScript membantu pengalaman pengguna, sedangkan validasi Laravel menjadi pemeriksaan yang tetap berlaku meskipun frontend dilewati.

---

## 2. Apa yang dikembalikan `$request->validated()` dan kenapa lebih aman daripada `$request->all()`?

### Jawaban

`$request->validated()` mengembalikan data yang termasuk dalam aturan Form Request dan telah lolos proses validasi.

Sebaliknya, `$request->all()` mengambil seluruh input yang dikirim melalui request, termasuk field tambahan yang mungkin tidak diharapkan oleh aplikasi.

Misalnya pengguna mengirim:

```text
code=IF101
name=Pemrograman Web
sks=3
lecturer_id=1
status=active
is_admin=1
```

Sedangkan Form Request hanya memiliki aturan untuk:

```php
return [
    'code'        => ['required', 'string', 'max:20'],
    'name'        => ['required', 'string', 'max:150'],
    'description' => ['nullable', 'string'],
    'sks'         => ['required', 'integer', 'between:1,6'],
    'lecturer_id' => ['required', 'exists:users,id'],
    'status'      => ['required', 'in:draft,active,archived'],
];
```

Dengan:

```php
$request->validated()
```

field liar seperti `is_admin` tidak menjadi bagian dari data tervalidasi tersebut.

### File yang dicek

Periksa controller mata kuliah, umumnya:

```text
app/Http/Controllers/CourseController.php
```

Cari method:

```php
public function store(StoreCourseRequest $request)
```

Pastikan penyimpanan menggunakan:

```php
Course::create($request->validated());
```

Bukan:

```php
Course::create($request->all());
```

Periksa juga Form Request:

```text
app/Http/Requests/StoreCourseRequest.php
```

### Cara mengecek

Untuk melihat perbedaannya saat pengembangan, data request dapat diperiksa sementara menggunakan:

```php
dd($request->all());
```

Kemudian bandingkan dengan:

```php
dd($request->validated());
```

Kirim field tambahan yang tidak terdapat pada rules, misalnya:

```text
is_admin=1
```

Pada `all()`, field tersebut akan terlihat sebagai bagian dari input request. Pada `validated()`, field yang tidak termasuk data tervalidasi tidak ikut dikembalikan.

Setelah pengujian selesai, hapus `dd()` agar aplikasi dapat berjalan normal.

### Hasil yang diharapkan

Gunakan:

```php
$course = Course::create($request->validated());
```

Dengan demikian, data yang diteruskan ke proses penyimpanan dibatasi pada data yang telah melalui aturan Form Request.

---

## 3. Jelaskan pola PRG. Apa yang terjadi kalau `store` mengembalikan view?

### Jawaban

PRG merupakan singkatan dari **Post → Redirect → Get**.

Alurnya adalah:

```text
Form dikirim
    ↓
POST
    ↓
Data diproses/disimpan
    ↓
Redirect
    ↓
GET halaman tujuan
```

Setelah data berhasil disimpan melalui request POST, controller sebaiknya melakukan redirect, misalnya:

```php
public function store(StoreCourseRequest $request)
{
    $course = Course::create($request->validated());

    return redirect()
        ->route('courses.show', $course)
        ->with('success', 'Mata kuliah berhasil ditambahkan.');
}
```

Jika setelah POST controller langsung menggunakan `return view()`, browser masih berada pada hasil request POST. Ketika pengguna menekan refresh/F5, browser dapat mencoba mengirim ulang request tersebut. Hal ini dapat menyebabkan operasi penyimpanan dijalankan kembali dan berpotensi menghasilkan data duplikat.

### File yang dicek

Buka:

```text
app/Http/Controllers/CourseController.php
```

Cari method:

```php
public function store(...)
```

Pastikan setelah penyimpanan menggunakan:

```php
return redirect()
```

bukan langsung:

```php
return view(...)
```

### Cara mengecek

1. Buka `CourseController.php`.
2. Pastikan `store()` menggunakan `redirect()` setelah berhasil menyimpan data.
3. Untuk percobaan di lingkungan development, ubah sementara respons setelah penyimpanan menjadi `return view(...)` dengan view yang sesuai.
4. Tambahkan satu mata kuliah.
5. Setelah halaman hasil muncul, tekan `F5` atau refresh.
6. Amati apakah browser meminta konfirmasi pengiriman ulang form, misalnya **Confirm Form Resubmission**.
7. Periksa database untuk melihat apakah operasi berpotensi dijalankan kembali.
8. Setelah pengujian, kembalikan ke `return redirect()`.

### Hasil yang diharapkan

Implementasi yang benar menggunakan pola:

```text
POST → Redirect → GET
```

Setelah redirect, refresh terjadi terhadap request GET, bukan mengirim ulang POST sebelumnya.

---

## 4. Kenapa filter pencarian sebaiknya di query string, bukan session? Beri satu skenario yang rusak kalau dipindah ke session.

### Jawaban

Filter pencarian sebaiknya disimpan pada **query string** karena filter merupakan state dari halaman yang sedang ditampilkan.

Contohnya:

```text
/courses?q=basis&status=active
```

Dengan query string, kondisi pencarian terlihat pada URL sehingga dapat dibagikan, disimpan sebagai bookmark, dan setiap tab dapat mempunyai filter yang berbeda.

Jika filter disimpan di session, seluruh tab pada browser yang menggunakan session yang sama dapat berbagi state filter tersebut.

Contoh masalah:

```text
Tab 1 → status=active
Tab 2 → status=draft
```

Jika filter disimpan di session, perubahan filter pada Tab 2 dapat mengubah state session menjadi `draft`. Ketika Tab 1 melakukan refresh, Tab 1 dapat ikut menggunakan filter `draft`. Akibatnya, dua tab yang seharusnya independen saling memengaruhi.

### File yang dicek

Buka:

```text
app/Http/Controllers/CourseController.php
```

Periksa method:

```php
public function index(Request $request)
```

Implementasinya dapat berbentuk:

```php
$courses = Course::query()
    ->with('lecturer')
    ->when($request->filled('q'), fn ($query) =>
        $query->where('name', 'like', '%' . $request->q . '%')
              ->orWhere('code', 'like', '%' . $request->q . '%'))
    ->when($request->filled('status'), fn ($query) =>
        $query->where('status', $request->status))
    ->latest()
    ->paginate(15)
    ->withQueryString();
```

Periksa juga form pencarian, misalnya:

```text
resources/views/courses/index.blade.php
```

Pastikan pencarian menggunakan method GET, misalnya:

```html
<form method="GET" action="{{ route('courses.index') }}">
```

### Cara mengecek

1. Buka halaman daftar mata kuliah.
2. Cari kata tertentu, misalnya `basis`.
3. Pilih filter status, misalnya `active`.
4. Perhatikan URL browser.

Seharusnya URL berbentuk kurang lebih:

```text
/courses?q=basis&status=active
```

5. Jika hasil memiliki lebih dari satu halaman, klik halaman 2.
6. Pastikan parameter pencarian dan status tetap ada pada URL.

Contoh:

```text
/courses?q=basis&status=active&page=2
```

Hal tersebut membutuhkan:

```php
->withQueryString();
```

### Pengecekan skenario dua tab

1. Buka halaman courses pada dua tab.
2. Pada Tab 1, gunakan filter `active`.
3. Pada Tab 2, gunakan filter `draft`.
4. Kedua tab seharusnya memiliki URL masing-masing.

Contoh:

```text
Tab 1: /courses?status=active
Tab 2: /courses?status=draft
```

Dengan query string, kedua state tersebut dapat tetap independen.

---

## 5. Apa fungsi `@csrf`? Serangan apa yang dicegahnya, dan bagaimana serangan itu bekerja?

### Jawaban

`@csrf` digunakan untuk memasukkan **CSRF token** ke dalam form Laravel. Token tersebut digunakan Laravel untuk memverifikasi request perubahan data yang berasal dari form dengan token yang sesuai dengan session pengguna.

Contoh:

```html
<form method="POST" action="{{ route('courses.store') }}">
    @csrf

    ...
</form>
```

`@csrf` menghasilkan input token tersembunyi pada form.

Proteksi ini mencegah **Cross-Site Request Forgery (CSRF)**.

Skenario serangannya secara konsep:

1. Pengguna sudah login ke KampusLMS.
2. Browser pengguna masih mempunyai session autentikasi.
3. Pengguna membuka situs lain yang berbahaya.
4. Situs tersebut mencoba membuat browser korban mengirim request perubahan data ke KampusLMS.
5. Tanpa perlindungan CSRF, request tersebut berpotensi diproses menggunakan session korban.
6. Dengan CSRF token, request tanpa token yang cocok ditolak oleh Laravel.

### File yang dicek

Periksa seluruh form yang melakukan perubahan data, terutama:

```text
resources/views/courses/create.blade.php
resources/views/courses/edit.blade.php
resources/views/courses/index.blade.php
```

Pada form POST harus terdapat:

```blade
@csrf
```

Contoh form update:

```blade
<form method="POST" action="{{ route('courses.update', $course) }}">
    @csrf
    @method('PUT')

    ...
</form>
```

Contoh form delete:

```blade
<form method="POST" action="{{ route('courses.destroy', $course) }}">
    @csrf
    @method('DELETE')

    ...
</form>
```

### Cara mengecek

1. Buka salah satu file form, misalnya:

```text
resources/views/courses/create.blade.php
```

2. Pastikan terdapat `@csrf`.
3. Jalankan aplikasi dan pastikan form normal dapat dikirim.
4. Untuk pengujian di development, hapus `@csrf` sementara.
5. Coba kirim form POST kembali.
6. Laravel seharusnya menolak request karena token CSRF tidak tersedia/tidak cocok, yang umumnya terlihat sebagai status **419**.
7. Setelah pengujian, pasang kembali `@csrf`.

### Hasil yang diharapkan

Form yang mengubah data harus memiliki proteksi CSRF. Secara sederhana:

```text
Validasi → apakah DATA yang dikirim valid?
CSRF      → apakah REQUEST memiliki token yang sesuai dengan session?
```

---

## 6. Kenapa aturan `unique` pada update perlu `ignore()`?

### Jawaban

Pada operasi update, data yang sedang diedit sudah tersimpan di database. Jika aturan `unique` digunakan tanpa mengecualikan record tersebut, nilai milik record itu sendiri dapat dianggap sebagai duplikat.

Misalnya database memiliki:

```text
id   = 10
code = IF101
name = Pemrograman Web
```

Kemudian pengguna hanya mengubah nama menjadi:

```text
Pemrograman Web Lanjut
```

Kode tetap:

```text
IF101
```

Jika validasi update hanya menggunakan:

```php
'code' => ['required', 'unique:courses,code'],
```

Laravel akan mencari `IF101` pada tabel `courses` dan menemukannya pada record yang sedang diedit. Akibatnya, update dapat ditolak walaupun pengguna tidak membuat kode duplikat baru.

Karena itu, record yang sedang diedit perlu dikecualikan dari pemeriksaan unique menggunakan `ignore()`.

Contoh:

```php
use Illuminate\Validation\Rule;

'code' => [
    'required',
    'string',
    'max:20',
    Rule::unique('courses', 'code')->ignore($this->route('course')),
],
```

Maknanya adalah: kode harus unik terhadap record lain, tetapi record mata kuliah yang sedang diperbarui tidak dihitung sebagai konflik dengan dirinya sendiri.

### File yang dicek

Buka:

```text
app/Http/Requests/UpdateCourseRequest.php
```

Pastikan terdapat import:

```php
use Illuminate\Validation\Rule;
```

Kemudian periksa rules untuk `code` dan pastikan menggunakan:

```php
Rule::unique('courses', 'code')->ignore(...)
```

Sesuaikan argumen `ignore()` dengan route model binding atau ID yang digunakan oleh project.

### Cara mengecek kasus pertama: kode tidak diubah

Misalnya terdapat:

```text
Course A
id   = 10
code = IF101
```

Lakukan:

1. Buka halaman edit Course A.
2. Jangan ubah `code`.
3. Ubah hanya `name`.
4. Klik simpan.

### Hasil yang benar

Update harus **berhasil**, karena `IF101` merupakan kode milik Course A sendiri.

### Cara mengecek kasus kedua: kode milik record lain

Misalnya terdapat:

```text
Course A → IF101
Course B → IF102
```

Lakukan:

1. Edit Course B.
2. Ubah `code` dari `IF102` menjadi `IF101`.
3. Klik simpan.

### Hasil yang benar

Update harus **ditolak**, karena `IF101` sudah digunakan oleh Course A.

Dengan demikian, `ignore()` bukan berarti aturan unique dimatikan. `ignore()` hanya mengecualikan record yang sedang diedit dari pemeriksaan terhadap dirinya sendiri.

---

# Ringkasan File yang Perlu Diperiksa

| Yang diperiksa | File utama |
|---|---|
| Validasi server saat tambah course | `app/Http/Requests/StoreCourseRequest.php` |
| Validasi server saat update course | `app/Http/Requests/UpdateCourseRequest.php` |
| `$request->validated()` | `app/Http/Controllers/CourseController.php` |
| Pola PRG pada `store()` / `update()` | `app/Http/Controllers/CourseController.php` |
| Query string, filter, pagination | `app/Http/Controllers/CourseController.php` |
| Form pencarian GET | `resources/views/courses/index.blade.php` |
| `old()`, `@error`, `@csrf` form tambah | `resources/views/courses/create.blade.php` |
| `old()`, `@error`, `@csrf` form edit | `resources/views/courses/edit.blade.php` |
| CSRF pada delete | File Blade yang memuat form delete |
| `unique()->ignore()` | `app/Http/Requests/UpdateCourseRequest.php` |

> **Catatan:** nama/path file Blade dapat berbeda jika struktur project KampusLMS yang digunakan berbeda. Yang perlu dicari adalah file yang mendefinisikan form create/edit/index dan controller serta Form Request untuk modul Course.

---

# Checklist Pengujian Akhir

Sebelum checkpoint, pastikan hasil berikut dapat diperagakan:

- [ ] Mengirim `sks=99` ditolak oleh validasi Laravel walaupun pembatasan frontend dilewati.
- [ ] `StoreCourseRequest` dan `UpdateCourseRequest` memiliki rules yang sesuai.
- [ ] Controller menyimpan data menggunakan `$request->validated()`, bukan `$request->all()`.
- [ ] Setelah `store`, aplikasi melakukan `redirect()` sehingga mengikuti pola PRG.
- [ ] Pencarian/filter muncul pada query string URL.
- [ ] Filter tetap ada ketika berpindah halaman karena menggunakan `withQueryString()`.
- [ ] Dua tab dapat memiliki query/filter yang berbeda tanpa saling mengubah state filter.
- [ ] Form POST/PUT/DELETE memiliki `@csrf`.
- [ ] Form tanpa CSRF ditolak Laravel (umumnya 419).
- [ ] Update course tanpa mengubah kode tetap berhasil.
- [ ] Mengubah kode menjadi kode milik course lain ditolak oleh validasi `unique`.

