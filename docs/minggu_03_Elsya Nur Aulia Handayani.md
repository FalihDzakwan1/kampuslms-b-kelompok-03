## Elsya Nur Aulia Handayani
## 10241026 


READ — Baca skema sebelum menulisnya (30 menit)

ERD 
<img src="image/ERD.jpeg" widhth="500">

1. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.

| Foreign Key | Relasi | `onDelete` | Alasan |
|---|---|---|---|
| `courses.lecturer_id → users.id` | User (dosen) mengajar course | `restrict` / `no action` | Mata kuliah tidak boleh otomatis terhapus ketika dosen dihapus karena data akademik harus tetap tersedia. Penghapusan dosen harus dicek terlebih dahulu atau dialihkan ke dosen lain. |
| `course_user.course_id → courses.id` | Course memiliki peserta | `cascade` | Data peserta hanya relevan jika mata kuliah masih ada. Jika mata kuliah dihapus, data pendaftaran peserta juga harus ikut dihapus agar tidak menjadi data yatim (orphan record). |
| `course_user.user_id → users.id` | User terdaftar dalam course | `cascade` | Jika akun mahasiswa/dosen dihapus, data keikutsertaannya pada mata kuliah tidak diperlukan lagi sehingga dapat dihapus otomatis. |
| `materials.course_id → courses.id` | Course memiliki materi | `cascade` | Materi tidak memiliki arti tanpa mata kuliah induknya. Jika course dihapus, semua materi terkait ikut dihapus. |
| `materials.uploaded_by → users.id` | User mengunggah materi | `set null` | Riwayat materi sebaiknya tetap tersedia walaupun akun pengunggah dihapus. Kolom `uploaded_by` harus dibuat nullable agar dapat dikosongkan. |
| `assignments.course_id → courses.id` | Course memiliki tugas | `cascade` | Tugas merupakan bagian dari mata kuliah. Jika mata kuliah dihapus, tugas juga harus dihapus. |
| `assignments.created_by → users.id` | User membuat tugas | `set null` | Informasi tugas tetap penting untuk histori pembelajaran meskipun pembuat tugas sudah tidak aktif. |
| `submissions.assignment_id → assignments.id` | Assignment memiliki submission | `cascade` | Submission hanya bermakna sebagai jawaban dari tugas tertentu. Jika tugas dihapus, pengumpulan mahasiswa juga tidak diperlukan. |
| `submissions.user_id → users.id` | User mengumpulkan tugas | `restrict` / `set null` | Nilai dan histori pengumpulan mahasiswa perlu dipertahankan untuk kebutuhan akademik. Data tidak sebaiknya langsung hilang. |
| `grades.submission_id → submissions.id` | Submission memiliki nilai | `cascade` | Nilai tidak dapat berdiri sendiri tanpa submission yang dinilai. Jika submission hilang, nilai terkait juga harus ikut hilang. |
| `grades.graded_by → users.id` | User memberi nilai | `set null` | Riwayat nilai tetap tersimpan walaupun akun dosen pemberi nilai sudah dihapus. |
| `notifications.notifiable_id → users.id` | User menerima notifikasi | `cascade` | Notifikasi merupakan data milik user tertentu sehingga dapat dihapus ketika user sudah tidak ada. |




2. Jawab: kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?

Jika seorang dosen dihapus, maka mata kuliahnya tidak ikut terhapus karena relasi `courses`.`lecturer_id` menggunakan `restrict` atau `no action`. Database akan menolak penghapusan dosen jika masih ada mata kuliah yang terhubung dengannya. Hal ini dilakukan agar data akademik tetap terjaga, karena mata kuliah masih memiliki keterkaitan dengan peserta, materi, tugas, submission, dan nilai mahasiswa. Jika menggunakan `cascade`, banyak data penting bisa ikut hilang. Oleh karena itu, mata kuliah harus dipindahkan terlebih dahulu ke dosen lain sebelum dosen tersebut dihapus.

Penghapusan dosen secara otomatis menggunakan `cascade` dapat menyebabkan kehilangan banyak data yang masih dibutuhkan, seperti riwayat pembelajaran dan aktivitas mahasiswa. Oleh karena itu, sistem sebaiknya mencegah penghapusan dosen secara langsung atau menyediakan mekanisme pemindahan mata kuliah kepada dosen lain. Dengan rancangan ini, data akademik tetap tersimpan meskipun akun dosen sudah tidak aktif.


4. Jawab: kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?

`grades.submission_id` dibuat **unique** karena satu pengumpulan tugas (submission) seharusnya hanya memiliki satu nilai.

Misalnya, mahasiswa mengumpulkan Tugas 1 satu kali. Maka sistem hanya boleh menyimpan satu nilai untuk tugas tersebut. Jika `submission_id` hanya menggunakan index biasa, bisa saja satu submission memiliki dua atau lebih nilai yang berbeda sehingga menimbulkan kebingungan mengenai nilai mana yang benar.

Dengan menggunakan `unique`, database akan menolak data duplikat sehingga setiap submission hanya memiliki satu nilai. Hal ini membuat data nilai lebih rapi, konsisten, dan mengurangi kesalahan dalam pengolahan nilai mahasiswa.

