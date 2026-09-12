#### Nama : Falih Dzakwan
#### NIM  : 10241028
#### WEEK 3


BREAK

1. Hapus `unique(['course_id','user_id'])` dari `course_user`, lalu daftarkan mahasiswa yang sama dua kali

    Constraint `unique` untuk mencegah kombinasi `course_id` + `user_id` yang sama muncul dua kali di tabel `course_user`. Tanpa constraint ini, kalau menginput form pendaftaran mahasiswa yang sama ke course yang sama dua kali, sistem akan diam saja dan menerima duplikasi.

2. Tambahkan role ke $fillable model User, lalu kirim request pembuatan user dengan role=admin lewat form yang tidak punya field role

    $fillable menunjukkan bahwa field tersebut boleh diisi langsung lewat mass assignment `User::create($request->all())`. Meskipun form aslinya tidak menyediakan field role, attacker tetap bisa menambahkan field itu secara manual di request. User baru yang dibuat akan punya role = admin, padahal seharusnya default user biasa.

3. Ganti seluruh $fillable dengan protected $guarded = []; lalu ulangi nomor 2

    $guarded menunjukkan bahwa field tersebut tidak boleh diisi langsung lewat mass assignment. Jadi jika $guarded = [] artinya tidak ada satupun kolom yang dilindungi, semua kolom yang ada di tabel menjadi bisa diisi mass assignment. Ini kebalikan dari $fillable. Sekarang bukan cuma role yang bisa dikirim attacker.

4. Kosongkan isi down() di satu migrasi, lalu jalankan php artisan migrate:refresh

    Migration yang `down()`nya kosong tidak akan melakukan apa-apa, perubahan yang dibuat `up()` akan tetap ada di database, padahal seharusnya sudah dihapus di tahap rollback.  Efeknya saat migration dijalankan ulang, Laravel mencoba membuat sesuatu yang ternyata masih ada  dan mengakibatkan error.

5. Ubah restrictOnDelete pada lecturer_id menjadi cascadeOnDelete, lalu hapus satu dosen

    Setelah ganti ke `cascadeOnDelete` dan hapus satu dosen, maka semua course yang diampu dosen itu ikut hilang, padahal cuma mau menghapus data dosen, bukan course-nya. Efeknya bisa merambat lagi kalau course itu sendiri jadi parent dari data lain, dan terjadi reaksi berantai yang tidak diinginkan. Ini bisa terjadi karena `cascadeOnDelete` begitu menghapus parent dari data lain, semua data terkait ikut terhapus otomatis secara berantai, tanpa peringatan tambahan tidak seperti `restrictOnDelete`.