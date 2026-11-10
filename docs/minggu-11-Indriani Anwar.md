**Nama:** Indriani Anwar
**NIM:** 10241036

------------------------------------------------------------------------

## A. READ --- Ikuti Satu Job (30 Menit)

### 1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` --- apa isinya?

Saat worker dimatikan, saya membuat satu tugas baru dengan status
**published** melalui aplikasi. Tugas dengan status **draft** tidak
digunakan karena tidak memicu event notifikasi.

Tugas tetap tersimpan di database karena proses penyimpanan tugas
dilakukan oleh aplikasi, sedangkan pengiriman notifikasi dijalankan
melalui queue. Jika Event dan Listener sudah terhubung dengan benar
serta konfigurasi menggunakan `QUEUE_CONNECTION=database`, job
notifikasi seharusnya masuk ke tabel `jobs`.

Pada tabel `jobs`, saya memeriksa apakah muncul baris baru setelah tugas
dibuat. Baris tersebut berisi informasi job yang menunggu untuk
diproses, termasuk payload dan waktu job dimasukkan ke queue. Selama
worker belum dijalankan, job akan tetap menunggu di tabel tersebut.

Jika tabel `jobs` tidak bertambah, saya perlu memeriksa apakah Event
benar-benar dipanggil, Listener sudah terdaftar dan berjalan, serta
konfigurasi `.env` menggunakan `QUEUE_CONNECTION=database`. Tugas yang
berhasil tersimpan belum tentu berarti notifikasinya sudah dikirim.

### 2. Baca kolom `payload`. Bisakah Anda mengenali nama Listener dan ID tugasnya?

Kolom `payload` berisi data job dalam format JSON. Untuk membacanya,
saya membuka atau menyalin isi payload dari baris job yang baru dibuat.

Jika Listener yang digunakan adalah `SendAssignmentNotification`, nama
class tersebut seharusnya dapat dikenali pada informasi job. Data Event
atau model yang dikirim bersama job juga dapat membantu menemukan ID
tugas, bergantung pada cara job disusun dan diserialisasi oleh Laravel.

Nama Listener dan ID tugas tidak dapat dipastikan hanya dari contoh
kode. Keduanya harus diperiksa dari payload aktual, kemudian ID tugas
dicocokkan dengan data pada tabel `assignments`.

Jika payload tidak menunjukkan informasi yang diharapkan, saya perlu
memeriksa apakah Listener menggunakan `ShouldQueue`, apakah Event
mengirim objek tugas yang benar, dan apakah job yang dibaca memang
berasal dari proses pembuatan tugas yang sedang diuji.

### 3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.

Setelah memastikan terdapat job yang menunggu, saya menjalankan worker
melalui terminal:

``` bash
php artisan queue:work
```

Worker akan mengambil job yang tersedia dan mencoba menjalankannya. Jika
job berhasil diproses, baris job tersebut seharusnya dihapus dari tabel
`jobs`. Terminal juga menampilkan informasi mengenai job yang diproses.

Jika baris masih ada atau muncul pesan kegagalan, saya perlu membaca
keluaran terminal dan memeriksa apakah job sedang dicoba ulang atau
gagal diproses. Saya juga harus memastikan worker menggunakan
konfigurasi aplikasi dan koneksi database yang benar.

Setelah pengujian, worker dapat dihentikan menggunakan `Ctrl+C`. Jika
worker tidak dijalankan, job yang menggunakan queue database hanya akan
menumpuk dan notifikasi tidak diproses.

### 4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?

Setelah job diproses, saya memeriksa tabel `notifications` untuk
memastikan notifikasi benar-benar tersimpan. Jika satu tugas baru
dikirimkan kepada seluruh mahasiswa yang terdaftar pada mata kuliah
tersebut, jumlah notifikasi yang diharapkan harus sesuai dengan jumlah
mahasiswa yang memenuhi syarat sebagai penerima.

Sebagai contoh, jika terdapat 40 mahasiswa terdaftar dan seluruhnya
menjadi penerima notifikasi, seharusnya terdapat 40 baris notifikasi
untuk tugas tersebut. Namun, jumlah aktual harus dibandingkan dengan
data mahasiswa yang benar-benar terdaftar, bukan hanya angka perkiraan.

Jika jumlah baris lebih sedikit, saya perlu memeriksa apakah seluruh
mahasiswa sudah terambil sebagai penerima, apakah terdapat job yang
gagal, atau apakah Listener berhenti sebelum semua notifikasi dikirim.
Jika jumlahnya lebih banyak, saya perlu memeriksa kemungkinan Event
terpicu lebih dari sekali atau notifikasi terkirim berulang.

Jumlah aktual tidak dapat ditentukan hanya dari kode contoh. Karena itu,
saya membandingkan tabel `notifications` dengan daftar mahasiswa pada
mata kuliah yang diuji.

### 5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

Kolom `data` menyimpan isi notifikasi dalam format JSON. Untuk
notifikasi tugas baru, data yang diperlukan cukup berupa informasi untuk
mengenali tugas dan membuka halaman terkait, misalnya `assignment_id`,
`course_name`, `title`, `due_at`, dan `url`.

Payload notifikasi tidak seharusnya menyimpan informasi sensitif seperti
nilai mahasiswa atau feedback dari dosen. Informasi tersebut tidak perlu
dimasukkan ke tabel notifikasi karena mahasiswa dapat melihatnya melalui
halaman tugas atau halaman nilai yang sudah menerapkan pemeriksaan hak
akses.

Saya membuka salah satu baris notifikasi dan memeriksa kolom `data`.
Jika isinya hanya informasi tugas yang diperlukan, payload tersebut
sudah sesuai dengan tujuannya. Jika terdapat nilai atau feedback
mahasiswa, informasi tersebut perlu dihapus dari payload.

Dengan demikian, notifikasi berfungsi sebagai pemberitahuan dan pengarah
ke halaman terkait, bukan sebagai tempat penyimpanan informasi akademik
yang sensitif.

------------------------------------------------------------------------

## B. BREAK --- Delapan Kerusakan (50 Menit)

### 1. Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru.

Untuk pengujian ini, saya memastikan konfigurasi `.env` menggunakan:

``` env
QUEUE_CONNECTION=database
```

Kemudian saya tidak menjalankan `php artisan queue:work` dan membuat
satu tugas baru. Tugas seharusnya tetap tersimpan, tetapi job notifikasi
hanya masuk ke tabel `jobs` dan tidak diproses karena tidak ada worker
yang mengambilnya.

Hal yang saya amati adalah aplikasi tetap dapat terlihat normal dan
tidak selalu menampilkan pesan error. Notifikasi tidak sampai bukan
karena tugas gagal dibuat, melainkan karena pekerjaan di queue belum
dijalankan.

Untuk memastikan penyebabnya, saya memeriksa tabel `jobs`. Jika terdapat
job yang menunggu sementara tidak ada worker yang berjalan, hal tersebut
menjadi petunjuk utama. Setelah pengujian, worker dijalankan kembali
menggunakan `php artisan queue:work`.

### 2. Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 40 mahasiswa.

Pada pengujian ini, saya menghapus `implements ShouldQueue` dari
Listener, kemudian membuat tugas untuk mata kuliah yang memiliki banyak
mahasiswa, misalnya 40 orang.

Tanpa `ShouldQueue`, Listener dijalankan langsung dalam request yang
memicu Event, bukan dititipkan ke queue sebagai job terpisah. Akibatnya,
proses pengiriman notifikasi dapat memperlambat respons aplikasi,
terutama jika setiap pengiriman membutuhkan akses database atau layanan
email.

Dosen dapat harus menunggu sampai seluruh proses pengiriman selesai
sebelum halaman memberikan respons. Saya membandingkan waktu respons
antara Listener yang dijalankan langsung dan Listener yang menggunakan
`ShouldQueue`.

Perbedaan durasi harus diukur dari hasil percobaan karena bergantung
pada jumlah penerima dan pekerjaan yang dilakukan. Setelah pengujian,
`implements ShouldQueue` dikembalikan agar notifikasi diproses secara
asynchronous oleh worker.

### 3. Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2.

Untuk percobaan ini, saya mengubah konfigurasi menjadi:

``` env
QUEUE_CONNECTION=sync
```

Kemudian saya mengulangi pembuatan tugas seperti pada pengujian
sebelumnya. Dengan driver `sync`, job dijalankan langsung dalam proses
request. Oleh karena itu, job tidak menunggu worker dan tidak menumpuk
di tabel `jobs` seperti pada driver `database`.

Saya membandingkan hasilnya dengan dua pengujian sebelumnya:

-   **Database tanpa worker:** job menunggu di tabel `jobs`, sehingga
    notifikasi belum diproses.
-   **Sync:** job langsung dijalankan tanpa worker, tetapi proses
    request dapat menjadi lebih lambat.
-   **Listener tanpa `ShouldQueue`:** Listener tetap berjalan langsung
    dalam request.

Driver `sync` dapat digunakan untuk pengembangan atau pengujian
sederhana, tetapi tidak memberikan pemrosesan asynchronous yang
dibutuhkan ketika pekerjaan notifikasi cukup berat.

Setelah pengujian, saya mengembalikan `QUEUE_CONNECTION=database` sesuai
kebutuhan proyek dan memastikan worker dijalankan.

### 4. Buat Notification melempar exception, jalankan worker.

Untuk melihat penanganan job yang gagal, saya membuat Notification uji
yang melempar exception saat diproses. Pengujian ini dilakukan
menggunakan kode lokal dan tidak menggunakan notifikasi produksi.

Jika job gagal, Laravel dapat mencoba menjalankannya kembali sesuai
pengaturan percobaan. Contohnya, job dapat diberi konfigurasi:

``` php
public int $tries = 3;

public int $backoff = 10;
```

Artinya, job dapat dicoba sampai tiga kali dengan jeda yang ditentukan
oleh `backoff`. Setelah seluruh percobaan gagal, job seharusnya tercatat
pada tabel `failed_jobs`, selama konfigurasi queue dan migrasi tabelnya
sudah benar.

Saya mengamati keluaran terminal worker, lalu memeriksa tabel
`failed_jobs` atau menjalankan:

``` bash
php artisan queue:failed
```

Jika job tidak tercatat sebagai gagal, saya perlu memeriksa apakah
exception benar-benar terjadi, apakah jumlah percobaan sudah habis, dan
apakah worker menggunakan koneksi database yang sesuai. Setelah
pengujian, exception buatan harus dihapus.

### 5. Hentikan worker di tengah pemrosesan job.

Pada pengujian ini, saya menjalankan worker lalu menghentikannya ketika
job sedang diproses. Tujuannya adalah melihat apakah job kembali ke
queue atau justru hilang.

Hasilnya bergantung pada kapan worker dihentikan serta konfigurasi
`timeout` dan `retry_after`. Laravel menggunakan mekanisme reservasi
job. Jika proses berhenti sebelum job selesai, job dapat tersedia
kembali setelah masa reservasinya berakhir.

Namun, jika proses sudah melakukan sebagian pekerjaan sebelum berhenti,
pekerjaan yang sama mungkin dicoba ulang. Hal tersebut berpotensi
menghasilkan efek ganda jika proses tidak dirancang dengan aman.

Karena itu, saya tidak langsung menyimpulkan bahwa job pasti kembali
atau pasti hilang. Saya memeriksa tabel `jobs`, keluaran worker, dan
apakah notifikasi sudah sempat masuk ke tabel `notifications`.

Pengujian ini menunjukkan bahwa job asynchronous perlu dirancang agar
aman ketika dicoba ulang. Setelah pengujian, saya memastikan worker
berjalan kembali dan memeriksa kemungkinan notifikasi terkirim ganda.

### 6. Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel `notifications` sebagai admin.

Untuk memperagakan risiko ini, saya menambahkan nilai mahasiswa ke
payload notifikasi uji, kemudian memeriksa kolom `data` pada tabel
`notifications`. Pengujian hanya dilakukan menggunakan data buatan atau
data uji yang aman.

Jika nilai muncul pada kolom `data`, berarti informasi tersebut
tersimpan di database sebagai bagian dari notifikasi. Pihak yang
memiliki akses langsung ke tabel dapat melihat informasi tersebut,
meskipun sebenarnya nilai cukup ditampilkan melalui halaman yang sudah
memiliki pemeriksaan akses.

Perbaikannya adalah menghapus nilai dan feedback dari payload
notifikasi. Payload cukup berisi informasi yang diperlukan untuk
mengarahkan mahasiswa ke halaman terkait, misalnya ID submission atau
judul tugas.

Ketika mahasiswa membuka halaman nilai, aplikasi harus memeriksa apakah
pengguna tersebut memang pemilik submission atau memiliki peran yang
diizinkan.

### 7. Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain.

Untuk pengujian ini, saya menggunakan dua akun mahasiswa uji yang
memiliki notifikasi berbeda. Saya login sebagai akun B, kemudian mencoba
memanggil endpoint tandai-dibaca menggunakan ID notifikasi milik akun A.

Endpoint yang aman harus memeriksa kepemilikan notifikasi di sisi
server. Jika endpoint hanya mencari notifikasi berdasarkan ID tanpa
memastikan pemiliknya adalah pengguna yang sedang login, akun B mungkin
dapat menandai notifikasi milik A sebagai sudah dibaca. Kondisi tersebut
merupakan celah otorisasi atau IDOR (*Insecure Direct Object
Reference*).

Perbaikannya adalah mengambil notifikasi melalui relasi pengguna yang
sedang login atau memeriksa kepemilikan sebelum memperbarui status.
Contoh pola menggunakan relasi pengguna:

``` php
$notification = $request->user()
    ->notifications()
    ->whereKey($id)
    ->firstOrFail();

$notification->markAsRead();
```

Dengan pola tersebut, pencarian dibatasi pada notifikasi milik pengguna
yang sedang login. Jika ID milik pengguna lain digunakan, sistem
menghasilkan respons `404 Not Found` karena notifikasi tidak ditemukan
dalam relasi akun tersebut.

Pada proyek ini, `NotificationController` dan `NotificationPolicy`
menggunakan pemeriksaan kepemilikan melalui Gate:

``` php
$notification = DatabaseNotification::findOrFail($id);

Gate::authorize('update', $notification);

$notification->markAsRead();
```

Jika ID notifikasi milik pengguna lain dipanggil, Laravel menolaknya
dengan status HTTP **403 Forbidden** karena `notifiable_id` tidak cocok
dengan ID pengguna yang sedang login.

### 8. Kalau memakai Reverb: ganti `PrivateChannel` menjadi `Channel`, langganan channel orang lain.

Pengujian ini hanya dilakukan jika proyek menggunakan Laravel Reverb.
Pada channel privat, pengguna harus melewati proses otorisasi sebelum
dapat berlangganan. Contoh penggunaan channel privat:

``` php
new PrivateChannel('users.' . $this->userId)
```

Jika channel tersebut diganti menjadi channel publik biasa (`Channel`),
siapa pun yang mengetahui nama channel dapat mencoba berlangganan tanpa
pemeriksaan kepemilikan. Jika informasi notifikasi dikirim melalui
channel publik tersebut, pengguna lain berpotensi melihat data yang
seharusnya hanya diterima pemiliknya.

Untuk mengujinya, saya menggunakan dua akun uji dan memeriksa apakah
akun kedua dapat berlangganan channel milik akun pertama.

Jika proyek belum menggunakan Reverb, bagian ini tidak perlu dijalankan.
Saya cukup menjelaskan bahwa channel publik tidak cocok untuk pesan yang
berisi notifikasi pribadi.

Perbaikannya adalah menggunakan `PrivateChannel` dan memastikan aturan
otorisasi channel memeriksa bahwa pengguna yang sedang login memang
berhak mengakses channel tersebut. Nama channel saja bukan pengamanan;
pemeriksaan akses tetap harus dilakukan di server.

