### Nama : Elsya Nur Aulia Handayani

#### NIM : 10241026

---

# MINGGU 13 — Kolaborasi, Version Control, dan Peer Review

Laporan praktikum Minggu 13 ini mencakup tiga bagian utama sesuai modul pembelajaran:
1. **READ** : Analisis riwayat Git pribadi dan perbandingan kontribusi pada repositori `kampuslms-b-kelompok-03`.
2. **BREAK** : Pengamatan enam skenario keamanan version control dan proteksi kolaborasi tim.
3. **BUILD** : Pelaksanaan Peer Review kode secara formal terhadap Pull Request #59 milik rekan kelompok (Indriani Anwar), lengkap dengan tiga temuan multidimensi dan evaluasi tindak lanjutnya.

---

## BAGIAN 1: READ — Baca Riwayat Kelompok Anda (30 Menit)

Pemeriksaan riwayat repositori dilakukan menggunakan perintah Git bawaan pada terminal proyek:
```bash
git log --oneline --graph --all
git shortlog -sn --all
git log --pretty='%an' | sort | uniq -c
```

Berikut adalah jawaban reflektif berdasarkan data riwayat Git repositori `kampuslms-b-kelompok-03`:

### 1. Jumlah Commit Atas Nama Sendiri dan Perbandingannya dengan Anggota Lain

Hasil eksekusi `git shortlog -sn --all` pada seluruh branch repositori:

| Peringkat | Kontributor / Akun Git | Jumlah Commit | Persentase Kontribusi | Fokus Area Kontribusi Utama |
|:---:|---|:---:|:---:|---|
| 1 | `Indri6` (Indriani Anwar) | 127 | 34.8% | Seeder database idempotent, konfigurasi Railway, security headers, optimasi query performa |
| 2 | `FalihDzakwan1` (Falih Dzakwan) | 97 | 26.6% | Setup awal Laravel, sistem notifikasi queue & event, konfigurasi Dockerfile, workflow CI |
| 3 | `elsyanrr` / `Elsyanrr` (Elsya Nur Aulia Handayani) | 95 | 26.0% | Proteksi IDOR rute & controller, layout views, automasi deployment script, manajemen supervisor worker |
| 4 | `Fatika` / `Fatika Rizki Syahada` | 46 | 12.6% | Rekapitulasi nilai, migrasi indeks performa, pengujian otorisasi, dokumentasi audit produksi |
| **Total** | **Seluruh Anggota Kelompok 03** | **365** | **100.0%** | **Repositori LMS Kampus terintegrasi penuh** |

**Refleksi Kontribusi Pribadi:**  
Akun saya tercatat dengan dua variasi nama (`elsyanrr` sebanyak 77 commit dan `Elsyanrr` sebanyak 18 commit) dengan total akumulasi **95 commit**. Jumlah ini menempatkan saya pada posisi ketiga dengan selisih yang sangat tipis dari Falih (97 commit), menyumbang 26.0% dari keseluruhan riwayat commit repositori. Kontribusi saya mencakup implementasi fitur tugas dan pengumpulan, proteksi otorisasi controller, penulisan script deploy otomatis (`deploy.sh`), konfigurasi integrasi supervisor pada container Docker, serta dokumentasi mingguan.

### 2. Konsistensi Keaktifan Mingguan

Berdasarkan penelusuran tanggal commit (`git log --author="elsya" --date=short`):
* **Minggu 1 s.d. 3 (September 2026):** Aktif melakukan commit untuk inisialisasi lingkungan kerja, perancangan antarmuka awal, dan integrasi GitHub Actions CI.
* **Minggu 4 s.d. 7 (September - Oktober 2026):** Aktif mengimplementasikan rute bersarang, fitur upload berkas tugas, serta perbaikan celah keamanan IDOR.
* **Minggu 8 (Pertengahan Oktober 2026):** Terdapat jeda commit selama sekitar satu pekan (antara 11 Oktober hingga 20 Oktober 2026). Jeda ini terjadi karena seluruh anggota kelompok fokus pada persiapan dan pelaksanaan Ujian Tengah Semester (UTS) tertulis.
* **Minggu 9 s.d. 13 (Akhir Oktober - November 2026):** Aktivitas commit kembali berjalan intensif setiap pekannya untuk menyelesaikan fitur notifikasi antrean, mitigasi N+1 query, optimasi seeder, persiapan deployment container produksi, hingga pelaksanaan peer review saat ini.

### 3. Persentase Kepatuhan Format Conventional Commits

Dari total 95 commit yang saya buat:
* **Mengikuti format Conventional Commits:** 62 commit (~65.3%)
* **Pesan non-standar / informal:** 33 commit (~34.7%)

**Analisis Evaluatif:**  
Pada awal semester (Minggu 1 sampai Minggu 4), pesan commit yang saya gunakan masih bersifat informal dan belum berpola (contohnya `commit`, `minggu_01_elsya`, `uptade minggu 02`, `update css`). Namun, setelah standar kelompok disepakati pada Minggu 5, saya mulai konsisten menerapkan format Conventional Commits dengan prefix yang jelas seperti `feat:`, `fix:`, `docs:`, `refactor:`, dan `test:`.

### 4. Evaluasi Pesan Commit Terburuk dan Penulisan Ulang

* **Pesan commit terburuk yang ditemukan:**  
  Hash: `502df21`  
  Pesan commit: `commit` (dibuat pada 1 September 2026).
* **Alasan buruk:**  
  Pesan tersebut sama sekali tidak memberikan informasi mengenai perubahan apa yang dilakukan, file apa yang terpengaruh, atau apa konteks penambahan berkas tersebut dalam sistem.
* **Penulisan ulang yang seharusnya:**  
  ```text
  docs(minggu-01): inisialisasi catatan praktikum lingkungan kerja laravel dan instalasi git
  ```

### 5. Evaluasi Commit Terbesar (`git log --stat`)

* **Commit terbesar yang teridentifikasi:**  
  Hash: `793a4d3`  
  Pesan commit: `feat(deploy): implementasi deploy.sh dan konfigurasi supervisor untuk M4`  
  Statistik perubahan: 5 berkas, 218 baris penambahan (`+`), 1 baris pengurangan (`-`).  
  Daftar berkas: `README.md`, `deploy.sh`, `docs/deployment.md`, `docs/image/supervisorctl_status.png`, `supervisord.conf`.
* **Analisis pemecahan:**  
  Commit ini seharusnya dipecah menjadi dua commit terpisah karena mencampurkan berkas konfigurasi infrastruktur server yang dapat dieksekusi dengan berkas dokumentasi markdown dan aset gambar:
  1. `feat(deploy): tambah skrip deploy otomatis deploy.sh dan konfigurasi supervisord` (khusus berkas `deploy.sh` dan `supervisord.conf`).
  2. `docs(deploy): susun panduan deployment produksi dan bukti status supervisorctl` (khusus berkas `docs/deployment.md`, `README.md`, dan tangkapan layar status).

---

## BAGIAN 2: BREAK — Enam Percobaan Version Control & Kolaborasi (40 Menit)

Pengujian skenario kegagalan ini dijalankan pada branch percobaan khusus (`percobaan-vc`) guna menguji ketahanan repositori dan memahami mekanisme proteksi Git.

| No. | Skenario Percobaan | Hasil Pengamatan pada Git / CI / GitHub | Pelajaran Teknis & Tindakan Mitigasi |
|:---:|---|---|---|
| **1** | Membuat berkas `.env` dengan kredensial palsu lalu mencoba melakukan `git push` | Pipeline GitHub Actions CI mendeteksi keberadaan berkas `.env` dan langsung menggagalkan build pada tahap validasi repositori (*lint/security check*). | Berkas `.env` wajib dimasukkan ke dalam `.gitignore` sejak awal proyek. Kredensial tidak boleh masuk ke repositori publik karena bot pemindai dapat menyalin rahasia dalam hitungan menit. |
| **2** | Menghapus berkas `.env` dari commit terbaru menggunakan perintah `git rm --cached .env` lalu commit ulang | Berkas fisik `.env` memang terhapus dari snapshot HEAD (branch aktif), namun rekaman berkas dan isinya tetap tersimpan utuh di dalam riwayat commit sebelumnya (`git log`). Siapa pun dapat melihat isinya dengan memeriksa commit lama. | Sekali berkas rahasia ter-push ke remote publik, menghapusnya di commit terbaru tidak cukup. Rahasia tersebut harus dianggap telah bocor: kata sandi database wajib diganti dan `APP_KEY` harus dibuat ulang (`php artisan key:generate`). |
| **3** | Dua pengembang mengedit baris yang sama pada `routes/web.php` secara bersamaan, lalu dilakukan operasi `merge` | Git menghentikan proses merge secara otomatis dan memunculkan status `CONFLICT (content): Merge conflict in routes/web.php`. Berkas ditandai dengan conflict marker (`<<<<<<<`, `=======`, `>>>>>>>`). | Konflik tidak dapat diselesaikan otomatis oleh sistem. Kedua pengembang harus berdiskusi untuk menentukan baris rute yang benar, menghapus marker manual, lalu menjalankan `git add` dan `git commit` untuk menyelesaikan merge. |
| **4** | Mencoba melakukan push langsung ke branch utama (`git push origin main`) tanpa melalui Pull Request | Perintah push ditolak mentah-mentah oleh server GitHub dengan pesan error: `remote: error: GH006: Protected branch hook declined. Changes must be made through a pull request.` | Fitur *Branch Protection Rules* pada GitHub berhasil mencegah perubahan langsung pada branch produksi tanpa proses peninjauan kode (*peer review*) dan verifikasi kelulusan tes otomatis. |
| **5** | Membuka halaman Pull Request yang status pengujian CI-nya gagal (*failing check*), lalu mencoba menekan tombol merge | Tombol tombol **Merge pull request** pada antarmuka web GitHub berubah menjadi abu-abu dan terkunci (*blocked*), menampilkan peringatan bahwa syarat kelulusan test belum terpenuhi. | Pengaturan branch protection yang mewajibkan status check sukses terbukti efektif menghentikan kode yang rusak (*broken build*) masuk ke branch utama sistem. |
| **6** | Membuat commit dengan memanipulasi identitas pengarang menggunakan perintah `git commit --author="Nama Rekan <rekan@mail.com>"` | Commit berhasil dibuat dan tercatat di `git log` serta `git shortlog` seolah-olah dibuat oleh rekan tersebut, padahal akun lokal yang mengeksekusi berbeda. | Kepenulisan pada riwayat Git lokal dapat dipalsukan jika tidak diproteksi dengan tanda tangan kriptografi (GPG signed commit). Itulah sebabnya penilaian kontribusi riwayat Git tidak dapat hanya berpatokan pada angka numerik `git shortlog`, melainkan harus dikonfirmasi melalui wawancara dan penguasaan kode. |

---

## BAGIAN 3: BUILD — Laporan Peer Review Formal (Pull Request #59)

Pemeriksaan peer review dilakukan terhadap Pull Request yang diajukan oleh **Indriani Anwar** (`Indri6`) pada branch `Indri`.

```
                   ROTASI PEER REVIEW KELOMPOK 03
+------------------------------------------------------------------+
| Falih Dzakwan (PR #57) ---> Direview oleh Fatika Rizki Syahada   |
| Fatika Rizki (PR #62)  ---> Direview oleh Indriani Anwar         |
| Indriani Anwar (PR #59)---> Direview oleh Elsya Nur Aulia H      |
| Elsya Nur Aulia (PR #80)---> Direview oleh Falih Dzakwan         |
+------------------------------------------------------------------+
```

---

# PEER REVIEW — Pull Request #59

## 1. Informasi Pull Request

| Keterangan | Informasi Rinci |
|---|---|
| **Repository** | `FalihDzakwan1/kampuslms-b-kelompok-03` |
| **Pull Request** | `#59` |
| **Author (Pembuat PR)** | Indriani Anwar (`Indri6`) |
| **Reviewer (Pemeriksa)** | Elsya Nur Aulia Handayani (`elsyanrr` / NIM: 10241026) |
| **Branch Sumber** | `Indri` |
| **Branch Tujuan** | `main` |
| **Fokus Perubahan** | Antarmuka daftar notifikasi, alur penilaian tugas dosen-mahasiswa, dan proteksi kepemilikan notifikasi |

**Judul Perubahan yang Diperiksa:**  
`feat(notifikasi & alur): implementasi halaman notifikasi dengan pemeriksaan kepemilikan dan alur penilaian tugas`

---

## 2. Berkas yang Diperiksa

| No. | Berkas Sumber | Cakupan Perubahan |
|---:|---|---|
| 1 | `app/Http/Controllers/NotificationController.php` | Menambahkan method `index()`, `markAsRead()`, dan `open()` untuk pengelolaan notifikasi web. |
| 2 | `app/Policies/NotificationPolicy.php` | Membuat policy baru untuk memvalidasi kepemilikan record notifikasi berdasarkan `notifiable_id` dan `notifiable_type`. |
| 3 | `resources/views/notifications/index.blade.php` | Membuat halaman tampilan daftar notifikasi lengkap dengan filter status dan tombol aksi. |
| 4 | `routes/web.php` | Mendaftarkan rute web untuk mengakses halaman notifikasi dan mutasi status dibaca. |

---

## 3. Hasil Pemeriksaan Kode

Pemeriksaan dilakukan secara obyektif berdasarkan diff perubahan kode pada Pull Request #59. Penilaian berfokus pada kualitas teknis kode, bukan menyerang individu penulis kode.

```
+-------------------------------------------------------------------------+
|                  RINGKASAN DIMENSI TEMUAN REVIEW                        |
+-------------------------------------------------------------------------+
|  A. Keamanan : Mutasi state via HTTP GET pada rute open notifikasi      |
|  B. Performa : Looping collection pada 'tandai semua dibaca' (N+1 query)|
|  C. Struktur : Duplikasi logika pemformatan data di Blade dan Controller|
+-------------------------------------------------------------------------+
```

### A. Keamanan — Mutasi State Menggunakan Method HTTP GET pada Rute Buka Notifikasi

**Lokasi Pemeriksaan :**  
* `routes/web.php:97`
* `app/Http/Controllers/NotificationController.php:87-103` (method `open()`)

**Kode yang Diperiksa :**
```php
// routes/web.php baris 97
Route::get('/notifications/{id}/open', [\App\Http\Controllers\NotificationController::class, 'open'])->name('notifications.open');

// app/Http/Controllers/NotificationController.php baris 87-103
public function open(Request $request, string $id)
{
    $notification = DatabaseNotification::findOrFail($id);

    // Pemeriksaan kepemilikan via Policy / Gate
    Gate::authorize('update', $notification);

    $notification->markAsRead();

    $targetUrl = $notification->data['url'] ?? route('dashboard');

    return redirect($targetUrl);
}
```

**Temuan :**  
Method `open()` didaftarkan pada rute HTTP berjenis `GET` (`Route::get('/notifications/{id}/open')`). Di dalam controller, method ini melakukan operasi mutasi data ke database dengan mengeksekusi `$notification->markAsRead()` sebelum mengarahkan pengguna ke `$targetUrl`.

**Risiko :**
1. Menurut standar arsitektur REST, method HTTP `GET` harus bersifat idempoten dan aman (*safe method*), yaitu tidak boleh mengubah state data pada server. Penggunaan `GET` untuk mengubah status berisiko terpanggil secara otomatis oleh fitur *browser prefetching*, akselerator unduhan, atau robot peramban (*link crawler*), yang dapat menyebabkan notifikasi ditandai telah dibaca padahal pengguna belum secara sadar membukanya.
2. Variabel `$targetUrl` diambil langsung dari array data notifikasi (`$notification->data['url']`). Jika tidak ada validasi bahwa target URL merupakan alamat internal atau berupa *relative path*, terdapat potensi risiko *Open Redirect* apabila payload data notifikasi di database termanipulasi.

**Saran Perbaikan :**  
1. Untuk mutasi status data, sebaiknya gunakan method HTTP `POST` melalui form submission atau panggilan AJAX. Jika tetap dipertahankan menggunakan `GET` demi kemudahan navigasi langsung saat diklik, berikan penjelasan desain terkait kompromi tersebut.
2. Tambahkan pengecekan keamanan pada URL tujuan untuk memastikan bahwa pengalihan hanya mengarah ke rute aplikasi internal (misalnya memastikan URL diawali tanda `/` atau mencocokkan host domain aplikasi).

---

### B. Performa — Penanganan Operasi Tandai Semua Dibaca Memuat Seluruh Record ke Memori

**Lokasi Pemeriksaan :**  
`app/Http/Controllers/NotificationController.php:73-76` (method `markAsRead()` saat memproses seluruh notifikasi).

**Kode yang Diperiksa :**
```php
public function markAsRead(Request $request, ?string $id = null)
{
    $user = $request->user();

    if ($id && $id !== 'all') {
        $notification = DatabaseNotification::findOrFail($id);
        Gate::authorize('update', $notification);
        $notification->markAsRead();
        $message = 'Notifikasi berhasil ditandai sebagai telah dibaca.';
    } else {
        // [Titik Kritis Performa]
        $user->unreadNotifications->markAsRead();
        $message = 'Semua notifikasi berhasil ditandai sebagai telah dibaca.';
    }

    // ... response handling
}
```

**Temuan :**  
Pada baris `$user->unreadNotifications->markAsRead()`, relasi diakses sebagai *dynamic property* tanpa tanda kurung method (`unreadNotifications` bukan `unreadNotifications()`). Sintaks ini menyebabkan Laravel mengeksekusi query `SELECT * FROM notifications WHERE notifiable_id = ? AND read_at IS NULL` dan memuat seluruh instans model notifikasi ke dalam memori PHP sebagai *Eloquent Collection*. Setelah itu, collection mengeksekusi perintah SQL `UPDATE` satu per satu untuk setiap baris notifikasi.

**Risiko :**  
Ketika seorang dosen atau mahasiswa memiliki jumlah notifikasi yang banyak (misalnya 100 s.d. 500 notifikasi yang belum dibaca), kode ini akan membebani memori PHP server (*memory spike*) dan memicu ratusan query write ke basis data secara berulang (*N-queries on bulk update*), yang memperlambat waktu respon request secara signifikan.

**Saran Perbaikan :**  
Ubah pemanggilan menjadi *Query Builder* langsung pada relasi dengan memanggil method berpenutup tanda kurung:
```php
$user->unreadNotifications()->update(['read_at' => now()]);
```
Dengan sintaks ini, Laravel hanya akan mengeksekusi **satu perintah SQL tunggal** langsung pada engine basis data (`UPDATE notifications SET read_at = ? WHERE notifiable_id = ? AND read_at IS NULL`), tanpa perlu memuat satu pun model ke dalam alokasi memori PHP.

---

### C. Struktur dan Keterbacaan — Duplikasi Logika Klasifikasi Tipe Notifikasi pada View dan Controller

**Lokasi Pemeriksaan :**  
* `resources/views/notifications/index.blade.php:48-95`
* `app/Http/Controllers/NotificationController.php:33-56` (method `poll()`)

**Kode yang Diperiksa pada View :**
```blade
@foreach($notifications as $n)
    @php
        $isUnread = is_null($n->read_at);
        $type = $n->data['type'] ?? 'general';
        $courseName = $n->data['course_name'] ?? null;
        $title = $n->data['title'] ?? 'Pemberitahuan';
        $isUpdate = !empty($n->data['is_update']);
    @endphp

    {{-- Penentuan styling dan icon berdasarkan tipe berulang-ulang --}}
    @if($type === 'grade_given')
        bg-slate-50 text-slate-700 border border-slate-200
    @elseif($type === 'assignment_published')
        bg-sky-50 text-sky-700 border border-sky-200
    @endif
    ...
    @if($type === 'grade_given')
        🎯
    @elseif($type === 'assignment_published')
        📝
    @endif
@endforeach
```

**Temuan :**  
Di dalam file Blade `index.blade.php`, terdapat blok pengkondisian `@if($type === ...)` yang berulang beberapa kali untuk menentukan ikon emoji, warna border, dan teks badge. Di sisi lain, pada method `poll()` di `NotificationController.php`, terdapat array transformasi yang juga melakukan pemetaan serupa untuk keperluan JSON polling navbar. Logika representasi tampilan ini tersebar di dua tempat yang berbeda.

**Risiko :**  
Jika di kemudian hari sistem menambah jenis notifikasi baru (misalnya pengumuman materi perkuliahan baru atau pengingat batas waktu pengumpulan), pengembang harus mengubah kode di beberapa file sekaligus. Hal ini memperbesar peluang terjadinya inkonsistensi antarmuka apabila salah satu berkas terlewat diperbarui.

**Saran Perbaikan :**  
Pertimbangkan untuk memusatkan logika representasi visual ini pada accessor model khusus atau Presenter class (misalnya menambahkan helper method `icon()`, `badgeColor()`, dan `label()` pada class notifikasi atau custom model `DatabaseNotification`). Dengan cara ini, kode pada template Blade akan menjadi jauh lebih ringkas dan mudah dipelihara.

---

## 4. Ringkasan Temuan Review

| No. | Dimensi Evaluasi | Temuan Spesifik | Rekomendasi Solusi |
|:---:|---|---|---|
| **1** | **Keamanan** | Method `open()` memutasi status notifikasi melalui HTTP method `GET` dan mengalihkan URL tanpa validasi internal domain. | Pertimbangkan pemisahan aksi mutasi via POST/AJAX dan pastikan URL tujuan dibatasi hanya pada rute lokal aplikasi. |
| **2** | **Performa** | Operasi 'tandai semua dibaca' memuat seluruh koleksi model ke memori PHP (`$user->unreadNotifications`), menimbulkan perulangan query UPDATE per record. | Ganti dengan Query Builder `$user->unreadNotifications()->update(['read_at' => now()])` agar dieksekusi dalam 1 query SQL atomic. |
| **3** | **Struktur / Keterbacaan** | Percabangan penentuan ikon dan badge notifikasi diulang-ulang di Blade dan menduplikasi struktur transformasi di controller `poll()`. | Satukan pemetaan tipe notifikasi ke dalam accessor model atau Presenter class agar terpusat dan mudah ditambah. |

---

## 5. Komentar untuk Pull Request GitHub

Teks komentar berikut disiapkan untuk disalin langsung ke kolom review Pull Request #59 di antarmuka GitHub:

### Komentar 1 — Keamanan (Rute Buka Notifikasi & Pengalihan URL)
> **File:** `routes/web.php:97` & `app/Http/Controllers/NotificationController.php:87-103`  
> Halo @Indri6, terima kasih atas implementasi halaman notifikasinya. Pada method `open()`, saya melihat adanya eksekusi `$notification->markAsRead()` yang mengubah state database melalui method HTTP `GET` (`Route::get('/notifications/{id}/open')`). Secara kaidah web, method GET sebaiknya idempoten agar tidak memicu perubahan data yang tidak sengaja saat browser melakukan prefetching atau link crawling. Selain itu, `$targetUrl = $notification->data['url']` diarahkan langsung tanpa validasi internal host. Apakah ada pertimbangan khusus untuk mempertahankan method GET ini, atau sebaiknya mutasi status dipisahkan via POST/AJAX dan target URL divalidasi sebagai internal route?

### Komentar 2 — Performa (Peningkatan Efisiensi Bulk Update Notifikasi)
> **File:** `app/Http/Controllers/NotificationController.php:73-76`  
> Pada penanganan aksi 'tandai semua dibaca', kode memanggil `$user->unreadNotifications->markAsRead()`. Karena dipanggil sebagai dynamic property (tanpa kurung), Laravel akan memuat seluruh data notifikasi yang belum dibaca ke dalam memori PHP sebagai Eloquent Collection sebelum melakukan loop UPDATE per baris. Jika seorang pengguna memiliki ratusan notifikasi, ini akan memakan memori dan memicu banyak query SQL. Sebaiknya pemanggilan diubah ke Query Builder langsung: `$user->unreadNotifications()->update(['read_at' => now()])`. Pendekatan ini akan mengeksekusi 1 query SQL atomic dan tidak memakan memori PHP sama sekali.

### Komentar 3 — Struktur dan Keterbacaan (Sentralisasi Logika Presentasi Notifikasi)
> **File:** `resources/views/notifications/index.blade.php:48-95` & `app/Http/Controllers/NotificationController.php:33-56`  
> Logika pengecekan `@if($type === 'grade_given') ... @elseif($type === 'assignment_published') ...` saat ini ditulis berulang di file Blade untuk menentukan ikon, warna border, dan badge, serta memiliki kemiripan format dengan method `poll()` di controller. Agar kode view lebih bersih dan penambahan jenis notifikasi baru di masa mendatang tidak menuntut modifikasi di banyak tempat, pertimbangkan untuk memindahkan logika mapping ini ke accessor model atau Presenter class tersendiri.

---

## 6. Tindak Lanjut Review

Berdasarkan ketentuan penugasan peer review, setiap temuan wajib ditanggapi oleh pembuat PR (**diterima dan diperbaiki**, atau **ditolak dengan alasan teknis yang sah dan bernilai penuh**). Minimal satu temuan harus benar-benar diselesaikan melalui commit perbaikan baru dengan awalan `fix:`.

| No. | Aspek Temuan | Tanggapan Author (Indriani Anwar) | Status Keputusan | Bukti Tindak Lanjut / Catatan Teknis |
|:---:|---|---|:---:|---|
| **1** | **Keamanan**<br>(Mutasi state via HTTP GET dan proteksi URL redirect) | *"Temuan ditolak dengan penjelasan teknis: Rute `/notifications/{id}/open` sengaja dirancang menggunakan method GET agar pengguna dapat membuka link notifikasi secara mulus langsung dari tab baru atau link eksternal email tanpa terhalang mekanisme form submission (faktor kemudahan navigasi UX). Akses ini sudah diproteksi secara ketat menggunakan `Gate::authorize('update', $notification)` pada baris 93, sehingga pengguna lain mustahil memanipulasi status notifikasi orang lain. Terkait risiko Open Redirect, seluruh isi payload `$notification->data['url']` digenerate secara internal oleh event/listener Laravel resmi (`GradeGivenNotification` dan `AssignmentPublishedNotification`), bukan berasal dari input form publik yang dapat diinjeksi pengguna luar. Oleh karena itu, arsitektur saat ini dipertahankan demi menjaga kenyamanan pengalaman pengguna."* | **Ditolak dengan Alasan yang Sah** | Proteksi Policy `NotificationPolicy@update` sudah aktif membatasi hak akses. Struktur internal payload aman dari input injection liar. |
| **2** | **Performa**<br>(Looping collection pada penandaan massal notifikasi) | *"Temuan diterima: Catatan ini sangat tepat dan bermanfaat. Mengakses `$user->unreadNotifications` memang memicu query SELECT untuk memuat seluruh objek ke dalam memori PHP sebelum mengeksekusi update baris per baris. Kami sepakat untuk mengubah pemanggilan tersebut menjadi pemanggilan Query Builder `$user->unreadNotifications()->update(['read_at' => now()])` agar proses pembaruan berjalan secara atomic dalam satu perintah SQL langsung di database engine."* | **Diterima dan Diperbaiki** | **Telah diperbaiki** melalui commit perbaikan baru: `fix(notifikasi): optimasi query tandai semua dibaca menggunakan atomic update query builder`. |
| **3** | **Struktur / Keterbacaan**<br>(Refactor pengkondisian tipe notifikasi ke Presenter Class) | *"Temuan ditolak dengan penjelasan teknis: Saat ini sistem LMS hanya mengelola dua jenis notifikasi spesifik, yaitu pengumuman tugas baru dan nilai yang keluar. Pengkondisian Blade yang ada saat ini dinilai masih sangat ringkas dan mudah dibaca langsung oleh tim tanpa harus membuka layer abstraksi baru (menerapkan prinsip YAGNI - You Aren't Gonna Need It dan KISS - Keep It Simple, Stupid). Membuat Custom Model atau Presenter Class pada tahap ini dinilai over-engineering untuk sistem dengan variasi data yang masih minim. Refactoring akan kami jadwalkan apabila sistem nantinya mendukung lebih dari lima tipe notifikasi baru."* | **Ditolak dengan Alasan yang Sah** | Menghindari abstraksi berlebih (*over-engineering*) pada skala aplikasi saat ini. Keputusan arsitektur logis dan beralasan kuat. |

---

### Bukti Commit Perbaikan (`fix:`)

Untuk memenuhi persyaratan bahwa minimal satu temuan benar-benar diselesaikan dalam kode repositori, temuan nomor 2 (**Performa**) telah diselesaikan melalui commit perbaikan berikut:

* **Hash Commit :** `6a40a86` / commit perbaikan controller notifikasi
* **Pesan Commit :** `fix(notifikasi): optimasi query tandai semua dibaca menggunakan atomic update query builder`
* **Berkas yang Diperbaiki :** `app/Http/Controllers/NotificationController.php`
* **Rincian Perubahan Kode :**
  ```diff
  --- a/app/Http/Controllers/NotificationController.php
  +++ b/app/Http/Controllers/NotificationController.php
  @@ -72,7 +72,7 @@ public function markAsRead(Request $request, ?string $id = null)
               $notification->markAsRead();
               $message = 'Notifikasi berhasil ditandai sebagai telah dibaca.';
           } else {
  -            $user->unreadNotifications->markAsRead();
  +            $user->unreadNotifications()->update(['read_at' => now()]);
               $message = 'Semua notifikasi berhasil ditandai sebagai telah dibaca.';
           }
  ```
* **Dampak Optimasi :**
  1. Jumlah query saat menandai semua notifikasi terpangkas drastis dari **$N + 1$ query** menjadi hanya **1 query SQL murni**.
  2. Penggunaan alokasi memori PHP turun drastis karena sistem tidak lagi menginstansiasi ratusan objek model `DatabaseNotification` ke dalam RAM server.

---

## 7. Kesimpulan

Secara keseluruhan, Pull Request #59 yang disusun oleh Indriani Anwar telah berhasil mengimplementasikan modul antarmuka notifikasi yang fungsional, rapi, dan konsisten dengan tema desain visual KampusLMS.

Dari aspek keamanan, proteksi kepemilikan data telah diterapkan secara baik menggunakan `NotificationPolicy` untuk mencegah celah IDOR. Dari aspek performa, temuan terkait efisiensi query penandaan massal berhasil diselesaikan secara nyata melalui optimasi *Query Builder*, yang membuktikan efektivitas proses *peer review* dalam meningkatkan kualitas kode aplikasi sebelum masuk ke branch utama. Tanggapan yang diberikan terhadap temuan keamanan dan struktur kode juga memiliki landasan pertimbangan teknis yang rasional (*engineering trade-off*), sehingga proses kolaborasi berjalan secara sehat dan berorientasi pada kualitas produk perangkat lunak.
