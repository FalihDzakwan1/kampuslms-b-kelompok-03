<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradeGivenNotification;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * MINGGU 14 — Feature Test Suite Alur Kritis KampusLMS.
 *
 * Menguji 8 area kritis dengan minimal 20 skenario feature test:
 * 1. Autentikasi (login berhasil, gagal, akses tamu)
 * 2. CRUD Mata Kuliah (sukses admin, ditolak dosen & mahasiswa)
 * 3. Enrollment (isolasi akses & visibilitas mahasiswa)
 * 4. Unggah Materi (sukses dosen pengampu, ditolak dosen lain)
 * 5. Pengumpulan Tugas (sukses, tolak lewat deadline bila allow_late false, tolak non-peserta)
 * 6. Unduhan Berkas (tolak bukan pemilik, izinkan pemilik & dosen)
 * 7. Penilaian Tugas (sukses dosen pengampu, tolak dosen lain)
 * 8. Notifikasi (terkirim saat tugas dibuat & saat nilai diberikan)
 */
class CriticalFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    private function makeUser(string $role, array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => $role], $attrs));
    }

    private function makeCourse(User $lecturer, array $attrs = []): Course
    {
        return Course::factory()->create(array_merge(['lecturer_id' => $lecturer->id], $attrs));
    }

    private function enroll(Course $course, User $student): void
    {
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
    }

    private function makeAssignment(Course $course, array $attrs = []): Assignment
    {
        return Assignment::create(array_merge([
            'course_id'    => $course->id,
            'created_by'   => $course->lecturer_id,
            'title'        => 'Tugas Analisis Web',
            'instructions' => 'Instruksi tugas pengujian.',
            'due_at'       => now()->addDays(3),
            'max_score'    => 100,
            'allow_late'   => true,
            'status'       => 'published',
        ], $attrs));
    }

    private function makeSubmission(Assignment $assignment, User $student, string $path = 'submissions/uji.pdf'): Submission
    {
        return Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'file_path'     => $path,
            'original_name' => 'uji.pdf',
            'file_size'     => 1024,
            'submitted_at'  => now(),
            'is_late'       => false,
        ]);
    }

    // =========================================================================
    // 1. AUTENTIKASI
    // =========================================================================

    public function test_01_login_berhasil_dengan_kredensial_valid(): void
    {
        $user = $this->makeUser('mahasiswa', [
            'email'    => 'mhs.test14@kampuslms.test',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('mahasiswa.courses.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_02_login_gagal_dengan_password_salah(): void
    {
        $user = $this->makeUser('mahasiswa', [
            'email'    => 'mhs.gagal14@kampuslms.test',
            'password' => 'password',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email'    => $user->email,
            'password' => 'password-yang-salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_03_akses_halaman_terproteksi_tanpa_login_diarahkan_ke_halaman_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // =========================================================================
    // 2. CRUD MATA KULIAH
    // =========================================================================

    public function test_04_admin_sukses_membuat_mata_kuliah_baru(): void
    {
        $admin  = $this->makeUser('admin');
        $dosen  = $this->makeUser('dosen');

        $payload = [
            'code'        => 'CS14-NEW',
            'name'        => 'Rekayasa Perangkat Lunak',
            'description' => 'Mata kuliah pengembangan web',
            'sks'         => 3,
            'lecturer_id' => $dosen->id,
            'status'      => 'active',
        ];

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), $payload);

        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('courses', [
            'code'        => 'CS14-NEW',
            'name'        => 'Rekayasa Perangkat Lunak',
            'lecturer_id' => $dosen->id,
        ]);
    }

    public function test_05_admin_sukses_memperbarui_dan_menghapus_mata_kuliah(): void
    {
        $admin  = $this->makeUser('admin');
        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen, ['name' => 'Nama Sebelum Update', 'code' => 'CS14-EDIT']);

        // 1. Update mata kuliah
        $updatePayload = [
            'code'        => 'CS14-EDIT',
            'name'        => 'Nama Sesudah Update',
            'description' => 'Deskripsi baru',
            'sks'         => 4,
            'lecturer_id' => $dosen->id,
            'status'      => 'active',
        ];

        $updateResponse = $this->actingAs($admin)->put(route('admin.courses.update', $course), $updatePayload);
        $updateResponse->assertRedirect(route('admin.courses.show', $course));
        $this->assertDatabaseHas('courses', [
            'id'   => $course->id,
            'name' => 'Nama Sesudah Update',
            'sks'  => 4,
        ]);

        // 2. Hapus mata kuliah
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.courses.destroy', $course));
        $deleteResponse->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_06_dosen_ditolak_melakukan_crud_mata_kuliah(): void
    {
        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        $payload = [
            'code'        => 'CS14-DOSEN',
            'name'        => 'Mata Kuliah Ilegal',
            'description' => 'Dosen tidak berhak buat MK',
            'sks'         => 3,
            'lecturer_id' => $dosen->id,
            'status'      => 'active',
        ];

        // Store ditolak
        $this->actingAs($dosen)->post(route('admin.courses.store'), $payload)->assertForbidden();
        $this->assertDatabaseMissing('courses', ['code' => 'CS14-DOSEN']);

        // Update ditolak
        $this->actingAs($dosen)->put(route('admin.courses.update', $course), $payload)->assertForbidden();

        // Destroy ditolak
        $this->actingAs($dosen)->delete(route('admin.courses.destroy', $course))->assertForbidden();
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_07_mahasiswa_ditolak_melakukan_crud_mata_kuliah(): void
    {
        $mhs    = $this->makeUser('mahasiswa');
        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        $payload = [
            'code'        => 'CS14-MHS',
            'name'        => 'Mata Kuliah Mahasiswa',
            'description' => 'Mahasiswa dilarang buat MK',
            'sks'         => 3,
            'lecturer_id' => $dosen->id,
            'status'      => 'active',
        ];

        $this->actingAs($mhs)->post(route('admin.courses.store'), $payload)->assertForbidden();
        $this->actingAs($mhs)->put(route('admin.courses.update', $course), $payload)->assertForbidden();
        $this->actingAs($mhs)->delete(route('admin.courses.destroy', $course))->assertForbidden();

        $this->assertDatabaseMissing('courses', ['code' => 'CS14-MHS']);
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    // =========================================================================
    // 3. ENROLLMENT & VISIBILITAS MAHASISWA
    // =========================================================================

    public function test_08_mahasiswa_hanya_melihat_mata_kuliah_yang_diikutinya_di_daftar(): void
    {
        $dosen    = $this->makeUser('dosen');
        $mhs      = $this->makeUser('mahasiswa');
        $courseA  = $this->makeCourse($dosen, ['name' => 'MK-TERDAFTAR-14']);
        $courseB  = $this->makeCourse($dosen, ['name' => 'MK-TIDAK-TERDAFTAR-14']);

        $this->enroll($courseA, $mhs);

        $response = $this->actingAs($mhs)->get(route('mahasiswa.courses.index'));

        $response->assertOk();
        $response->assertSee('MK-TERDAFTAR-14');
        $response->assertDontSee('MK-TIDAK-TERDAFTAR-14');
    }

    public function test_09_mahasiswa_tidak_terdaftar_ditolak_mengakses_detail_mata_kuliah(): void
    {
        $dosen   = $this->makeUser('dosen');
        $mhsLuar = $this->makeUser('mahasiswa');
        $course  = $this->makeCourse($dosen);

        $response = $this->actingAs($mhsLuar)->get(route('mahasiswa.courses.show', $course));

        $response->assertForbidden();
    }

    // =========================================================================
    // 4. UNGGAH MATERI PERKULIAHAN
    // =========================================================================

    public function test_10_dosen_pengampu_sukses_mengunggah_materi_ke_disk_privat(): void
    {
        Storage::fake('local');

        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);
        $file   = UploadedFile::fake()->create('modul-minggu14.pdf', 1024, 'application/pdf');

        $payload = [
            'title'       => 'Materi Pengujian Modul 14',
            'description' => 'Materi resmi pertemuan 14',
            'type'        => 'file',
            'file'        => $file,
        ];

        $response = $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), $payload);

        $response->assertRedirect(route('dosen.courses.materials.index', $course));
        $response->assertSessionHas('success');

        $material = Material::where('title', 'Materi Pengujian Modul 14')->first();
        $this->assertNotNull($material);
        $this->assertSame($course->id, $material->course_id);
        $this->assertSame('modul-minggu14.pdf', $material->original_name);

        Storage::disk('local')->assertExists($material->file_path);
    }

    public function test_11_dosen_lain_ditolak_mengunggah_materi_pada_mata_kuliah_bukan_miliknya(): void
    {
        Storage::fake('local');

        $dosenPemilik = $this->makeUser('dosen');
        $dosenLain    = $this->makeUser('dosen');
        $course       = $this->makeCourse($dosenPemilik);
        $file         = UploadedFile::fake()->create('materi-ilegal.pdf', 500, 'application/pdf');

        $payload = [
            'title'       => 'Materi Bajakan',
            'type'        => 'file',
            'file'        => $file,
        ];

        $response = $this->actingAs($dosenLain)->post(route('dosen.courses.materials.store', $course), $payload);

        $response->assertForbidden();
        $this->assertDatabaseMissing('materials', ['title' => 'Materi Bajakan']);
    }

    // =========================================================================
    // 5. PENGUMPULAN TUGAS (SUBMISSIONS)
    // =========================================================================

    public function test_12_mahasiswa_terdaftar_sukses_mengumpulkan_tugas(): void
    {
        Storage::fake('local');

        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $assignment = $this->makeAssignment($course, [
            'due_at'     => now()->addDays(2),
            'allow_late' => true,
        ]);

        $file = UploadedFile::fake()->create('laporan-tugas.pdf', 500, 'application/pdf');

        $response = $this->actingAs($mhs)->post(
            route('mahasiswa.assignments.submissions.store', $assignment),
            [
                'file' => $file,
                'note' => 'Jawaban tugas resmi',
            ]
        );

        $response->assertSessionHas('success');

        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $mhs->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertSame('laporan-tugas.pdf', $submission->original_name);
        $this->assertFalse($submission->is_late);
        Storage::disk('local')->assertExists($submission->file_path);
    }

    public function test_13_pengumpulan_tugas_ditolak_setelah_deadline_bila_allow_late_false(): void
    {
        Storage::fake('local');

        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $assignment = $this->makeAssignment($course, [
            'due_at'     => now()->subHours(2), // Sudah lewat deadline
            'allow_late' => false,               // Keterlambatan dilarang
        ]);

        $file = UploadedFile::fake()->create('terlambat.pdf', 300, 'application/pdf');

        $response = $this->actingAs($mhs)->post(
            route('mahasiswa.assignments.submissions.store', $assignment),
            ['file' => $file]
        );

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $mhs->id,
        ]);
    }

    public function test_14_pengumpulan_tugas_ditolak_untuk_mahasiswa_yang_tidak_terdaftar(): void
    {
        Storage::fake('local');

        $dosen      = $this->makeUser('dosen');
        $mhsLuar    = $this->makeUser('mahasiswa'); // Tidak di-enroll
        $course     = $this->makeCourse($dosen);
        $assignment = $this->makeAssignment($course);

        $file = UploadedFile::fake()->create('ilegal.pdf', 200, 'application/pdf');

        $response = $this->actingAs($mhsLuar)->post(
            route('mahasiswa.assignments.submissions.store', $assignment),
            ['file' => $file]
        );

        $response->assertForbidden();
        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $mhsLuar->id,
        ]);
    }

    // =========================================================================
    // 6. UNDUHAN BERKAS & IDOR PROTECTION
    // =========================================================================

    public function test_15_unduhan_submission_ditolak_untuk_mahasiswa_bukan_pemilik(): void
    {
        Storage::fake('local');
        Storage::put('submissions/secret.pdf', 'konten rahasia submission');

        $dosen      = $this->makeUser('dosen');
        $mhsA       = $this->makeUser('mahasiswa');
        $mhsB       = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhsA);
        $this->enroll($course, $mhsB);

        $assignment = $this->makeAssignment($course);
        $submission = $this->makeSubmission($assignment, $mhsA, 'submissions/secret.pdf');

        // Mahasiswa B mencoba mengunduh submission milik Mahasiswa A -> 403 Forbidden
        $response = $this->actingAs($mhsB)->get(route('submissions.download', $submission));

        $response->assertForbidden();
    }

    public function test_16_pemilik_submission_dan_dosen_pengampu_diizinkan_mengunduh_submission(): void
    {
        Storage::fake('local');
        Storage::put('submissions/tugas-valid.pdf', 'konten tugas mahasiswa');

        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $assignment = $this->makeAssignment($course);
        $submission = $this->makeSubmission($assignment, $mhs, 'submissions/tugas-valid.pdf');

        // Pemilik submission berhasil mengunduh (200 OK)
        $this->actingAs($mhs)->get(route('submissions.download', $submission))->assertOk();

        // Dosen pengampu MK berhasil mengunduh (200 OK)
        $this->actingAs($dosen)->get(route('submissions.download', $submission))->assertOk();
    }

    // =========================================================================
    // 7. PENILAIAN TUGAS (GRADING)
    // =========================================================================

    public function test_17_dosen_pengampu_sukses_memberikan_nilai_pada_submission(): void
    {
        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $assignment = $this->makeAssignment($course, ['max_score' => 100]);
        $submission = $this->makeSubmission($assignment, $mhs);

        $payload = [
            'score'    => 92.5,
            'feedback' => 'Hasil pekerjaan sangat baik dan terstruktur.',
        ];

        $response = $this->actingAs($dosen)->put(route('dosen.submissions.grade', $submission), $payload);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by'     => $dosen->id,
            'score'         => 92.5,
            'feedback'      => 'Hasil pekerjaan sangat baik dan terstruktur.',
        ]);
    }

    public function test_18_dosen_lain_ditolak_memberikan_nilai_pada_submission_mata_kuliah_lain(): void
    {
        $dosenPemilik = $this->makeUser('dosen');
        $dosenLain    = $this->makeUser('dosen');
        $mhs          = $this->makeUser('mahasiswa');
        $course       = $this->makeCourse($dosenPemilik);
        $this->enroll($course, $mhs);

        $assignment = $this->makeAssignment($course);
        $submission = $this->makeSubmission($assignment, $mhs);

        $payload = [
            'score'    => 100,
            'feedback' => 'Penilaian tidak berhak',
        ];

        $response = $this->actingAs($dosenLain)->put(route('dosen.submissions.grade', $submission), $payload);

        $response->assertForbidden();
        $this->assertDatabaseMissing('grades', [
            'submission_id' => $submission->id,
        ]);
    }

    // =========================================================================
    // 8. NOTIFIKASI OTOMATIS (EVENTS & NOTIFICATIONS)
    // =========================================================================

    public function test_19_notifikasi_terkirim_ke_semua_mahasiswa_mk_saat_tugas_baru_dipublikasikan(): void
    {
        Notification::fake();

        $dosen   = $this->makeUser('dosen');
        $course  = $this->makeCourse($dosen);
        $mhsA    = $this->makeUser('mahasiswa');
        $mhsB    = $this->makeUser('mahasiswa');
        $mhsLuar = $this->makeUser('mahasiswa'); // Mahasiswa luar MK

        $this->enroll($course, $mhsA);
        $this->enroll($course, $mhsB);

        $payload = [
            'title'        => 'Tugas Baru Otomatis Notifikasi',
            'instructions' => 'Instruksi tugas pengujian notifikasi.',
            'due_at'       => now()->addWeek()->toDateTimeString(),
            'status'       => 'published',
        ];

        $this->actingAs($dosen)->post(route('dosen.courses.assignments.store', $course), $payload);

        // Bukti terkirim hanya ke mahasiswa terdaftar menggunakan Notification::fake()
        Notification::assertSentTo([$mhsA, $mhsB], NewAssignmentNotification::class);
        Notification::assertNotSentTo($mhsLuar, NewAssignmentNotification::class);
        Notification::assertNotSentTo($dosen, NewAssignmentNotification::class);
    }

    public function test_20_notifikasi_terkirim_ke_mahasiswa_saat_nilai_diberikan(): void
    {
        Notification::fake();

        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $assignment = $this->makeAssignment($course);
        $submission = $this->makeSubmission($assignment, $mhs);

        $this->actingAs($dosen)->put(route('dosen.submissions.grade', $submission), [
            'score'    => 88,
            'feedback' => 'Bagus sekali',
        ]);

        // Bukti notifikasi nilai terkirim ke pemilik tugas via Notification::fake()
        Notification::assertSentTo($mhs, GradeGivenNotification::class, function ($notification) use ($assignment) {
            return $notification->grade->submission->assignment_id === $assignment->id;
        });
    }

    // =========================================================================
    // 9. KEAMANAN TAMBAHAN (INTEGRITAS & DRAFT PROTECTION)
    // =========================================================================

    public function test_21_mahasiswa_tidak_dapat_melihat_atau_mengumpulkan_tugas_berstatus_draft(): void
    {
        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $draftAssignment = $this->makeAssignment($course, [
            'title'  => 'Tugas Rahasia Belum Rilis',
            'status' => 'draft',
        ]);

        // Detail tugas draft ditolak
        $this->actingAs($mhs)->get(route('mahasiswa.assignments.show', $draftAssignment))->assertForbidden();

        // Submit ke tugas draft ditolak
        $file = UploadedFile::fake()->create('draft.pdf', 100, 'application/pdf');
        $this->actingAs($mhs)->post(
            route('mahasiswa.assignments.submissions.store', $draftAssignment),
            ['file' => $file]
        )->assertForbidden();

        $this->assertDatabaseMissing('submissions', ['assignment_id' => $draftAssignment->id]);
    }

    public function test_22_mahasiswa_tidak_dapat_menghapus_submission_miliknya(): void
    {
        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $assignment = $this->makeAssignment($course);
        $submission = $this->makeSubmission($assignment, $mhs);

        $response = $this->actingAs($mhs)->delete(route('mahasiswa.submissions.destroy', $submission));

        $response->assertForbidden();
        $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
    }
}
