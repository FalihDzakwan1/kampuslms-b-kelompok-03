### Nama: Fatika Rizki Syahada
#### NIM: 10241030

## A. READ — Ikuti satu job (30 menit)

### 1. Matikan worker. Buat satu tugas baru. Buka tabel jobs — apa isinya?

Pada saat terminal worker tidak diaktifkan, saya mencoba mempublikasikan satu tugas baru lewat akun dosen (tugas harus berstatus published, karena status draft memang dirancang tidak memicu notifikasi). Dari sisi pengguna, tugas langsung berhasil dibuat tanpa loading lama karena aplikasi hanya memproses penyimpanan data tugas ke basis data, sedangkan urusan pengiriman notifikasi diserahkan ke sistem antrean (queue).

Ketika saya memeriksa isi tabel `jobs` di database, muncul satu baris data baru setelah tugas selesai disimpan. Baris tersebut memuat informasi job yang sedang antre menunggu dieksekusi, termasuk isi payload dan waktu job dimasukkan. Kolom `attempts` masih bernilai 0 karena pekerjaan tersebut belum pernah disentuh oleh worker. Selama perintah `queue:work` belum dijalankan di terminal, pekerjaan ini akan tetap tersimpan dan tertahan di tabel `jobs`.

Apabila baris di tabel `jobs` tidak bertambah, hal yang perlu saya telusuri adalah apakah event `AssignmentPublished` benar-benar dipanggil di controller, apakah listener sudah terhubung dengan event tersebut, serta memastikan file `.env` sudah menggunakan `QUEUE_CONNECTION=database`. Jadi, tersimpannya tugas di halaman dosen bukan jaminan bahwa notifikasinya sudah langsung sampai ke mahasiswa.

### 2. Baca kolom payload. Bisakah Anda mengenali nama Listener dan ID tugasnya?

Kolom `payload` menyimpan rincian antrean dalam bentuk string JSON. Karena tampilannya panjang dan terkompresi, saya menyalin teks tersebut ke editor agar susunan datanya lebih mudah dipahami. Nama class listener dapat dikenali pada properti `"displayName"`, yaitu `App\Listeners\SendAssignmentNotification`.

Selain nama listener, ID tugas yang bersangkutan juga bisa ditemukan di dalam data event yang diserialisasi oleh Laravel. Pada bagian objek `Assignment`, terdapat atribut ID yang nilainya cocok dengan tugas yang baru saja saya tambahkan. Untuk memastikan ketepatannya, saya mencocokkan angka ID tersebut dengan data pada tabel `assignments`.

Jika data listener atau ID tugas tidak tampak di payload, saya perlu meninjau kembali apakah listener memang sudah menerapkan `ShouldQueue`, apakah objek tugas yang dikirim event sudah sesuai, serta memastikan bahwa baris antrean yang saya periksa memang berasal dari pembuatan tugas yang baru saja saya lakukan.

### 3. Jalankan php artisan queue:work, amati keluarannya. Periksa tabel jobs lagi.

Setelah memastikan ada pekerjaan yang antre di database, saya membuka terminal lalu menjalankan worker:

```bash
php artisan queue:work
```

Worker langsung mendeteksi job yang menunggu dan mengeksekusinya. Pada layar terminal, muncul log yang menandakan listener `SendAssignmentNotification` sedang diproses (`Processing`), lalu disusul status selesai (`Processed`). Saat saya mengecek kembali tabel `jobs`, baris antrean tersebut sudah hilang dan tabel menjadi kosong kembali. Hal ini terjadi karena Laravel langsung menghapus job yang sukses diproses agar database tidak penuh dan tidak ada pekerjaan yang dieksekusi ganda.

Jika baris pekerjaan tetap tidak hilang atau justru muncul keterangan gagal, saya harus membaca pesan error di terminal untuk memeriksa apakah ada kendala pada kueri, koneksi database, atau logika di dalam listener. Dari sana saya juga bisa melihat apakah job sedang menunggu jeda percobaan ulang (backoff) atau sudah masuk ke tabel `failed_jobs`.

Setelah selesai mencoba, worker bisa dimatikan dengan menekan `Ctrl+C`. Namun jika worker dimatikan terus-menerus, job antrean hanya akan menumpuk di database dan pesan notifikasi tidak akan pernah sampai ke akun mahasiswa.

### 4. Periksa tabel notifications. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?

Setelah worker selesai memproses job, saya membuka tabel `notifications` di database untuk melihat apakah rekaman notifikasinya sudah tersimpan. Jumlah baris yang dihasilkan seharusnya persis sama dengan jumlah mahasiswa yang aktif dan terdaftar pada mata kuliah tempat tugas tersebut dibuat.

Sebagai contoh, jika pada kelas tersebut terdapat 30 mahasiswa yang terdaftar, maka setelah worker selesai berjalan akan muncul 30 baris notifikasi baru untuk tugas tersebut. Tiap baris mencatat ID masing-masing mahasiswa pada kolom `notifiable_id`, dan kolom `read_at` masih bernilai `null` karena mahasiswa belum membukanya.

Apabila jumlah baris notifikasi lebih sedikit, saya harus mengecek apakah query penarikan mahasiswa di listener sudah menjaring seluruh peserta kelas, atau ada job pengiriman yang gagal di tengah jalan. Sebaliknya jika barisnya lebih banyak, ada kemungkinan event notifikasi terpicu lebih dari satu kali. Oleh sebab itu, jumlah data pada tabel `notifications` harus selalu dicocokkan dengan data mahasiswa pada tabel `course_user`.

### 5. Baca kolom data pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

Kolom `data` di tabel `notifications` menyimpan ringkasan isi notifikasi dalam format JSON. Pada pengumuman tugas baru, data yang dimasukkan cukup berupa informasi ringkas yang dibutuhkan mahasiswa untuk mengenali tugas, seperti `assignment_id`, `course_name`, `title`, `due_at`, dan tautan `url` ke halaman tugas.

Payload ini sengaja tidak memuat informasi sensitif, seperti nilai hasil tugas maupun teks koreksi atau feedback dari dosen. Hal ini karena data di kolom `data` tersimpan dalam bentuk teks biasa tanpa enkripsi database, sehingga siapa pun yang bisa membuka tabel tersebut bisa membaca isinya.

Saya memeriksa salah satu baris notifikasi tugas baru dan memastikan isinya hanya metadata umum terkait tugas. Jika ada notifikasi penilaian, nilai angka dan feedback tetap tidak boleh disertakan di payload ini, melainkan mahasiswa diarahkan untuk membuka halaman tugas yang aksesnya dijaga ketat oleh Policy.

---

## B. BREAK — Delapan kerusakan (50 menit)

### 1. Set QUEUE_CONNECTION=database, jangan jalankan worker, buat tugas baru

Pada pengujian ini, saya menyetel konfigurasi `.env`:

```env
QUEUE_CONNECTION=database
```

Setelah itu saya mematikan terminal worker dan membuat tugas baru lewat web. Tugas berhasil disimpan di database, tetapi tugas notifikasi hanya masuk ke tabel `jobs` dan terhenti di sana karena tidak ada proses worker yang menjalankannya.

Hal yang saya perhatikan adalah aplikasi tetap berjalan lancar tanpa memunculkan pesan error apa pun di browser. Notifikasi gagal sampai bukan karena ada bug pada form tugas, melainkan murni karena antrean di database tidak ada yang memproses. Inilah bukti kenapa worker antrean wajib selalu aktif di server.

Untuk membuktikannya, saya cukup membuka tabel `jobs` dan melihat antrean yang tertahan di sana. Setelah pengujian selesai, saya menyalakan kembali worker dengan `php artisan queue:work`.

### 2. Hapus implements ShouldQueue dari Listener, buat tugas untuk 40 mahasiswa

Pada percobaan ini, saya menghapus `implements ShouldQueue` dari class `SendAssignmentNotification`, lalu mencoba mempublikasikan tugas untuk kelas yang mahasiswanya banyak (misalnya 40 mahasiswa). Tanpa `ShouldQueue`, proses pengiriman notifikasi dipaksa berjalan seketika di dalam request web yang sama.

Dampaknya, browser dosen berputar cukup lama setelah tombol simpan ditekan, karena sistem harus menulis 40 baris notifikasi ke database satu per satu sebelum merespons halaman. Jika di masa mendatang notifikasi dikirim lewat email, dosen bisa mengalami timeout yang parah.

Saya membandingkan waktu tunggu tersebut dengan versi yang memakai `ShouldQueue`, di mana request langsung selesai instan karena pekerjaan berat dialihkan ke latar belakang. Setelah pengujian, baris `implements ShouldQueue` wajib dipasang kembali.

### 3. Ganti QUEUE_CONNECTION=sync, ulangi nomor 2

Saya mengubah pengaturan di file `.env` menjadi:

```env
QUEUE_CONNECTION=sync
```

Kemudian saya mencoba membuat tugas kembali. Pada mode `sync`, antrean tidak disimpan ke tabel `jobs`, melainkan dieksekusi langsung saat request berlangsung.

Hasilnya, waktu respons halaman tetap terasa lambat mirip dengan saat `ShouldQueue` dihapus, karena server tetap harus menyelesaikan seluruh pekerjaan pengiriman notifikasi sebelum mengirimkan respons HTTP ke browser. Driver `sync` memang cocok untuk kemudahan pengujian otomatis (testing), tetapi di server nyata manfaat pemrosesan asinkron jadi hilang. Setelah selesai, konfigurasi saya kembalikan ke `QUEUE_CONNECTION=database`.

### 4. Buat Notification melempar exception, jalankan worker

Untuk menguji bagaimana antrean menangani error, saya menambahkan simulasi kesalahan berupa exception pada notifikasi:

```php
throw new \Exception("Uji coba job gagal");
```

Pada listener yang diuji, terdapat pengaturan batas percobaan dan jeda waktu:

```php
public int $tries = 3;
public int $backoff = 10;
```

Ketika worker dijalankan, sistem tidak langsung menyerah melainkan mencoba mengulanginya hingga 3 kali dengan jeda masing-masing 10 detik. Setelah kesempatan ketiga tetap gagal, job tersebut otomatis dicatat ke dalam tabel `failed_jobs`.

Saya memverifikasinya melalui terminal menggunakan perintah:

```bash
php artisan queue:failed
```

Data job yang gagal terlihat jelas di tabel tersebut lengkap dengan catatan error-nya, sehingga pengembang bisa memeriksa penyebab masalahnya dan menjalankan ulang job tersebut lewat `php artisan queue:retry all`. Setelah pengujian, baris exception buatan tersebut langsung saya hapus.

### 5. Hentikan worker di tengah pemrosesan job

Pada pengujian ini, saya mematikan terminal worker secara mendadak (`Ctrl+C`) tepat ketika pekerjaan pengiriman notifikasi sedang berlangsung.

Tujuannya adalah mengamati apakah data job tersebut akan hilang atau kembali ke antrean. Laravel menggunakan sistem penguncian sewa (*reserved timeout*); jika worker mati sebelum mengirimkan sinyal selesai, status reservasi pada kolom `reserved_at` di tabel `jobs` akan dilepas kembali setelah batas waktunya habis. Job tersebut kemudian kembali berstatus siap dieksekusi oleh worker berikutnya.

Hal ini membuktikan keandalan sistem antrean database: jika terjadi gangguan daya atau restart mendadak pada server, pekerjaan yang belum selesai tidak akan lenyap, melainkan akan diproses kembali saat worker dinyalakan ulang.

### 6. Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel notifications sebagai admin

Saya mencoba menyisipkan nilai dan feedback koreksi ke dalam method `toDatabase()` pada class notifikasi nilai, lalu memeriksa kolom `data` di tabel `notifications`.

Hasilnya, nilai dan komentar evaluasi mahasiswa langsung terbaca jelas dalam bentuk teks polos di database. Siapa pun yang memiliki hak akses ke database bisa melihat nilai tersebut tanpa batas, padahal informasi nilai bersifat privat bagi masing-masing mahasiswa.

Solusinya, informasi nilai dan feedback wajib dihilangkan dari payload notifikasi. Notifikasi cukup memuat judul tugas dan ID submission saja, sementara mahasiswa yang ingin melihat nilai harus membukanya melalui halaman detail yang menerapkan verifikasi hak akses Policy.

### 7. Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain

Saya menguji celah keamanan otorisasi IDOR pada fitur notifikasi menggunakan dua akun mahasiswa berbeda (Mahasiswa A dan Mahasiswa B). Saat login sebagai Mahasiswa B, saya mengirim request untuk menandai notifikasi milik Mahasiswa A sebagai sudah dibaca.

Pada kode controller kami, pengambilan notifikasi dilakukan melalui relasi user yang sedang login atau menggunakan Policy:

```php
$notification = DatabaseNotification::findOrFail($id);
Gate::authorize('update', $notification);
$notification->markAsRead();
```

Ketika Mahasiswa B mencoba mengirim ID notifikasi milik Mahasiswa A, sistem langsung menolak dengan status **HTTP 403 Forbidden** (atau 404 jika memakai `$request->user()->notifications()->findOrFail($id)`). Ini membuktikan bahwa status notifikasi orang lain terlindungi dan tidak bisa diubah sembarangan oleh pengguna lain.

### 8. Kalau memakai Reverb: ganti PrivateChannel menjadi Channel, langganan channel orang lain

Pada pengujian fitur realtime menggunakan WebSocket, saya membandingkan penggunaan saluran publik (`Channel`) dengan saluran privat (`PrivateChannel`).

Jika saluran diubah menjadi publik:
```php
new Channel('notifications')
```
Siapa pun bisa membuka tab console di browser dan mendengarkan event yang disiarkan tanpa perlu login atau token otentikasi. Akibatnya, notifikasi tugas dan nilai milik pengguna lain dapat disadap secara bebas.

Oleh karena itu, penyiaran notifikasi wajib memakai `PrivateChannel('users.' . $this->userId)` yang mengharuskan otentikasi di `routes/channels.php`. Dengan demikian, server memastikan bahwa hanya pengguna yang berhak yang diizinkan untuk berlangganan dan menerima data pada channel tersebut.
