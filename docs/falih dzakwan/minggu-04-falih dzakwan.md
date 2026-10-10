# Minggu 4 - Falih Dzakwan

## READ

**1. Method apa yang menerima request? Di controller mana?**

Request pengiriman form tambah mata kuliah diterima oleh method `store` yang berada di dalam `CourseController` (`app/Http/Controllers/CourseController.php`).

**2. Di titik mana persisnya validasi terjadi, sebelum atau sesudah baris pertama method controller?**

Validasi terjadi sebelum baris pertama kode di method controller dieksekusi. Laravel memvalidasi request terlebih dahulu, jika validasi gagal, proses akan langsung berhenti dan kode di dalam fungsi `store` tidak akan pernah disentuh.

**3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?**

Laravel secara otomatis me-redirect pengguna ke halaman sebelumnya yaitu kembali ke halaman form tambah mata kuliah. Yang menentukan tujuannya adalah framework Laravel itu sendiri lewat penanganan error (exception handler). Ketika form request gagal, Laravel melempar `ValidationException` yang otomatis membaca HTTP Referer dari request dan memanggil instruksi setara `redirect()->back()`.

**4. Dari mana `@error('sks')` mengambil pesannya?**

Directive `@error('sks')` di Blade mengambil pesan kesalahannya dari Session. Saat validasi gagal, Laravel otomatis menyimpan sementara pesan-pesan error ke dalam session di bawah kunci bernama `errors` yang merupakan objek `MessageBag`.

**5. Dari mana `old('sks')` mengambil nilainya? Berapa lama nilai itu bertahan?**

Sama seperti pesan error, fungsi `old('sks')` juga mengambil nilainya dari Session lebih tepatnya flashed input data. Saat validasi gagal, Laravel menyimpan seluruh inputan user ke session sebelum melakukan redirect, nilai ini hanya bertahan selama satu siklus request HTTP selanjutnya. Artinya, setelah halaman form ditampilkan kembali dan merender nilai tersebut, datanya akan langsung dihapus. Jika halaman di-refresh manual, nilai itu akan hilang.

**6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.**

Nama cookie session yang digunakan Laravel untuk menyimpan koneksi sesi ini adalah `laravel_session`. Cookie inilah yang menjadi kunci identifikasi untuk membaca data error dan data input lama (`old`) dari server.

## BREAK

| # | Yang Dirusak | Observasi | Hasil | Kesimpulan |
|---|---|---|---|---|
| 1 | **Hapus @csrf dari form HTML**, lalu tekan tombol kirim (submit). | Tampil halaman error bertuliskan **419 Page Expired**. | Request POST ditolak mentah-mentah oleh server. | @csrf wajib ada di setiap form POST. Ini pertahanan Laravel untuk mencegah serangan Cross-Site Request Forgery (CSRF), memastikan form dikirim dari web kita sendiri, bukan web peretas. |
| 2 | Ganti $request->validated() menjadi **$request->all()** di controller, lalu tembak atribut liar via *curl*. | Field liar yang tidak diminta misalnya role=admin atau atribut tersembunyi lainnya akan ikut lolos masuk ke fungsi create(). | Terjadi potensi jebolnya keamanan Mass Assignment. | Jangan memakai $request->all() untuk input ke database. Selalu gunakan $request->validated() agar hanya field yang sudah divalidasi dan diizinkan saja yang masuk. |
| 3 | Hapus validasi **exists:users,id** pada field lecturer_id, lalu kirim lecturer_id=99999 via curl. | Laravel meloloskan request tersebut dan menyimpannya ke database padahal user dengan ID 99999 tidak ada. | Tercipta Data Yatim (Orphan Data) di database. | Validasi exists sangat krusial untuk integritas relasi. Tanpanya, aplikasi akan error saat mencoba membaca properti null ketika menampilkan halaman yang memuat nama dosen dari relasi tersebut. |
| 4 | Hapus validasi **in:draft,published** pada status, lalu kirim status=superadmin via curl. | Request lolos. Kata "superadmin" dipaksa masuk ke dalam kolom status. | Terjadi Enum Jebol. Jika database tidak dijaga ketat, data kotor akan merusak filter aplikasi. | Validasi in: penting untuk melindungi integritas nilai dari sebuah kolom yang memiliki batasan pasti seperti status, role, atau tipe. |
| 5 | Hapus **->withQueryString()** pada kode *pagination*, cari mata kuliah tertentu, lalu klik halaman 2. | URL yang awalnya ?search=Pemrograman&page=1 berubah menjadi ?page=2 saja. Parameter pencarian hilang. | Hasil filter **mereset ke awal** dan menampilkan semua data lagi (Bug klasik). | Fungsi withQueryString() di pagination untuk mempertahankan kata kunci pencarian saat berpindah halaman. |
| 6 | Ganti return redirect() menjadi **return view(...)** di method store, simpan data, lalu tekan F5 (Refresh). | Halaman berhasil tampil tanpa redirect. Namun, saat ditekan F5, browser memunculkan alert "Confirm Form Resubmission". | Jika ditekan OK, **Data Ganda** akan masuk ke database. | Gunakan pola **PRG (Post/Redirect/Get)** setelah berhasil menyimpan form. Redirect() memutus riwayat POST sehingga mencegah pengiriman ganda akibat refresh halaman. |
| 7 | Hapus helper **old(...)** pada *value* di input form, lalu isi form dan sengajakan satu error (SKS salah). | Saat halaman kembali beserta pesan error, semua isian panjang yang sudah diketik pengguna menjadi kosong kembali. | Pengalaman pengguna menjadi sangat buruk dan **membuat frustrasi**. | Fungsi old() untuk mengambil data dari flashed session agar pengguna tidak perlu repot mengetik ulang semuanya dari nol saat ada satu kesalahan. |
| 8 | Hapus penulisan pembungkus **Closure (fungsi pembungkus logika WHERE dan OR WHERE)** pada query pencarian. | Logika OR akan menabrak pembatasan AND di luar pencarian. Misalnya mencari nama MK dan secara bersamaan mem-filter status=archived. | **Logika bocor**. Mata kuliah yang aktif malah ikut tampil jika namanya cocok, menabrak filter status. | Dalam raw query atau Eloquent yang menggabungkan klausa pencarian AND dan OR, sangat penting untuk menggunakan *Logical Grouping* (where(function() {...})) agar batasannya tidak tertembus. |

