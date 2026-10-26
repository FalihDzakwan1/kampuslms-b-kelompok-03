**Nama:** Indriani Anwar  
**NIM:** 10241036

## A. READ — Ikuti satu job (30 menit)

### 1. Matikan worker. Buat satu tugas baru. Buka tabel jobs — apa isinya?

Saat worker dimatikan, saya membuat satu tugas baru berstatus *published* lewat aplikasi (tugas berstatus *draft* sengaja tidak memicu event notifikasi). Tugas tetap tersimpan karena proses penyimpanan tugas dilakukan oleh aplikasi, sedangkan pengiriman notifikasi dititipkan ke queue. Jika Event dan Listener sudah terhubung dengan benar serta `QUEUE_CONNECTION=database`, pekerjaan notifikasi seharusnya masuk ke tabel `jobs`.

Di tabel `jobs`, saya perlu melihat apakah ada baris baru setelah tugas dibuat. Baris tersebut berisi informasi job yang menunggu diproses, termasuk payload dan waktu job dimasukkan ke queue. Selama worker belum dijalankan, job akan tetap menunggu di tabel tersebut.

Jika tabel `jobs` tidak bertambah, saya perlu memeriksa apakah Event benar-benar dipanggil, Listener terdaftar dan berjalan, serta konfigurasi `.env` menggunakan `QUEUE_CONNECTION=database`. Jadi, tugas yang berhasil tersimpan belum tentu berarti notifikasinya sudah dikirim.

### 2. Baca kolom payload. Bisakah Anda mengenali nama Listener dan ID tugasnya?

Kolom `payload` berisi data job dalam format JSON. Isinya memang tidak ditampilkan seperti tabel biasa, jadi saya perlu membuka atau menyalin nilai `payload` untuk melihat detailnya. Jika Listener yang dibuat adalah `SendAssignmentNotification`, nama class tersebut seharusnya dapat dikenali pada informasi job. Data Event atau model yang dikirim bersama job juga dapat membantu menemukan ID tugas, tergantung cara job disusun dan diserialisasi oleh Laravel.

Saya tidak bisa memastikan nama Listener dan ID tugas dari contoh saja karena nilainya bergantung pada job yang benar-benar masuk ke tabel. Untuk memastikannya, saya perlu membaca payload dari baris yang baru dibuat dan mencocokkan ID tugasnya dengan tabel `assignments`.

Jika payload tidak menunjukkan informasi yang saya harapkan, saya perlu memeriksa apakah Listener memang menggunakan `ShouldQueue`, apakah Event mengirim objek tugas yang benar, dan apakah job yang dilihat merupakan job dari proses yang baru saja dijalankan.

### 3. Jalankan php artisan queue:work, amati keluarannya. Periksa tabel jobs lagi.

Setelah memastikan ada job yang menunggu, saya menjalankan worker dari terminal:

```bash
php artisan queue:work
```

Worker akan mengambil job yang tersedia dan mencoba menjalankannya. Jika job berhasil diproses, baris job tersebut seharusnya hilang dari tabel `jobs`. Terminal juga akan menampilkan informasi bahwa job telah diproses, termasuk nama job atau Listener yang dijalankan.

Jika barisnya masih ada atau muncul pesan gagal, saya perlu membaca keluaran terminal dan memeriksa apakah job sedang dicoba ulang atau gagal diproses. Saya juga perlu memastikan worker menggunakan konfigurasi aplikasi yang benar dan tidak sedang terhubung ke database atau environment yang berbeda.

Setelah pengujian, worker bisa dihentikan dengan `Ctrl+C`. Namun, jika worker tidak dijalankan sama sekali, job database hanya akan menumpuk dan notifikasi tidak diproses.

### 4. Periksa tabel notifications. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?

Setelah job diproses, saya memeriksa tabel `notifications` untuk melihat apakah notifikasi benar-benar tersimpan. Jika satu tugas baru dikirimkan kepada seluruh mahasiswa yang terdaftar pada mata kuliah tersebut, jumlah notifikasi yang diharapkan seharusnya sesuai dengan jumlah mahasiswa penerima yang memenuhi syarat.

Misalnya, jika ada 40 mahasiswa terdaftar dan semuanya termasuk penerima notifikasi, seharusnya muncul 40 baris notifikasi untuk tugas tersebut. Namun, jumlah sebenarnya harus dibandingkan dengan data mahasiswa yang benar-benar terdaftar, bukan hanya angka perkiraan.

Jika jumlah baris lebih sedikit, saya perlu memeriksa apakah semua mahasiswa terambil sebagai penerima, apakah ada job yang gagal, atau apakah Listener berhenti sebelum semua notifikasi dikirim. Jika jumlahnya lebih banyak, saya perlu memeriksa kemungkinan Event terpicu lebih dari sekali atau notifikasi terkirim berulang.

Jumlah baris aktual tidak bisa ditentukan hanya dari kode contoh. Karena itu, saya perlu membandingkan tabel `notifications` dengan daftar mahasiswa pada mata kuliah yang diuji.

### 5. Baca kolom data pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

Kolom `data` menyimpan isi notifikasi dalam format JSON. Untuk notifikasi tugas baru, data yang diperlukan cukup berupa informasi untuk mengenali tugas dan membuka halamannya, misalnya `assignment_id`, `course_name`, `title`, `due_at`, dan `url`.

Payload notifikasi tidak seharusnya menyimpan data sensitif, seperti nilai mahasiswa atau isi feedback dari dosen. Data pada tabel `notifications` tersimpan di database dan dapat dibaca oleh pihak yang memiliki akses ke database tersebut. Karena itu, informasi yang tidak perlu disimpan di sana sebaiknya tidak dimasukkan.

Saya perlu membuka salah satu baris notifikasi dan memeriksa kolom `data`. Jika hanya berisi ID dan informasi tugas yang diperlukan, payload tersebut sesuai dengan tujuan notifikasi. Jika ada nilai atau feedback mahasiswa, data itu perlu dihapus dari payload. Mahasiswa dapat melihat nilai dan feedback melalui halaman tugas atau halaman nilai yang memeriksa hak akses pengguna.

## B. BREAK — Delapan kerusakan (50 menit)

### 1. Set QUEUE_CONNECTION=database, jangan jalankan worker, buat tugas baru

Untuk pengujian ini, saya memastikan `.env` berisi:

```env
QUEUE_CONNECTION=database
```

Kemudian saya tidak menjalankan `php artisan queue:work` dan membuat satu tugas baru. Tugas seharusnya tetap tersimpan, tetapi job notifikasi hanya masuk ke tabel `jobs` dan tidak diproses karena tidak ada worker yang mengambilnya.

Hal yang perlu saya amati adalah aplikasi tetap terlihat normal dan tidak selalu menampilkan pesan error. Notifikasi tidak sampai bukan karena tugas gagal dibuat, melainkan karena pekerjaan di queue belum dijalankan. Ini menjelaskan mengapa worker harus aktif agar notifikasi asinkron dapat diproses.

Untuk memastikan penyebabnya, saya memeriksa tabel `jobs`. Jika ada job yang menunggu dan tidak ada worker yang berjalan, itulah petunjuk utamanya. Setelah pengujian, saya menjalankan kembali worker dengan `php artisan queue:work`.

### 2. Hapus implements ShouldQueue dari Listener, buat tugas untuk 40 mahasiswa

Pada pengujian ini, saya menghapus `implements ShouldQueue` dari Listener, lalu membuat tugas untuk mata kuliah yang memiliki banyak mahasiswa, misalnya 40 orang. Tanpa `ShouldQueue`, Listener dijalankan langsung dalam request yang memicu Event, bukan dititipkan ke queue sebagai job terpisah.

Akibatnya, proses pengiriman notifikasi dapat memperlambat respons aplikasi, terutama jika setiap pengiriman membutuhkan waktu untuk mengakses database atau layanan email. Dosen bisa harus menunggu sampai seluruh proses selesai sebelum halaman kembali memberikan respons.

Saya membandingkan waktu respons dengan versi Listener yang memakai `ShouldQueue`. Perbedaan waktunya bergantung pada jumlah penerima dan pekerjaan yang dilakukan, sehingga angka durasi perlu diukur dari hasil percobaan, bukan diasumsikan terlebih dahulu.

Setelah pengujian, `implements ShouldQueue` perlu dikembalikan agar pekerjaan notifikasi dijalankan oleh worker secara asinkron.

### 3. Ganti QUEUE_CONNECTION=sync, ulangi nomor 2

Untuk percobaan ini, saya mengubah konfigurasi menjadi:

```env
QUEUE_CONNECTION=sync
```

Lalu saya mengulangi pembuatan tugas seperti pada pengujian sebelumnya. Dengan driver `sync`, job dijalankan langsung dalam proses request, sehingga tidak menunggu worker dan tidak menumpuk di tabel `jobs` seperti pada driver `database`.

Saya membandingkan hasilnya dengan dua pengujian sebelumnya. Pada `database` tanpa worker, job menunggu dan notifikasi tidak diproses. Pada `sync`, pekerjaan langsung dijalankan sehingga notifikasi dapat diproses tanpa worker, tetapi request dapat menjadi lambat. Jika Listener tidak memakai `ShouldQueue`, prosesnya juga tetap berlangsung langsung dalam request.

Driver `sync` berguna untuk pengembangan atau pengujian sederhana, tetapi tidak memberikan manfaat pemrosesan asinkron yang dibutuhkan ketika pekerjaan notifikasi cukup berat. Setelah selesai, saya mengembalikan `QUEUE_CONNECTION=database` sesuai kebutuhan proyek dan memastikan worker dijalankan.

### 4. Buat Notification melempar exception, jalankan worker

Untuk melihat penanganan job gagal, saya membuat Notification uji yang melempar exception saat diproses. Pengujian ini dilakukan pada kode lokal dan tidak menggunakan notifikasi produksi.

Jika job gagal, Laravel akan mencoba menjalankannya kembali sesuai pengaturan percobaan. Contohnya, job dapat diberi konfigurasi berikut:

```php
public int $tries = 3;
public int $backoff = 10;
```

Artinya, job dapat dicoba sampai tiga kali dengan jeda yang ditentukan oleh `backoff`. Setelah semua percobaan gagal, job seharusnya tercatat pada tabel `failed_jobs`, selama konfigurasi queue dan migrasi tabelnya benar.

Saya mengamati keluaran terminal worker, lalu memeriksa tabel `failed_jobs` atau menjalankan:

```bash
php artisan queue:failed
```

Jika job tidak tercatat sebagai gagal, saya perlu memeriksa apakah exception benar-benar terjadi, apakah jumlah percobaan sudah habis, dan apakah worker menggunakan koneksi database yang sesuai. Setelah pengujian, exception buatan harus dihapus.

### 5. Hentikan worker di tengah pemrosesan job

Pada pengujian ini, saya menjalankan worker lalu menghentikannya ketika job sedang diproses. Tujuannya untuk melihat apakah job kembali ke queue atau hilang.

Hasilnya bergantung pada kapan worker dihentikan dan konfigurasi timeout serta `retry_after`. Laravel menggunakan mekanisme reservasi job; jika proses berhenti sebelum job selesai, job dapat tersedia kembali setelah masa reservasinya berakhir. Namun, jika proses sudah melakukan sebagian pekerjaan sebelum berhenti, pekerjaan yang sama mungkin dicoba ulang dan berpotensi menghasilkan efek ganda jika tidak dirancang dengan aman.

Karena itu, saya tidak bisa menyimpulkan bahwa job pasti langsung kembali atau pasti hilang tanpa melihat keadaan tabel dan konfigurasi aktual. Saya perlu memeriksa tabel `jobs`, keluaran worker, serta apakah notifikasi sudah sempat masuk ke tabel `notifications`.

Pengujian ini menunjukkan bahwa job asinkron perlu dirancang agar aman ketika dicoba ulang. Setelah selesai, saya memastikan worker berjalan kembali dan memeriksa apakah ada notifikasi yang terkirim ganda.

### 6. Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel notifications sebagai admin

Untuk memperagakan risiko ini, saya menambahkan nilai mahasiswa ke payload notifikasi uji, lalu memeriksa kolom `data` di tabel `notifications`. Pengujian hanya dilakukan dengan data buatan atau data uji yang aman.

Jika nilai muncul dalam kolom `data`, berarti informasi tersebut tersimpan permanen di database sebagai bagian dari notifikasi. Pihak yang memiliki akses langsung ke tabel bisa melihat informasi itu, walaupun informasi tersebut sebetulnya cukup ditampilkan melalui halaman nilai yang memiliki pemeriksaan akses.

Perbaikannya adalah menghapus nilai dan feedback dari payload notifikasi. Payload cukup berisi informasi yang diperlukan untuk mengarahkan mahasiswa ke halaman yang tepat, misalnya ID submission atau judul tugas. Saat mahasiswa membuka halaman nilai, aplikasi harus memeriksa bahwa pengguna tersebut memang pemilik submission atau memiliki peran yang diizinkan.

### 7. Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain

Untuk pengujian ini, saya menggunakan dua akun mahasiswa uji yang memiliki notifikasi berbeda. Saya login sebagai akun B, kemudian mencoba memanggil endpoint tandai-dibaca dengan ID notifikasi milik akun A.

Endpoint yang aman harus memeriksa kepemilikan notifikasi di sisi server. Jika endpoint hanya mencari notifikasi berdasarkan ID tanpa memastikan pemiliknya adalah pengguna yang sedang login, akun B mungkin dapat menandai notifikasi milik A sebagai sudah dibaca. Ini merupakan celah otorisasi atau IDOR.

Perbaikannya adalah mengambil notifikasi melalui relasi pengguna yang sedang login, atau memeriksa kepemilikan sebelum memperbarui status. Contoh pola dengan relasi pengguna:

```php
$notification = $request->user()
    ->notifications()
    ->whereKey($id)
    ->firstOrFail();

$notification->markAsRead();
```

Dengan pola tersebut, pencarian dibatasi pada notifikasi milik pengguna yang login. Jika ID milik pengguna lain digunakan, sistem menghasilkan respon 404 Not Found karena notifikasi tidak ditemukan pada relasi akun tersebut.

Pada proyek ini, `NotificationController` dan `NotificationPolicy` mengamankannya menggunakan pemeriksaan kepemilikan via Gate:

```php
$notification = DatabaseNotification::findOrFail($id);
Gate::authorize('update', $notification);
$notification->markAsRead();
```

Jika ID notifikasi milik pengguna lain dipanggil, Laravel akan menolaknya dengan kode status HTTP **403 Forbidden** karena `notifiable_id` tidak cocok dengan ID pengguna yang sedang login.

### 8. Kalau memakai Reverb: ganti PrivateChannel menjadi Channel, langganan channel orang lain

Pengujian ini hanya dilakukan jika proyek menggunakan Laravel Reverb. Pada channel privat, pengguna harus melewati proses otorisasi sebelum dapat berlangganan. Contoh channel privat:

```php
new PrivateChannel('users.' . $this->userId)
```

Jika diganti menjadi channel publik biasa (`Channel`), siapa pun yang mengetahui nama channel dapat mencoba berlangganan tanpa pemeriksaan kepemilikan. Jika informasi notifikasi dikirim ke channel tersebut, pengguna lain berpotensi melihat data yang seharusnya hanya diterima pemiliknya.

Untuk mengujinya, saya menggunakan dua akun uji dan memeriksa apakah akun kedua bisa berlangganan channel milik akun pertama. Jika proyek belum menggunakan Reverb, bagian ini tidak perlu dijalankan; cukup jelaskan bahwa channel publik tidak cocok untuk pesan yang berisi notifikasi pribadi.

Perbaikannya adalah menggunakan `PrivateChannel` dan memastikan aturan otorisasi channel memeriksa bahwa pengguna yang sedang login berhak mengakses channel tersebut. Nama channel saja bukan pengamanan; pemeriksaan akses tetap harus dilakukan di server.

