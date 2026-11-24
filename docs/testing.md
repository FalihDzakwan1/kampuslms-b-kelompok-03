# Dokumentasi Pengujian (Feature Test Suite) — KampusLMS

Dokumen ini memuat arsitektur, cakupan skenario, dan panduan pengujian otomatis (*Automated Feature Testing*) pada sistem **KampusLMS**. Seluruh alur kritis sistem dilindungi oleh rangkaian pengujian otomatis untuk menjamin keandalan fungsional, integritas data, serta penegakan otorisasi (RBAC & proteksi IDOR).

---

## 1. Menjalankan Pengujian

Pengujian menggunakan SQLite in-memory database (`phpunit.xml`) dan berjalan terisolasi tanpa memengaruhi database operasional/pengembangan.

```bash
# Menjalankan seluruh suite pengujian aplikasi
php artisan test

# Menjalankan rangkaian pengujian alur kritis (CriticalFlowsTest)
php artisan test --filter=CriticalFlowsTest
```

---

## 2. Matriks Cakupan 8 Alur Kritis (Minimal 20 Feature Test)

Berkas utama pengujian alur kritis terletak di **`tests/Feature/CriticalFlowsTest.php`**, mencakup **22 metode feature test** yang memetakan seluruh kebutuhan uji fungsional dan keamanan:

| No | Area Alur Kritis | Skenario yang Diuji | Metode Pengujian | Validasi Assertion |
|:--:|---|---|---|---|
| **1** | **Autentikasi** | Login berhasil | `test_01_login_berhasil_dengan_kredensial_valid` | `assertRedirect(courses.index)`<br>`assertAuthenticatedAs($user)` |
| | | Login gagal password salah | `test_02_login_gagal_dengan_password_salah` | `assertRedirect('/login')`<br>`assertSessionHasErrors('email')`<br>`assertGuest()` |
| | | Akses tanpa login | `test_03_akses_halaman_terproteksi_tanpa_login_diarahkan_ke_halaman_login` | `assertRedirect('/login')`<br>`assertGuest()` |
| **2** | **CRUD Mata Kuliah** | Sukses oleh Admin (Store) | `test_04_admin_sukses_membuat_mata_kuliah_baru` | `assertDatabaseHas('courses', ...)`<br>`assertRedirect(admin.courses.index)` |
| | | Sukses oleh Admin (Update & Delete) | `test_05_admin_sukses_memperbarui_dan_menghapus_mata_kuliah` | `assertDatabaseHas` (updated)<br>`assertDatabaseMissing` (deleted) |
| | | Ditolak untuk Dosen | `test_06_dosen_ditolak_melakukan_crud_mata_kuliah` | `assertForbidden()` (403)<br>`assertDatabaseMissing('courses')` |
| | | Ditolak untuk Mahasiswa | `test_07_mahasiswa_ditolak_melakukan_crud_mata_kuliah` | `assertForbidden()` (403)<br>`assertDatabaseMissing('courses')` |
| **3** | **Enrollment** | Mahasiswa hanya melihat MK miliknya | `test_08_mahasiswa_hanya_melihat_mata_kuliah_yang_diikutinya_di_daftar` | `assertOk()` (200)<br>`assertSee(MK_A)` & `assertDontSee(MK_B)` |
| | | Ditolak akses detail MK asing | `test_09_mahasiswa_tidak_terdaftar_ditolak_mengakses_detail_mata_kuliah` | `assertForbidden()` (403 via `CoursePolicy`) |
| **4** | **Unggah Materi** | Sukses oleh dosen pengampu | `test_10_dosen_pengampu_sukses_mengunggah_materi_ke_disk_privat` | `assertRedirect(materials.index)`<br>`Storage::disk('local')->assertExists(...)` |
| | | Ditolak untuk dosen lain | `test_11_dosen_lain_ditolak_mengunggah_materi_pada_mata_kuliah_bukan_miliknya` | `assertForbidden()` (403)<br>`assertDatabaseMissing('materials')` |
| **5** | **Pengumpulan Tugas** | Sukses oleh mahasiswa terdaftar | `test_12_mahasiswa_terdaftar_sukses_mengumpulkan_tugas` | `assertSessionHas('success')`<br>`assertDatabaseHas('submissions')`<br>`Storage::assertExists(...)` |
| | | Ditolak setelah deadline (allow_late=false) | `test_13_pengumpulan_tugas_ditolak_setelah_deadline_bila_allow_late_false` | `assertSessionHas('error')`<br>`assertDatabaseMissing('submissions')` |
| | | Ditolak untuk yang tidak terdaftar | `test_14_pengumpulan_tugas_ditolak_untuk_mahasiswa_yang_tidak_terdaftar` | `assertForbidden()` (403)<br>`assertDatabaseMissing('submissions')` |
| **6** | **Unduhan Berkas** | Ditolak untuk bukan pemilik | `test_15_unduhan_submission_ditolak_untuk_mahasiswa_bukan_pemilik` | `assertForbidden()` (403 via `SubmissionPolicy`) |
| | | Diizinkan untuk pemilik & dosen pengampu | `test_16_pemilik_submission_dan_dosen_pengampu_diizinkan_mengunduh_submission` | `assertOk()` (200 untuk Mhs A)<br>`assertOk()` (200 untuk Dosen) |
| **7** | **Penilaian** | Sukses oleh dosen pengampu | `test_17_dosen_pengampu_sukses_memberikan_nilai_pada_submission` | `assertSessionHas('success')`<br>`assertDatabaseHas('grades', ...)` |
| | | Ditolak untuk dosen lain | `test_18_dosen_lain_ditolak_memberikan_nilai_pada_submission_mata_kuliah_lain` | `assertForbidden()` (403)<br>`assertDatabaseMissing('grades')` |
| **8** | **Notifikasi** | Terkirim saat tugas dibuat (published) | `test_19_notifikasi_terkirim_ke_semua_mahasiswa_mk_saat_tugas_baru_dipublikasikan` | `Notification::assertSentTo([$mhsA, $mhsB], NewAssignmentNotification::class)`<br>`Notification::assertNotSentTo($mhsLuar, ...)` |
| | | Terkirim saat nilai diberikan | `test_20_notifikasi_terkirim_ke_mahasiswa_saat_nilai_diberikan` | `Notification::assertSentTo($mhs, GradeGivenNotification::class, ...)` |
| **+** | **Keamanan Tambahan** | Mahasiswa dilarang akses tugas draft | `test_21_mahasiswa_tidak_dapat_melihat_atau_mengumpulkan_tugas_berstatus_draft` | `assertForbidden()` (403)<br>`assertDatabaseMissing('submissions')` |
| | | Mahasiswa dilarang hapus submission | `test_22_mahasiswa_tidak_dapat_menghapus_submission_miliknya` | `assertForbidden()` (403)<br>`assertDatabaseHas('submissions')` |

---

## 3. Prinsip Kualitas Pengujian (Anti "Test Menipu")

Setiap test dalam suite ini dirancang untuk memiliki nilai diagnostik nyata dan mencegah kegagalan semu (*false positive*):

1. **Jalur Gagal Lebih Utama (Negative Testing):**
   * Menguji bahwa orang yang tidak berhak **pasti ditolak** dengan status **HTTP 403 Forbidden**, bukan hanya menguji status 200 pada jalur sukses.
2. **Tidak Menggunakan Dummy Seeder:**
   * Setiap pengujian membuat data secara mandiri menggunakan `User::factory()`, `Course::factory()`, dan `Model::create()`, sehingga urutan eksekusi test tidak saling memengaruhi (*test isolation*).
3. **Database Bersih (`RefreshDatabase`):**
   * Skema database dimigrasikan ulang dalam memori secara deterministik pada tiap test method.
4. **Isolasi Storage & Notifikasi Tanpa Efek Samping:**
   * Seluruh pengujian unggah/unduh berkas menggunakan `Storage::fake('local')` dan pengujian notifikasi pada `CriticalFlowsTest` menggunakan `Notification::fake()` sehingga tidak menulis berkas fisik ataupun memicu pengiriman notifikasi riil saat CI dijalankan.
5. **Verifikasi Dampak Database Nyata:**
   * Tidak ada pengujian yang hanya memeriksa status HTTP semata. Setiap mutasi data diverifikasi langsung melalui `assertDatabaseHas()` dan `assertDatabaseMissing()`.
