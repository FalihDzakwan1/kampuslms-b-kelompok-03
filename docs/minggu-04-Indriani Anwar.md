### Nama : Indriani Anwar
#### NIM : 10241036
---
READ — Telusuri satu siklus form gagal (30 menit)

1. Method apa yang menerima request? Di controller mana?

Pada saat pengguna mengisi form tambah mata kuliah kemudian menekan tombol submit, request dikirim menggunakan metode POST menuju route penyimpanan data. Request tersebut akan diterima oleh method store() pada controller yang menangani data mata kuliah maupun user, yaitu CourseController dan UserController.

Contoh :
```php
public function store(StoreCourseRequest $request)
{
    $course = Course::create($request->validated());

    return redirect()
        ->route('courses.show', $course)
        ->with('success', 'Mata kuliah berhasil ditambahkan.');
}
```
Jadi method penerima request adalah store () pada ```app/Http/Controllers/CourseController.php``` dan ```app/Http/Controllers/UserController.php``` 

2. Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?

Validasi terjadi sebelum isi method controller dijalankan, karena Laravel menggunakan Form Request. Validasi dilakukan menggunakan kelas khusus seperti ```StoreCourseRequest``` yang dipanggil pada parameter method ```public function store(StoreCourseRequest $request)```

Sebelum Laravel menjalankan isi method store(), Laravel terlebih dahulu menjalankan proses validasi pada StoreCourseRequest. Jadi validasi terjadi sebelum baris pertama kode di dalam method controller dieksekusi. Modul menjelaskan bahwa jika validasi gagal, Laravel otomatis melakukan redirect kembali dengan error dan input lama tanpa perlu ditulis manual.

3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?

Jika validasi gagal, Laravel otomatis melakukan redirect kembali ke halaman form sebelumnya.

Tujuan redirect tersebut ditentukan oleh mekanisme validasi Laravel (ValidationException) yang akan mengembalikan user ke URL sebelumnya. Laravel juga membawa dua data penting saat redirect:
- Pesan error validasi.
- Input lama dari form.

Data tersebut disimpan sementara menggunakan flash session agar dapat digunakan pada halaman form berikutnya.

4. Dari mana @error('sks') mengambil pesannya?

@error('sks') mengambil pesan dari error validasi yang dibuat Laravel ketika proses validasi gagal.
```php
@error('sks')
    <p>{{ $message }}</p>
@enderror
```
Pesan tersebut berasal dari aturan validasi pada Form Request:
```php
'sks' => ['required', 'integer', 'between:1,6']
```
Jika pengguna memasukkan:
``` sks = 99 ```

maka aturan: ```between:1,6```
gagal dan Laravel membuat pesan error. "Pesan tersebut disimpan sementara dalam session (flash session) dan kemudian dibaca oleh Blade menggunakan ```@error()```

Dan juga, Laravel otomatis membawa error validasi ketika validasi gagal.

5. Dari mana old('sks') mengambil nilainya? Berapa lama nilai itu bertahan?

old('sks') mengambil nilai dari input sebelumnya yang dikirim oleh pengguna ketika validasi gagal. Laravel menyimpan input tersebut sementara ke dalam flash session agar dapat digunakan

Contoh:
Pengguna mengisi ```sks = 99```

Kemudian validasi gagal karena SKS hanya boleh 1 sampai 6.

Laravel menyimpan nilai tersebut ke flash session. Pada halaman form:
```php
<input 
    name="sks" 
    value="{{ old('sks') }}"
>
```

Maka laravel akan mengisi kembali nilai 99 dan nilai tersebut bertahan selama satu request berikutnya.

6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.

Pada browser, cookie session Laravel dapat ditemukan melalui DevTools
- Application 
- Cookies 
- Pilih domain aplikasi

Nama cookie session default Laravel adalah ```laravel_session```

Cookie tersebut berfungsi sebagai identitas session pengguna. Data seperti error validasi dan old input tidak langsung disimpan di cookie, tetapi disimpan pada tempat penyimpanan session Laravel sesuai konfigurasi SESSION_DRIVER.

Contohnya : 
Jika menggunakan ```SESSION_DRIVER=file```

maka session disimpan di ```storage/framework/sessions```

Cookie hanya menyimpan ID session yang digunakan Laravel untuk mengambil data session tersebut.

---
BREAK — Tujuh kerusakan (45 menit)
# BREAK — Tujuh Kerusakan

| No | Yang Dirusak | Prediksi Sebelum Dijalankan | Hasil yang Diamati | Kesimpulan |
|----|--------------|-----------------------------|--------------------|------------|
| 1 | Menghapus `@csrf` dari form, lalu mengirim form | Laravel akan menolak request POST karena token CSRF tidak ditemukan atau tidak valid. | Muncul error **419 Page Expired** saat form dikirim. | `@csrf` digunakan untuk melindungi aplikasi dari serangan CSRF dengan memastikan request berasal dari aplikasi yang valid. |
| 2 | Mengganti `$request->validated()` menjadi `$request->all()`, lalu mengirim field liar melalui `curl` | Semua data yang dikirim pengguna akan diteruskan tanpa penyaringan, termasuk field yang tidak seharusnya diproses. | Field tambahan yang dikirim melalui request dapat ikut masuk ke proses penyimpanan. | `$request->validated()` lebih aman karena hanya mengambil data yang sudah lolos validasi. Menggunakan `$request->all()` dapat membuka risiko mass assignment. |
| 3 | Menghapus validasi `exists:users,id` pada `lecturer_id` dan mengirim `lecturer_id=99999` | Data dengan foreign key yang tidak ada di database tetap dapat masuk. | Mata kuliah tersimpan dengan `lecturer_id` yang tidak memiliki user terkait. | Validasi `exists` memastikan relasi foreign key tetap valid dan mencegah data yatim. |
| 4 | Menghapus validasi `in:...` pada `status`, lalu mengirim `status=superadmin` | Nilai status apa pun dapat diterima karena tidak ada pembatasan. | Data dengan status `superadmin` berhasil masuk meskipun bukan status yang diperbolehkan. | Validasi `in` menjaga agar nilai enum tetap sesuai aturan aplikasi. |
| 5 | Menghapus `->withQueryString()`, melakukan pencarian lalu pindah halaman 2 | Parameter pencarian atau filter tidak akan ikut terbawa pada pagination berikutnya. | Setelah pindah halaman, filter pencarian hilang dan daftar kembali tanpa filter. | `withQueryString()` digunakan untuk mempertahankan state filter dan pencarian pada pagination. |
| 6 | Mengganti `return redirect()` menjadi `return view()` pada `store`, lalu menekan F5 setelah simpan | Browser akan mengirim ulang request POST sebelumnya sehingga data dapat tersimpan ulang. | Muncul peringatan **Confirm Form Resubmission** atau data dapat terduplikasi. | Pola PRG (**Post → Redirect → Get**) mencegah pengiriman ulang data ketika halaman di-refresh. |
| 7 | Menghapus `old(...)` dari input form, lalu mengirim form dengan satu kesalahan | Setelah validasi gagal, input yang sudah diisi pengguna akan hilang. | Pengguna harus mengisi ulang seluruh form meskipun hanya satu field yang salah. | `old()` mengambil input sebelumnya dari flash session agar pengguna tidak perlu mengisi ulang seluruh form. |

Tahapan Break : 

1. Hapus @csrf dari form, lalu kirim
csrf berfungsi untuk melindungi aplikasi dari pengiriman request palsu yang memanfaatkan session pengguna yang sedang login. 

contoh : 
open ```courses/create.blade.php``` 

Hasil : <img src="image/hapuscsrf.jpeg" width="500"> 

Hal tersebut dikarenakan laravel tidak menemukan atau tidak dapat memvalidasi CSRF token pada request yang masuk dikarenakan csrf pada create mata kuliah telah dihapus. 

2. Ganti $request->validated() menjadi $request->all(), lalu kirim field liar lewat curl

pada ```CourseController``` atau ```UserController``` terdapat ```$request->validated()``` pada store function yang digunakan untuk mengecekan dan memvalidasi setiap data yang masuk.

Pengujian ini bertujuan untuk melihat hasil jika ```$request->all()``` dijalankan, namun sebelum itu ketika ingin mencoba mengganti status jadi superadmin, maka mengubah ```'status'      => ['required', 'in:draft,active,archived']``` menjadi ```'status'      => ['required']``` Pada ```StoreCourseRequest```

Hasil : <img src="image/superadmin.jpeg" width="500"> 

Hasil tersebut dikarenakan request dapat diterima keseluruhan tanpa di validasi terlebih dahulu.

3. Hapus validasi exists:users,id pada lecturer_id, kirim lecturer_id=99999
Pada ```StoreCourseRequest``` dapat diubah ```'lecturer_id' => ['required', 'exists:users,id']``` menjadi ```'lecturer_id' => ['required']``` membuat Laravel hanya mengecek apakah field lecturer_id terisi, tanpa memastikan apakah ID tersebut benar-benar ada pada tabel users, dan menghapus

```php
->constrained('users')
->restrictOnDelete();
```

Harus dihapus karena bagian tersebut membuat foreign key constraint pada database yang tetap mengecek apakah lecturer_id memiliki data pada tabel users. Jadi meskipun validasi exists:users,id di Laravel sudah dihapus, database tetap akan menolak lecturer_id=99999 jika user tersebut tidak ada.

Sedangkan ->restrictOnDelete() hanya mengatur penghapusan data user yang masih digunakan oleh course, sehingga tidak berpengaruh pada proses memasukkan lecturer_id baru.

Hasil : <img src="image/lecture999.jpeg" width="500"> 

Hal tersebut dikarenakan telah menghapus ketentuan validasi `exists` pada `lecturer_id` dan menghapus foreign key pada database, sehingga data dengan `lecturer_id` yang tidak memiliki relasi pada tabel `users` tetap dapat disimpan.

4. Hapus validasi in:... pada status, kirim status=superadmin

 Mengubah ```'status'      => ['required', 'in:draft,active,archived']``` menjadi ```'status'      => ['required']``` Pada ```StoreCourseRequest``` dan menghapus enum 
 ```php
 $table->enum('status', [
                'draft',
                'active',
                'archived'
            ])->default('draft');
```
menjadi ```$table->string('status')->default('draft');``` 

Hasil : <img src="image/superadmin2.jpeg" width="500"> 

Hal tersebut membuat Laravel hanya memastikan bahwa field status memiliki nilai, tanpa membatasi isi nilainya. Namun, jika database masih menggunakan tipe enum, nilai status=superadmin tetap akan ditolak oleh database.

5. Hapus ->withQueryString(), lakukan pencarian lalu klik halaman 2

Menghapus ```->withQueryString()``` pada ```UserController``` maupun ```CourseController``` dapat menyebabkan filter pencarian tidak bertahan saat berpindah halaman, sehingga menghasilkan bug pada pagination. dikarenakan  Fungsi withQueryString() digunakan untuk mempertahankan parameter pencarian atau filter pada URL ketika pengguna berpindah halaman pagination. Jika bagian tersebut dihapus, parameter query seperti q atau role tidak akan ikut terbawa saat pengguna membuka halaman berikutnya.

6. Ganti return redirect() menjadi return view() pada store, lalu tekan F5 setelah simpan

Mengganti pada ```CourseController```
```php
return redirect()->route('courses.index')
    ->with('success', 'Mata kuliah berhasil ditambahkan.');
```

Menjadi 
```php
return view()->route('courses.index')
    ->with('success', 'Mata kuliah berhasil ditambahkan.');
```

Menyebabkan browser masih berada pada halaman hasil POST sehingga refresh dapat menyebabkan pengiriman ulang data dan membuat duplikasi. Hal ini terjadi karena redirect() menerapkan pola Post/Redirect/Get (PRG). Setelah data disimpan, browser diarahkan ke halaman GET sehingga refresh tidak mengirim ulang POST.

7. Hapus old(...) dari semua input, lalu kirim form dengan satu kesalahan

Menghapus pada file ```courses/create.blade.php``` atau form input lainnya:

```value="{{ old('name') }}"```

Menjadi:

```value=""```

Hal tersebut menyebabkan nilai input yang sudah diisi pengguna akan hilang ketika validasi gagal dan Laravel melakukan redirect kembali ke halaman form. Tanpa old(), halaman form tidak dapat mengambil kembali data sebelumnya dari session flash, sehingga pengguna harus mengisi ulang seluruh input.

Hal ini berbeda ketika menggunakan old(). Laravel menyimpan input sebelumnya sementara di session ketika validasi gagal, kemudian fungsi old() mengambil kembali nilai tersebut untuk ditampilkan pada form. Dengan demikian, pengguna hanya perlu memperbaiki data yang salah tanpa mengisi ulang seluruh form.

