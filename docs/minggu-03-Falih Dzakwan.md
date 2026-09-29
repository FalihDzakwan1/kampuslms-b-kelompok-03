#### Nama : Falih Dzakwan
#### NIM  : 10241028
#### WEEK 3


READ
1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.
![alt text](image/ERD.jpeg)

2. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.
- courses.lecturer_id menggunakan restrictOnDelete()	
- materials.course_id menggunakan cascadeOnDelete()	
- assignments.course_id menggunakan cascadeOnDelete()	
- course_user.course_id menggunnakan cascadeOnDelete()	
- course_user.user_id menggunakan restrictOnDelete()	
- materials.uploaded_by menggunakan restrictOnDelete()	
- assignments.created_by menggunakan restrictOnDelete()	
- submissions.assignment_id menggunakan cascadeOnDelete()	
- submissions.user_id menggunakan restrictOnDelete()	
- grades.submission_id menggunakan cascadeOnDelete() 
- grades.graded_by menggunakan restrictOnDelete()	

3. Jawab: kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?
Dengan menggunakan restrictOnDelete dosen tidak akan bisa dihapus karena masih terikat dengan mata kuliahnya, namun jika dipaksa menghapus dosen, maka mata kuliahnya tetap ada dan tidak ikut terhapus, tapi data dosen didalamnya akan menjadi null

4. Jawab: kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?
bersifat unique karena menjamin relasi ini satu submission cuma boleh punya satu nilai.

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