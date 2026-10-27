# Catatan Individu Minggu - 10 - Elsya Nur Aulia Handayani (10241026)

## 10.3 Read → Break → Fix → Build

### READ — Ikuti satu job (30 menit)

### 1. Matikan worker. Buat satu tugas baru. Buka tabel jobs — apa isinya?

Sebelum membuat tugas, saya mematikan worker terlebih dahulu dengan menutup terminal `queue:work`. Setelah itu, saya masuk menggunakan akun dosen dan membuat satu tugas baru dengan status *published* (tugas yang berstatus *draft* sengaja tidak memicu event notifikasi). Pembuatan tugas tetap berjalan cepat dan berhasil disimpan karena aplikasi hanya memproses penyimpanan data tugas ke database, sedangkan pengiriman notifikasi ke mahasiswa dititipkan ke sistem antrean (queue).

Ketika membuka tabel `jobs` di database, saya melihat ada satu baris baru yang muncul setelah tugas selesai dibuat. Baris ini merupakan antrean pekerjaan pengiriman notifikasi yang statusnya masih menunggu untuk diproses. Di dalamnya terdapat kolom `payload` yang memuat data pekerjaan, serta kolom `attempts` yang masih bernilai 0 karena job tersebut memang belum pernah disentuh oleh worker. Selama terminal worker belum dijalankan, baris pekerjaan ini akan terus tersimpan dan tertahan di tabel `jobs`.

Jika tabel `jobs` ternyata tidak bertambah setelah tugas dibuat, saya harus mengecek apakah event `AssignmentPublished` benar-benar dipicu di controller, apakah listener `SendAssignmentNotification` sudah terhubung dan aktif, serta memastikan file `.env` sudah menggunakan `QUEUE_CONNECTION=database`. Dari sini terlihat jelas bahwa tugas yang berhasil disimpan oleh dosen belum tentu notifikasinya sudah langsung terkirim ke mahasiswa.

### 2. Baca kolom payload. Bisakah Anda mengenali nama Listener dan ID tugasnya?

Kolom `payload` menyimpan data pekerjaan dalam bentuk teks JSON berukuran panjang. Karena isinya berupa deretan teks terkompresi yang tidak rapi seperti tabel biasa, saya membuka dan menyalin nilainya ke editor teks agar strukturnya lebih mudah dibaca. Dari data JSON tersebut, nama listener dapat langsung dikenali pada bagian `"displayName"` yang menunjukkan class `App\Listeners\SendAssignmentNotification`.

Selain nama listener, ID tugas juga dapat ditemukan di dalam bagian data event yang ikut diserialisasi oleh Laravel. Pada objek model `Assignment` yang tersimpan di dalam payload, terdapat atribut `id` tugas yang sesuai dengan tugas yang baru saja dibuat. Untuk memastikannya, saya mencocokkan nomor ID tersebut dengan baris data tugas pada tabel `assignments`.

Jika informasi listener atau tugas tidak ditemukan di dalam payload, ada beberapa hal yang perlu saya periksa: apakah listener tersebut sudah mengimplementasikan `ShouldQueue`, apakah objek tugas yang dikirim oleh event sudah benar, dan apakah baris job yang saya periksa memang berasal dari pembuatan tugas yang terakhir dilakukan.

### 3. Jalankan php artisan queue:work, amati keluarannya. Periksa tabel jobs lagi.

Setelah memastikan baris antrean sudah masuk ke tabel `jobs`, saya membuka terminal dan menjalankan worker dengan perintah:

```bash
php artisan queue:work
```

Worker langsung mendeteksi pekerjaan yang tersedia dan memprosesnya. Pada keluaran terminal, muncul pesan bahwa listener `SendAssignmentNotification` sedang diproses (`Processing`), lalu disusul pesan berhasil (`Processed`). Ketika saya kembali membuka dan me-refresh tabel `jobs`, baris antrean yang sebelumnya ada langsung hilang dan tabel menjadi kosong. Hal ini terjadi karena Laravel otomatis menghapus job yang sudah sukses diproses agar database tetap bersih dan tidak terjadi pengiriman ganda.

Apabila baris pekerjaan di tabel `jobs` tidak kunjung hilang atau muncul status error di terminal, saya perlu membaca pesan kegagalan tersebut untuk mengetahui apakah terjadi kesalahan pada query, koneksi database, atau logika di listener. Dari situ saya juga bisa memantau apakah job sedang menunggu jeda percobaan ulang (backoff) atau sudah dipindahkan ke tabel kegagalan (`failed_jobs`).

Setelah proses pengujian selesai, worker dapat dimatikan kembali menggunakan `Ctrl+C`. Percobaan ini menegaskan bahwa jika worker lupa dijalankan di server, seluruh antrean notifikasi hanya akan menumpuk di database tanpa pernah sampai ke tangan mahasiswa.

### 4. Periksa tabel notifications. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?

Setelah worker selesai memproses antrean, saya memeriksa tabel `notifications` untuk memastikan data notifikasi benar-benar masuk. Jumlah baris notifikasi yang dihasilkan harus cocok dengan jumlah mahasiswa yang aktif dan terdaftar pada mata kuliah tempat tugas tersebut dipublikasikan.

Sebagai contoh, jika mata kuliah tersebut diikuti oleh 5 mahasiswa yang memenuhi syarat penerima, maka setelah job selesai dieksekusi seharusnya muncul tepat 5 baris baru di tabel `notifications`. Setiap baris mencatat ID masing-masing mahasiswa pada kolom `notifiable_id`, sedangkan kolom `read_at` masih bernilai `null` karena notifikasi tersebut belum dibuka oleh mahasiswa bersangkutan.

Jika baris notifikasi yang muncul jumlahnya lebih sedikit dari daftar mahasiswa kelas, saya perlu memeriksa apakah query pengambilan data mahasiswa di listener sudah benar atau ada mahasiswa yang terlewat. Sebaliknya, jika jumlah barisnya melebihi jumlah mahasiswa, kemungkinan event terpicu lebih dari sekali saat dosen menyimpan tugas. Karena itu, jumlah baris aktual harus selalu dicocokkan dengan data tabel `course_user`.

### 5. Baca kolom data pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

Kolom `data` di tabel `notifications` menyimpan rincian pemberitahuan dalam format JSON. Pada notifikasi tugas baru, isinya memuat informasi penting agar mahasiswa mengetahui konteks tugas tersebut, seperti `assignment_id`, `course_name`, `title`, `due_at`, dan `url` menuju halaman tugas.

Data di dalam payload notifikasi sengaja dibatasi dan tidak memuat informasi sensitif, seperti nilai mahasiswa atau isi komentar koreksi dari dosen. Hal ini karena kolom `data` tersimpan dalam bentuk teks biasa tanpa enkripsi, sehingga siapa pun yang memiliki akses ke database dapat melihat seluruh isinya secara langsung.

Ketika saya memeriksa salah satu baris notifikasi tugas baru, seluruh isinya sudah sesuai dan aman karena hanya berupa metadata publik mengenai tugas. Jika nantinya ada notifikasi penilaian, nilai angka dan feedback tetap tidak boleh dimasukkan ke dalam payload JSON ini, melainkan mahasiswa harus diarahkan untuk melihat nilainya langsung di halaman detail submission yang dilindungi oleh Policy otorisasi.

### BREAK — Delapan Kerusakan (50 menit)

Dokumentasi hasil eksperimen dengan sengaja merusak atau memodifikasi konfigurasi antrean dan notifikasi:

| # | Yang Dicoba | Yang Diamati | Penjelasan Teknis & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Set `QUEUE_CONNECTION=database` di `.env`, matikan worker (`queue:work`), lalu buat tugas baru lewat akun dosen. | **Gagal total tanpa satu pun pesan error.** Dosen merasa tugas berhasil dibuat (muncul pesan sukses), tetapi di akun mahasiswa lonceng notifikasi tidak pernah berbunyi dan tabel notifikasi kosong melompong. | **Kegagalan Diam-diam (*Silent Failure*).** Ini masalah paling berbahaya di lingkungan server/produksi. Aplikasi tampak sehat dan normal, padahal seluruh pekerjaan latar belakang mandek menumpuk di tabel `jobs`. Jika tidak ada sistem pemantau (*monitoring*) yang memastikan worker selalu hidup, sistem notifikasi mati total tanpa ada log error apa pun. |
| **2** | Hapus `implements ShouldQueue` dari class `SendAssignmentNotification`, lalu simpan tugas untuk mata kuliah berisi 40 mahasiswa. | **Browser dosen berputar (*loading*) sangat lama.** Dosen harus menunggu beberapa detik tertahan di tombol "Simpan" sampai seluruh 40 notifikasi selesai ditulis satu per satu ke database sebelum halaman berpindah. | **Menyandera Pengguna (*Blocking Request*).** Tanpa `ShouldQueue`, request HTTP dipaksa mengerjakan hal-hal sekunder yang bukan prioritas saat itu. Jika ke depan notifikasi ditambah via email (SMTP), 40 koneksi email bisa membuat browser dosen *timeout* 30 detik atau bahkan gagal menyimpan tugas jika salah satu email bermasalah. |
| **3** | Ganti `QUEUE_CONNECTION=sync` di `.env`, lalu ulangi percobaan nomor 2 (membuat tugas baru). | Browser dosen tetap terasa lambat (mirip nomor 2), tetapi bedanya kode listener tidak perlu diubah-ubah. Job langsung diproses seketika di dalam siklus request yang sama. | **Menghilangkan Manfaat Antrean.** Driver `sync` berguna saat testing otomatis (`phpunit`) agar deterministik dan cepat selesai dalam satu baris perintah. Namun di server produksi, memakai `sync` sama saja dengan mematikan antrean latar belakang karena server kembali lambat dan membebani pengguna. |
| **4** | Sengaja tambahkan kode error `throw new Exception("Simulasi gagal");` di dalam Notification/Listener, lalu jalankan worker. | Worker mencoba menjalankan job tersebut sebanyak **3 kali** dengan jeda masing-masing 10 detik (`tries = 3`, `backoff = 10`). Setelah percobaan ke-3 tetap gagal, job dipindahkan ke tabel **`failed_jobs`** dan method `failed()` mencatat pesan error ke `laravel.log`. | **Mekanisme Ketahanan (*Queue Resilience*).** Fitur `tries` dan `backoff` mencegah sistem langsung menyerah saat ada gangguan jaringan sesaat. Begitu kuota mencoba habis, data kegagalan tidak hilang melainkan diamankan di `failed_jobs` agar bisa diinvestigasi oleh developer atau diulang kembali lewat perintah `php artisan queue:retry all`. |
| **5** | Hentikan paksa terminal worker (`Ctrl + C`) tepat saat worker sedang memproses job. | **Job kembali aman ke tabel `jobs`.** Status reservasi (`reserved_at`) dilepas kembali sehingga saat worker dinyalakan ulang, job tersebut langsung diproses kembali dari awal. | **Perlindungan *Atomic Job / Database Locking*.** Worker memakai transaksi dan batas waktu sewa (*reserved timeout*). Jika server mati mendadak (misal listrik padam atau server restart), job yang belum sempat menghasilkan status `DONE` tidak akan hilang tercecer, melainkan otomatis dikembalikan ke antrean untuk dicoba lagi saat server nyala. |
| **6** | Masukkan angka nilai mahasiswa (`score` dan `feedback`) ke dalam payload `toDatabase()` pada notifikasi nilai, lalu buka tabel `notifications`. | **Nilai dan catatan evaluasi mahasiswa terpampang bebas** dalam bentuk teks biasa di kolom `data` tabel `notifications`. Siapa pun yang bisa melihat database (admin, teknisi DB) bisa membaca nilai rahasia seluruh mahasiswa tanpa batasan. | **Kebocoran Data Sensitif (*Data Leak*).** Kolom `data` di tabel notifications tidak memiliki enkripsi bawaan. Jika isi feedback dosen memuat teguran pribadi atau nilai rahasia, data itu bocor. Solusinya: payload hanya boleh memuat ID dan judul; mahasiswa wajib membuka halaman detail submission yang dilindungi Policy untuk melihat nilainya. |
| **7** | Kirim request `POST /notifications/{id}/read` menggunakan akun Mahasiswa A, tetapi memasukkan ID notifikasi milik Mahasiswa B. | Server merespons dengan **HTTP 404 Not Found** (atau 403 Forbidden). Status notifikasi milik Mahasiswa B tetap utuh (*unread*). | **Pencegahan IDOR Notifikasi Berhasil.** Karena di controller kita memakai `$request->user()->notifications()->findOrFail($id)`, query pencarian dikunci hanya pada relasi milik user yang sedang login. Mahasiswa A tidak bisa mengubah atau mengacak-acak status notifikasi milik mahasiswa lain. |
| **8** | Jika memakai Laravel Reverb: ubah channel penyiaran dari `PrivateChannel('users.' . $id)` menjadi `Channel('notifications')` publik. | Mahasiswa A bisa membuka tab console browser dan mendengarkan (*listen*) channel tersebut, lalu membaca pesan notifikasi tugas dan nilai milik Mahasiswa B secara realtime. | **Penyadapan Realtime (*WebSocket Eavesdropping*).** Saluran publik `Channel` tidak memerlukan otentikasi token. Siapa pun bisa *subscribe* dan menyadap lalu lintas data yang lewat. Karena itu, data yang menyangkut akun pengguna wajib menggunakan `PrivateChannel` yang dijaga oleh otorisasi channel di `routes/channels.php`. |

---

