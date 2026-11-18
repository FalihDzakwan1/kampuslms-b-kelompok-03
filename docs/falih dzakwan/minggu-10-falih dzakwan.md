#### Nama : Falih Dzakwan
#### NIM  : 10241028

---

# MINGGU 10 — Event, Listener, Queue Worker, dan Notifikasi

Catatan individu Minggu 10 ini mendokumentasikan penelusuran alur kerja sistem notifikasi asinkron (**READ**) dan pengujian delapan skenario kegagalan antrean serta keamanan notifikasi (**BREAK**) pada implementasi PR #57 di `kampuslms-b-kelompok-03`.

---

## READ — Ikuti Satu Job (30 Menit)

### 1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` — apa isinya?

Saat proses `php artisan queue:work` dimatikan, saya masuk sebagai **Dosen** dan mempublikasikan satu tugas baru berstatus `published` (tugas `draft` tidak memicu event `AssignmentPublished`).

* **Perilaku di Browser:** Proses simpan tugas langsung selesai dengan cepat tanpa loading lama karena proses penyimpanan ke database dipisahkan dari pengiriman notifikasi ke seluruh mahasiswa.
* **Isi Tabel `jobs`:** Muncul **1 baris baru** pada tabel `jobs` dengan kolom utama:
  * `queue`: `'default'`
  * `attempts`: `0` (belum pernah dicoba diproses oleh worker)
  * `reserved_at`: `NULL` (belum dikunci oleh worker mana pun)
  * `payload`: berisi data JSON serialisasi job untuk `App\Listeners\SendAssignmentNotification`.

Selama worker tidak dijalankan dan `.env` menggunakan `QUEUE_CONNECTION=database`, job tersebut akan terus bertahan di tabel `jobs`.

---

### 2. Baca kolom `payload`. Bisakah Anda mengenali nama Listener dan ID tugasnya?

Ya. Meskipun isi kolom `payload` berbentuk JSON panjang satu baris, jika diformat terlihat bagian-bagian berikut:
* Pada properti `"displayName"` dan `"data.commandName"`, tertulis jelas nama class listener yang bertugas: `App\Listeners\SendAssignmentNotification` (dibungkus oleh `Illuminate\Events\CallQueuedListener`).
* Pada properti `"data.command"`, Laravel melakukan serialisasi objek `App\Events\AssignmentPublished` beserta properti model `$assignment` (`SerializesModels`), sehingga kunci primer (ID tugas) dan class model `App\Models\Assignment` dapat dikenali dan dicocokkan dengan tabel `assignments`.

---

### 3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.

Ketika menjalankan perintah di terminal:

```bash
php artisan queue:work
```

Worker langsung mengambil antrean dari tabel `jobs` dan menampilkan log terminal:
1. `App\Listeners\SendAssignmentNotification` ... `RUNNING`
2. `App\Listeners\SendAssignmentNotification` ... `DONE`
3. Disusul oleh job notifikasi terpisah `App\Notifications\NewAssignmentNotification` (`RUNNING` → `DONE`) untuk tiap mahasiswa penerima karena class notifikasi juga mengimplementasikan `ShouldQueue`.

Setelah dicek kembali, **baris antrean di tabel `jobs` telah hilang (kosong)** karena job yang selesai dengan sukses otomatis dihapus oleh Laravel agar tidak dieksekusi ulang.

---

### 4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?

Setelah worker selesai memproses seluruh job, pada tabel `notifications` muncul baris baru yang jumlahnya **sama persis dengan jumlah mahasiswa terdaftar** pada tabel pivot `course_user` untuk mata kuliah tersebut.

Setiap baris memiliki:
* `type`: `App\Notifications\NewAssignmentNotification`
* `notifiable_type`: `App\Models\User`
* `notifiable_id`: ID masing-masing mahasiswa terdaftar
* `read_at`: `NULL` (menandakan belum dibaca oleh mahasiswa).

---

### 5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

Berdasarkan implementasi `app/Notifications/NewAssignmentNotification.php:38-49` dan `app/Notifications/GradeGivenNotification.php:42-56`, kolom `data` hanya menyimpan metadata yang aman:
* `type` (`assignment_published` / `grade_given`)
* `assignment_id` / `submission_id`
* `course_name`, `title`, `due_at` / `is_update`
* `url` relatif menuju halaman detail (`route(..., absolute: false)`).

**Evaluasi Keamanan Payload:**  
Kolom `data` di tabel `notifications` disimpan dalam format JSON teks polos (*plaintext*, tidak terenkripsi). Oleh karena itu, nilai angka (`score`) maupun komentar evaluasi dosen (`feedback`) **sengaja tidak dimasukkan** ke dalam payload notifikasi. Mahasiswa hanya menerima pemberitahuan dan diarahkan ke halaman submission yang diproteksi `SubmissionPolicy`.

---

## BREAK — Delapan Kerusakan (50 Menit)

| # | Yang Dicoba | Yang Diamati | Penjelasan Teknis & Bahaya Keamanannya |
|---|---|---|---|
| **1** | Set `QUEUE_CONNECTION=database`, matikan worker (`queue:work`), lalu publikasikan tugas baru. | Dosen melihat pesan sukses tugas tersimpan tanpa error, tetapi notifikasi tidak pernah muncul di akun mahasiswa dan job menumpuk di tabel `jobs`. | **Kegagalan Senyap (*Silent Failure*):** Aplikasi web tidak melempar error karena tugasnya memang hanya menaruh antrean ke database. Tanpa pemantauan proses worker (seperti Supervisor di produksi), seluruh notifikasi macet total tanpa disadari pengguna. |
| **2** | Hapus `implements ShouldQueue` dari `SendAssignmentNotification`, lalu buat tugas untuk kelas berisi 40 mahasiswa. | Request HTTP halaman simpan tertahan (*loading*) beberapa detik sampai proses pembuatan 40 notifikasi selesai seluruhnya sebelum redirect berjalan. | **Blocking Synchronous Request:** Pengguna dipaksa menunggu proses latar belakang selesai. Jika suatu saat notifikasi ditambah pengiriman email SMTP eksternal, halaman dosen bisa mengalami *timeout* HTTP 504 atau gagal total jika satu email error. |
| **3** | Ubah `QUEUE_CONNECTION=sync` pada `.env`, lalu ulangi percobaan membuat tugas untuk 40 mahasiswa. | Walaupun `ShouldQueue` terpasang, worker tidak dipakai dan seluruh pengiriman tetap berjalan sinkron di siklus request yang sama. | **Driver `sync` Membatalkan Asinkronisasi:** Driver `sync` sangat bermanfaat untuk automated testing (`NotificationQueueTest`) agar deterministik tanpa proses worker terpisah, namun berbahaya jika dibiarkan aktif di produksi karena mengembalikan beban *blocking* ke web server. |
| **4** | Lempar `\Exception` buatan di dalam `NewAssignmentNotification` lalu jalankan worker. | Worker mencoba mengeksekusi ulang sebanyak **`$tries = 3`** kali dengan jeda **`$backoff = 10`** detik sebelum akhirnya memindahkan job ke tabel **`failed_jobs`** dan memanggil method `failed()` untuk menulis log error (`NewAssignmentNotification.php:22-57`). | **Resiliensi & Dead-Letter Queue:** Mekanisme retry mencegah kegagalan sementara (seperti koneksi jaringan putus sesaat) langsung membuang notifikasi, sementara tabel `failed_jobs` menyimpan rekaman error agar developer dapat melakukan inspeksi dan menjalankan ulang (`php artisan queue:retry`). |
| **5** | Hentikan terminal worker (`Ctrl + C`) secara paksa tepat saat job sedang diproses. | Job yang belum selesai tidak hilang; setelah masa reservasi (`retry_after`) habis atau worker dinyalakan lagi, job kembali tersedia untuk diproses. | **Database Locking & At-Least-Once Delivery:** Kolom `reserved_at` menandai job yang sedang dikerjakan. Jika proses terhenti mendadak, sistem mengembalikan job ke antrean agar tidak hilang, namun kode job harus dijaga tetap aman bila terjadi eksekusi ulang. |
| **6** | Memasukkan `score` dan `feedback` ke array `toDatabase()` pada `GradeGivenNotification`, lalu memeriksa tabel `notifications`. | Angka nilai mahasiswa dan catatan pribadi dosen terbaca bebas dalam bentuk teks polos di kolom `data` database. | **Kebocoran Data Akademik Sensitif:** Siapa pun yang memiliki akses baca log/database dapat melihat nilai rahasia seluruh mahasiswa. Data sensitif tidak boleh disisipkan ke payload notifikasi dan wajib tetap berada di balik pengecekan Policy halaman detail. |
| **7** | Mengirim request `POST /notifications/{id}/read` menggunakan akun Mahasiswa A dengan memasukkan ID notifikasi milik Mahasiswa B. | Server menolak permintaan dan mengembalikan status error **403 Forbidden** (melalui `NotificationPolicy@update`). Status notifikasi Mahasiswa B tetap tidak berubah. | **Proteksi IDOR Notifikasi:** Pengecekan kepemilikan (`$notification->notifiable_id === $user->id`) memastikan pengguna tidak bisa membaca, membuka, ataupun menandai notifikasi milik akun lain sebagai sudah dibaca. |
| **8** | Jika menggunakan Laravel Reverb/WebSocket: mengubah `PrivateChannel` menjadi `Channel` publik lalu mendengarkan dari akun lain. | Pengguna lain yang membuka console browser dapat ikut *subscribe* ke channel publik dan mengintip pesan notifikasi akun lain secara realtime. | **Penyadapan Saluran Siaran (*Channel Eavesdropping*):** Saluran publik tidak memeriksa autentikasi `routes/channels.php`. Semua notifikasi berbasis pengguna wajib memakai `PrivateChannel` yang memverifikasi kecocokan `$user->id === $id`. |

---

## Kesimpulan

Pemisahan proses lambat (seperti penyiaran notifikasi ke banyak mahasiswa) menggunakan Event, Listener, dan Queue (`ShouldQueue`) menjaga respons web tetap cepat bagi pengguna. Agar andal dan aman di produksi, queue harus didukung worker aktif (Supervisor), konfigurasi retry (`$tries`, `$backoff`, `failed_jobs`), payload notifikasi yang bersih dari data nilai sensitif, serta otorisasi Policy pada setiap aksi pembacaan notifikasi.
