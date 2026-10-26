# Catatan Individu Minggu - 09 - Elsya Nur Aulia Handayani (10241026)

## 9.3 Read → Break → Fix → Build

### READ — Telusuri satu berkas (30 menit)

#### 1. Temukan berkas fisiknya di sistem berkas. Path lengkapnya apa?

Saat mengunggah satu berkas materi lewat akun dosen, berkas tersebut disimpan di direktori privat server, bukan di folder publik. Di sistem operasi Windows (menggunakan Laragon), saya mencari berkas fisiknya di dalam folder `storage/app/private/materials/`. Path lengkapnya mengarah ke `C:\laragon\www\Proweb\kampuslms-b-kelompok-03\storage\app\private\materials\`.

Ketika saya memeriksa nama filenya di Windows Explorer, nama asli berkas yang diunggah ternyata sudah tidak dipakai lagi. Laravel otomatis menggantinya menjadi nama acak berupa hash unik (misalnya `xyz123abc456.pdf`). Hal ini dilakukan oleh fungsi `$file->store('materials', 'local')` di `MaterialController` agar nama berkas yang masuk tidak saling bertabrakan atau menimpa satu sama lain jika ada dosen lain yang mengunggah berkas dengan nama yang kebetulan sama.

---

#### 2. Buka baris database yang bersangkutan. Bandingkan file_path dengan lokasi fisiknya.

Setelah berkas terunggah, saya membuka tabel `materials` di database untuk melihat data yang baru tersimpan. Pada kolom `file_path`, nilai yang tercatat hanya berupa path relatif dari disk privat, yaitu `materials/xyz123abc456.pdf`. Nilai ini berbeda dengan path fisik di harddisk yang memuat alamat lengkap mulai dari huruf drive `C:\...` sampai folder privatnya.

Perbedaan ini penting agar aplikasi tetap portabel. Database sengaja tidak menyimpan path absolut komputer lokal karena struktur folder di laptop pengembangan belum tentu sama dengan server produksi. Dengan hanya menyimpan path relatif, aplikasi bisa dipindahkan ke server Linux atau layanan penyimpanan cloud seperti AWS S3 tanpa perlu mengubah satu pun baris data di database, karena Laravel cukup menggabungkan path relatif tersebut dengan konfigurasi root disk yang aktif.

---

#### 3. Coba akses berkas itu langsung lewat browser dengan menebak URL-nya. Berhasil atau tidak? Kenapa?

Saya mencoba membuka berkas tersebut secara langsung melalui browser dengan mengetikkan URL seperti `http://127.0.0.1:8000/storage/app/private/materials/xyz123abc456.pdf` atau `http://127.0.0.1:8000/materials/xyz123abc456.pdf`. Hasilnya, halaman menampilkan error 404 Not Found dan berkas sama sekali tidak bisa diakses atau diunduh.

Hal ini terjadi karena web server hanya membuka pintu akses publik ke folder `public/`. Folder `storage/app/private` berada di luar folder public tersebut, sehingga web server secara otomatis memblokir permintaan langsung dari peramban. Berkas materi yang ada di folder privat hanya bisa diakses lewat jalur controller (`materials/{material}/download` atau `/preview`) yang mewajibkan otorisasi pengguna melalui Policy sebelum mengalirkan isi berkasnya ke browser.

---

#### 4. Buka config/filesystems.php. Identifikasi disk local dan public — apa persis bedanya?

Di berkas `config/filesystems.php`, Laravel menyediakan konfigurasi disk penyimpanan bawaan, yaitu disk `local` dan disk `public`. Keduanya sama-sama menyimpan data di harddisk lokal server, tetapi memiliki perbedaan mendasar pada izin akses dan tujuannya.

Disk `local` memiliki root direktori di `storage/app/private` dan tidak memiliki URL publik. Artinya, berkas yang disimpan di disk ini sepenuhnya privat dan hanya bisa dibaca oleh kode PHP di server. Sebaliknya, disk `public` memiliki root di `storage/app/public` dan dilengkapi pengaturan URL `env('APP_URL').'/storage'`. Berkas di disk public memang sengaja disiapkan agar bisa diakses langsung lewat browser melalui tautan symlink. Karena itu, berkas akademik seperti materi perkuliahan dan tugas mahasiswa wajib menggunakan disk `local` agar tidak bisa dibuka sembarangan tanpa izin.

---

#### 5. Jalankan php artisan storage:link. Apa yang dibuatnya? Kenapa materi Anda tidak boleh ada di sana?

Ketika perintah `php artisan storage:link` dijalankan dari terminal, Laravel membuat sebuah jalan pintas sistem (symbolic link atau symlink) dari folder `storage/app/public` ke dalam folder `public/storage`. Melalui tautan ini, berkas statis yang ditaruh di folder publik bisa langsung disajikan oleh web server ke pengguna.

Materi kuliah tidak boleh diletakkan di sana karena siapa pun yang mengetahui atau menebak link berkasnya bisa langsung mengunduh materi tanpa harus login terlebih dahulu. Jika materi disimpan di disk public, sistem otorisasi dan Policy yang sudah dibuat di controller menjadi tidak berguna, karena pengguna bisa melewati controller dan langsung mengambil file dari link publik tersebut. Berkas materi dan tugas mahasiswa harus tetap berada di disk privat agar aksesnya selalu diperiksa oleh sistem.

---

### BREAK — Delapan Kerusakan (50 menit)

Berikut dokumentasi hasil pengujian delapan skenario kerusakan keamanan dan penanganan berkas:

| # | Yang Dicoba | Yang Diamati | Penjelasan Teknis & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Pindahkan berkas submission ke `storage/app/public`, lalu akses URL-nya tanpa login di jendela Incognito. | **Tugas seluruh kelas terbuka untuk publik.** Berkas PDF jawaban mahasiswa langsung terbuka dan bisa diunduh siapa saja tanpa perlu login. | **Kebocoran Privasi & Plagiarisme Fatal.** Ini kesalahan paling berbahaya dalam penanganan upload berkas. Jawaban tugas mahasiswa bersifat rahasia. Menaruhnya di disk publik membuat URL-nya statis dan tidak terlindungi. Mahasiswa lain bisa saling intip jawaban teman sekelasnya. |
| **2** | Hapus baris `Gate::authorize('view', $submission)` dari method `download` di `SubmissionController`, lalu akses ID submission orang lain. | **IDOR (*Insecure Direct Object Reference*) versi berkas.** Mahasiswa A bisa mengunduh file tugas milik Mahasiswa B hanya dengan mengganti nomor ID pada URL download. | **Otorisasi Server Wajib.** Tanpa `Gate::authorize()`, controller hanya mencari file berdasarkan ID database tanpa peduli siapa yang memintanya. Siapa pun yang login bisa mencuri berkas milik mahasiswa lain cukup dengan *looping* angka ID di URL. |
| **3** | Simpan berkas dengan method `storeAs()` memakai nama asli (`getClientOriginalName()`), lalu dua akun mengunggah file bernama sama `tugas.pdf` ke tugas yang sama. | **Berkas mahasiswa pertama tertimpa dan hilang.** File fisik `tugas.pdf` di server langsung tergantikan oleh file milik mahasiswa kedua. | **Konflik Nama Berkas (*File Overwrite*).** Nama asli dari komputer pengguna sering kali sama (misal `tugas.pdf` atau `jawaban.docx`). Jika disimpan dengan nama asli, file lama akan langsung tertimpa. Akibatnya, saat dosen menilai tugas mahasiswa pertama, berkas yang tampil justru milik mahasiswa kedua. Solusinya wajib memakai hash acak unik bawaan Laravel (`store()`). |
| **4** | Unggah berkas skrip PHP berbahaya (`exploit.php`) yang diubah namanya menjadi `exploit.pdf` dengan validasi `extensions`, lalu bandingkan dengan validasi `mimes`. | Validasi `extensions:pdf` **meloloskan** berkas tersebut, sedangkan validasi `mimes:pdf` **langsung menolaknya** dengan pesan error. | **Beda Memeriksa Nama vs Memeriksa Isi (MIME Type).** `extensions` hanya membaca tulisan ekstensi di ujung nama file (hanya pencocokan teks string biasa). Sedangkan `mimes` memeriksa *magic bytes* atau tipe MIME asli dari isi file (`application/pdf`). Validasi `mimes` mencegah penyerang menyelundupkan script berbahaya (*Remote Code Execution*) berkedok PDF. |
| **5** | Ubah aturan validasi di controller dari `max:5120` menjadi `max:5`, lalu coba unggah berkas PDF berukuran 1 MB. | **Upload langsung ditolak** oleh validasi form dengan pesan bahwa ukuran berkas melebihi batas. | **Satuan Validasi Laravel adalah Kilobyte (KB).** Banyak developer mengira `max:5` berarti 5 Megabyte. Padahal di Laravel, parameter ukuran file dihitung dalam Kilobyte. Jadi `max:5` artinya cuma 5 KB (5.120 byte). File 1 MB (~1.024 KB) tentu langsung gagal. Untuk batas 5 MB, nilai yang benar adalah `max:5120`. |
| **6** | Ubah konfigurasi `upload_max_filesize = 1M` di file `php.ini`, lalu unggah berkas 3 MB dengan validasi controller `max:5120`. | **Batas server mengalahkan validasi aplikasi.** Request gagal bahkan sebelum validasi controller Laravel sempat berjalan. Form kembali dengan file bernilai kosong / error upload code 1. | **Batasan Web Server Lebih Tinggi dari Aplikasi.** Sebelum request masuk ke framework Laravel, PHP engine di server sudah menyaring ukuran upload lebih dulu melalui `upload_max_filesize` dan `post_max_size`. Jika server membatasi 1 MB, aplikasi tidak akan pernah bisa menerima file 3 MB meskipun di Laravel diizinkan `max:5120`. |
| **7** | Hapus method `updateOrCreate` di `SubmissionController`, ganti dengan `create()`, lalu kumpulkan tugas dua kali untuk assignment yang sama. | Terjadi error crash halaman **SQL Integrity Constraint Violation (Unique Constraint Error)** pada percobaan kedua. | **Penyelamatan oleh Unique Index Database.** Di migrasi database minggu ke-3, ada aturan `$table->unique(['assignment_id', 'user_id'])`. Aturan ini menjamin satu mahasiswa tidak bisa punya dua baris submission pada tugas yang sama. Penggunaan `updateOrCreate` sangat penting agar pengumpulan ulang tidak crash dan berkas fisik lama bisa dibersihkan secara rapi. |
| **8** | Hapus satu baris data submission langsung lewat query SQL database, lalu periksa folder `storage/app/private/submissions`. | Baris di database hilang, tetapi **berkas fisiknya tertinggal di harddisk server sebagai berkas yatim (*orphan file*)**. | **Kebocoran Kapasitas Penyimpanan (*Storage Leak*).** Database tidak otomatis menghapus berkas di storage saat baris datanya dihapus via SQL manual. Jika aplikasi tidak menghapus file fisiknya lewat `Storage::disk('local')->delete()`, server lama-kelamaan akan penuh oleh ribuan file sampah tak bertuan yang tidak lagi tercatat di database. |

---
