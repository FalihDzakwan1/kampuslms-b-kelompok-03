### Nama: Fatika Rizki Syahada
#### NIM: 10241030

---

### READ — Telusuri satu berkas (30 menit)

#### 1. Temukan berkas fisiknya di sistem berkas. Path lengkapnya apa?
Saat saya login sebagai akun dosen dan mengunggah berkas materi kuliah, berkas tersebut disimpan di direktori privat server, bukan di folder publik. Di laptop saya, berkas fisiknya tersimpan di dalam folder `storage/app/private/materials/`. Path lengkapnya mengarah ke `/Users/fatikarizkisyahada/Documents/GitHub/kampuslms-b-kelompok-03/storage/app/private/materials/`.

Nama asli berkas yang diunggah otomatis diganti oleh fungsi `$file->store('materials', 'local')` di controller menjadi nama hash acak yang unik (misalnya `abc123xyz456.pdf`). Hal ini dilakukan agar nama berkas yang masuk tidak saling bertabrakan atau menimpa satu sama lain jika ada dosen lain yang mengunggah berkas dengan nama yang sama persis.

---

#### 2. Buka baris database yang bersangkutan. Bandingkan file_path dengan lokasi fisiknya.
Setelah berkas terunggah, saya memeriksa tabel `materials` di database untuk melihat baris data yang baru tersimpan. Pada kolom `file_path`, nilai yang tercatat hanya berupa path relatif dari disk privat, yaitu `materials/abc123xyz456.pdf`. Nilai ini berbeda dengan path fisik di penyimpanan laptop yang memuat path direktori lengkap dari akar sistem.

Database sengaja hanya menyimpan path relatif agar aplikasi tetap portabel. Jika path absolut komputer ikut disimpan, aplikasi akan bermasalah saat dipindahkan ke server produksi Linux di Railway atau ke penyimpanan cloud seperti AWS S3. Dengan path relatif, Laravel cukup menggabungkannya dengan konfigurasi root disk yang aktif di server.

---

#### 3. Coba akses berkas itu langsung lewat browser dengan menebak URL-nya. Berhasil atau tidak? Kenapa?
Saya mencoba membuka berkas tersebut secara langsung melalui browser dengan mengetikkan URL `http://127.0.0.1:8000/storage/app/private/materials/abc123xyz456.pdf`. Hasilnya, halaman langsung menampilkan error 404 Not Found dan berkas sama sekali tidak bisa dibuka atau diunduh.

Hal ini terjadi karena web server hanya membuka pintu akses publik ke folder `public/`. Folder `storage/app/private` berada di luar folder publik tersebut, sehingga web server otomatis memblokir permintaan langsung dari browser. Berkas privat hanya bisa diunduh melalui controller yang memanggil otorisasi Policy sebelum mengalirkan isi filenya ke pengguna.

---

#### 4. Buka config/filesystems.php. Identifikasi disk local dan public — apa persis bedanya?
Di berkas `config/filesystems.php`, Laravel menyediakan konfigurasi disk penyimpanan bawaan, yaitu disk `local` dan disk `public`. Keduanya memiliki perbedaan mendasar pada izin akses dan tujuannya.

Disk `local` memiliki root direktori di `storage/app/private` dan tidak memiliki URL publik (`'serve' => false`). Artinya, berkas di disk ini sepenuhnya privat dan hanya bisa dibaca oleh script PHP di server. Sebaliknya, disk `public` memiliki root di `storage/app/public` dan dilengkapi pengaturan URL `/storage`. Berkas di disk public memang disiapkan agar bisa diakses langsung lewat tautan symlink. Karena itu, materi kuliah dan submission tugas wajib menggunakan disk `local` agar tidak bisa dibuka sembarangan tanpa izin.

---

#### 5. Jalankan php artisan storage:link. Apa yang dibuatnya? Kenapa materi Anda tidak boleh ada di sana?
Ketika perintah `php artisan storage:link` dijalankan dari terminal, Laravel membuat sebuah jalan pintas sistem (symbolic link atau symlink) dari folder `storage/app/public` ke dalam folder `public/storage`. Melalui tautan ini, berkas statis yang ditaruh di folder publik bisa langsung disajikan oleh web server ke browser pengguna.

Materi kuliah tidak boleh diletakkan di sana karena siapa pun yang mengetahui atau menebak link berkasnya bisa langsung mengunduh materi tanpa harus login terlebih dahulu. Jika materi disimpan di disk public, sistem otorisasi dan Policy yang dibuat di controller menjadi tidak berguna karena pengguna bisa memotong jalur controller dan langsung mengambil file dari link publik tersebut.

---

### BREAK — Delapan Kerusakan (50 menit)

| # | Yang Dicoba | Yang Diamati | Penjelasan Teknis & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Pindahkan berkas submission ke `storage/app/public`, lalu akses URL-nya tanpa login di Incognito. | **Tugas seluruh kelas terbuka untuk publik.** Berkas PDF jawaban mahasiswa langsung terbuka dan bisa diunduh siapa saja tanpa login. | **Kebocoran Privasi Fatal.** Menaruh tugas di disk publik membuat URL-nya statis dan tidak terlindungi, sehingga mahasiswa bisa saling intip jawaban teman sekelas. |
| **2** | Hapus baris `Gate::authorize('view', $submission)` dari method download di controller, lalu akses ID submission orang lain. | **IDOR versi berkas.** Mahasiswa A bisa mengunduh file tugas milik Mahasiswa B hanya dengan mengganti nomor ID pada URL download. | **Otorisasi Server Wajib.** Tanpa Gate, controller langsung menyajikan file berdasarkan ID database tanpa memeriksa kepemilikan berkas. |
| **3** | Simpan berkas dengan method `storeAs()` memakai nama asli, lalu dua akun mengunggah file bernama sama `tugas.pdf`. | **Berkas mahasiswa pertama tertimpa dan hilang.** File fisik `tugas.pdf` di server langsung tergantikan oleh file milik akun kedua. | **Konflik Nama Berkas.** Menyimpan dengan nama asli membuat file saling menimpa. Solusinya wajib memakai hash acak unik bawaan Laravel (`store()`). |
| **4** | Unggah berkas script PHP (`exploit.php`) yang diubah namanya menjadi `exploit.pdf` dengan validasi `extensions` vs `mimes`. | Validasi `extensions:pdf` meloloskan berkas tersebut, sedangkan validasi `mimes:pdf` langsung menolaknya dengan error. | **Beda Ekstensi vs MIME Type.** `extensions` hanya membaca teks ujung nama file, sedangkan `mimes` memeriksa isi berkas (*magic bytes*). `mimes` mencegah script jahat disusupkan. |
| **5** | Ubah aturan validasi di controller dari `max:5120` menjadi `max:5`, lalu coba unggah berkas PDF 1 MB. | **Upload langsung ditolak** oleh validasi form dengan pesan bahwa ukuran berkas melebihi batas. | **Satuan Validasi Laravel adalah KB.** Parameter ukuran file di Laravel dihitung dalam Kilobyte. `max:5` artinya cuma 5 KB. Untuk batas 5 MB, nilai yang benar adalah `max:5120`. |
| **6** | Ubah konfigurasi `upload_max_filesize = 1M` di file `php.ini`, lalu unggah berkas 3 MB dengan validasi controller `max:5120`. | **Batas server mengalahkan validasi aplikasi.** Request gagal di level PHP engine sebelum validasi controller Laravel sempat berjalan. | **Batasan Server Lebih Tinggi.** Jika web server membatasi upload 1 MB, aplikasi tidak akan pernah bisa menerima file 3 MB meskipun di Laravel diizinkan `max:5120`. |
| **7** | Hapus method `updateOrCreate` di controller, ganti dengan `create()`, lalu kumpulkan tugas dua kali untuk tugas yang sama. | Terjadi error crash halaman **SQL Integrity Constraint Violation** pada percobaan kedua. | **Penyelamatan oleh Unique Index.** Database memiliki constraint unik `(assignment_id, user_id)`. Penggunaan `updateOrCreate` wajib agar pengumpulan ulang menimpa file lama tanpa crash. |
| **8** | Hapus satu baris data submission langsung lewat query SQL database, lalu periksa folder storage privat. | Baris di database hilang, tetapi **berkas fisiknya tertinggal di harddisk server sebagai berkas yatim (*orphan file*)**. | **Kebocoran Kapasitas Penyimpanan.** Menghapus baris SQL manual tidak menghapus file di storage. Aplikasi harus menghapus file fisiknya lewat `Storage::delete()` agar server tidak penuh sampah. |
