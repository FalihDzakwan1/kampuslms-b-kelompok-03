<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature test otorisasi KampusLMS (Milestone 2).
 *
 * Tiap skenario punya pasangan: jalur SUKSES (200/redirect/dibuat)
 * dan jalur DITOLAK (403/redirect login/tidak berubah).
 */
class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // View memakai @vite; di test tidak ada manifest hasil build.
        $this->withoutVite();
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    private function makeUser(string $role, array $attrs = []): User
    {
        // Factory berjalan unguarded, jadi 'role' (tidak fillable) bisa diisi.
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

    private function makeAssignment(Course $course): Assignment
    {
        return Assignment::create([
            'course_id'    => $course->id,
            'created_by'   => $course->lecturer_id,
            'title'        => 'Tugas Uji',
            'instructions' => 'Kerjakan soal uji.',
            'due_at'       => now()->addDays(3),
            'max_score'    => 100,
            'allow_late'   => true,
            'status'       => 'published',
        ]);
    }

    private function makeSubmission(Assignment $assignment, User $student): Submission
    {
        return Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'file_path'     => 'submissions/uji.pdf',
            'original_name' => 'uji.pdf',
            'file_size'     => 1024,
            'submitted_at'  => now(),
            'is_late'       => false,
        ]);
    }

    // ------------------------------------------------------------------
    // Autentikasi
    // ------------------------------------------------------------------

    public function test_login_dengan_kredensial_benar_berhasil(): void
    {
        $mhs = $this->makeUser('mahasiswa', ['email' => 'mhs-uji@kampuslms.test']);

        $this->post('/login', ['email' => $mhs->email, 'password' => 'password'])
            ->assertRedirect(route('mahasiswa.courses.index'));

        $this->assertAuthenticatedAs($mhs);
    }

    public function test_login_dengan_password_salah_ditolak(): void
    {
        $mhs = $this->makeUser('mahasiswa', ['email' => 'mhs-uji@kampuslms.test']);

        $this->from('/login')
            ->post('/login', ['email' => $mhs->email, 'password' => 'salah-total'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_tamu_diarahkan_ke_login_saat_membuka_halaman_terproteksi(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    // ------------------------------------------------------------------
    // Dosen <-> mata kuliah
    // ------------------------------------------------------------------

    public function test_dosen_dapat_melihat_mata_kuliah_miliknya(): void
    {
        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        $this->actingAs($dosen)
            ->get(route('dosen.courses.show', $course))
            ->assertOk();
    }

    public function test_dosen_tidak_dapat_melihat_mata_kuliah_dosen_lain(): void
    {
        $dosenA  = $this->makeUser('dosen');
        $dosenB  = $this->makeUser('dosen');
        $courseB = $this->makeCourse($dosenB);

        $this->actingAs($dosenA)
            ->get(route('dosen.courses.show', $courseB))
            ->assertForbidden();
    }

    public function test_dosen_tidak_dapat_menambah_materi_di_mata_kuliah_dosen_lain(): void
    {
        $dosenA  = $this->makeUser('dosen');
        $dosenB  = $this->makeUser('dosen');
        $courseB = $this->makeCourse($dosenB);

        $this->actingAs($dosenA)
            ->get(route('dosen.courses.materials.create', $courseB))
            ->assertForbidden();
    }

    public function test_dosen_dapat_mengelola_peserta_mata_kuliah_sendiri_tetapi_tidak_milik_lain(): void
    {
        $dosenA  = $this->makeUser('dosen');
        $dosenB  = $this->makeUser('dosen');
        $courseA = $this->makeCourse($dosenA);
        $courseB = $this->makeCourse($dosenB);

        $this->actingAs($dosenA)
            ->get(route('dosen.courses.enrollments', $courseA))
            ->assertOk();

        $this->actingAs($dosenA)
            ->get(route('dosen.courses.enrollments', $courseB))
            ->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Mahasiswa <-> mata kuliah
    // ------------------------------------------------------------------

    public function test_mahasiswa_terdaftar_dapat_melihat_mata_kuliahnya(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $this->actingAs($mhs)
            ->get(route('mahasiswa.courses.show', $course))
            ->assertOk();
    }

    public function test_mahasiswa_tidak_dapat_melihat_mata_kuliah_yang_tidak_diikuti(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);

        $this->actingAs($mhs)
            ->get(route('mahasiswa.courses.show', $course))
            ->assertForbidden();
    }

    public function test_daftar_mata_kuliah_mahasiswa_hanya_berisi_yang_diikuti(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $diikuti = $this->makeCourse($dosen, ['name' => 'MK-DIIKUTI-QWERTY']);
        $this->makeCourse($dosen, ['name' => 'MK-ASING-ZXCVBN']);
        $this->enroll($diikuti, $mhs);

        $this->actingAs($mhs)
            ->get(route('mahasiswa.courses.index'))
            ->assertOk()
            ->assertSee('MK-DIIKUTI-QWERTY')
            ->assertDontSee('MK-ASING-ZXCVBN');
    }

    // ------------------------------------------------------------------
    // Enrollment: hanya admin / dosen pengampu (temuan M2: self-enroll)
    // ------------------------------------------------------------------

    public function test_mahasiswa_tidak_dapat_mendaftarkan_dirinya_sendiri_ke_mata_kuliah(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->makeAssignment($course);

        // Endpoint self-enroll lama sudah dihapus.
        $this->actingAs($mhs)
            ->post("/mahasiswa/courses/{$course->id}/enroll")
            ->assertNotFound();

        // Jalur resmi enrollment pun tertutup bagi mahasiswa (middleware role).
        $this->actingAs($mhs)
            ->post(route('dosen.courses.enrollments.store', $course), ['user_id' => $mhs->id])
            ->assertForbidden();

        $this->assertDatabaseMissing('course_user', [
            'course_id' => $course->id,
            'user_id'   => $mhs->id,
        ]);

        // Materi & tugas MK tersebut tetap tertutup.
        $this->actingAs($mhs)->get(route('mahasiswa.courses.show', $course))->assertForbidden();
        $this->actingAs($mhs)->get(route('mahasiswa.courses.assignments.index', $course))->assertForbidden();
        $this->actingAs($mhs)->get(route('mahasiswa.courses.materials.index', $course))->assertForbidden();
    }

    public function test_dosen_pengampu_dapat_mendaftarkan_mahasiswa_tetapi_dosen_lain_tidak(): void
    {
        $dosenA = $this->makeUser('dosen');
        $dosenB = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosenA);

        $this->actingAs($dosenB)
            ->post(route('dosen.courses.enrollments.store', $course), ['user_id' => $mhs->id])
            ->assertForbidden();
        $this->assertDatabaseMissing('course_user', ['course_id' => $course->id, 'user_id' => $mhs->id]);

        $this->actingAs($dosenA)
            ->post(route('dosen.courses.enrollments.store', $course), ['user_id' => $mhs->id])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('course_user', ['course_id' => $course->id, 'user_id' => $mhs->id]);

        // Setelah didaftarkan dosen, barulah mahasiswa bisa membuka MK.
        $this->actingAs($mhs)->get(route('mahasiswa.courses.show', $course))->assertOk();
    }

    // ------------------------------------------------------------------
    // Submission (IDOR antar mahasiswa) & aturan submit sekali
    // ------------------------------------------------------------------

    public function test_mahasiswa_dapat_melihat_submission_miliknya_sendiri(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $submission = $this->makeSubmission($this->makeAssignment($course), $mhs);

        $this->actingAs($mhs)
            ->get(route('mahasiswa.submissions.show', $submission))
            ->assertOk();
    }

    public function test_mahasiswa_tidak_dapat_melihat_submission_mahasiswa_lain(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhsA   = $this->makeUser('mahasiswa');
        $mhsB   = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhsA);
        $this->enroll($course, $mhsB);
        $submissionB = $this->makeSubmission($this->makeAssignment($course), $mhsB);

        $this->actingAs($mhsA)
            ->get(route('mahasiswa.submissions.show', $submissionB))
            ->assertForbidden();
    }

    public function test_mahasiswa_terdaftar_dapat_mengumpulkan_tugas_pertama_kali(): void
    {
        Storage::fake('local');

        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $assignment = $this->makeAssignment($course);

        $this->actingAs($mhs)
            ->post(
                route('mahasiswa.assignments.submissions.store', $assignment),
                ['file' => UploadedFile::fake()->create('jawaban.pdf', 100, 'application/pdf')]
            )
            ->assertSessionHas('success');

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $mhs->id,
        ]);
    }

    public function test_mahasiswa_dapat_mengumpulkan_ulang_sebelum_dinilai_tetapi_tidak_setelah_dinilai(): void
    {
        Storage::fake('local');

        $dosen      = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $assignment = $this->makeAssignment($course);
        $submission = $this->makeSubmission($assignment, $mhs);

        // Belum dinilai: submit ulang diizinkan (updateOrCreate menimpa, tidak menumpuk baris).
        $this->actingAs($mhs)
            ->post(
                route('mahasiswa.assignments.submissions.store', $assignment),
                ['file' => UploadedFile::fake()->create('revisi.pdf', 100, 'application/pdf')]
            )
            ->assertSessionHas('success');

        $this->assertSame(1, Submission::where('assignment_id', $assignment->id)->count());
        $this->assertSame('revisi.pdf', $submission->fresh()->original_name);

        // Sudah dinilai dosen: submission dikunci, submit ulang ditolak.
        \App\Models\Grade::create([
            'submission_id' => $submission->id,
            'graded_by'     => $dosen->id,
            'score'         => 90,
            'graded_at'     => now(),
        ]);

        $this->actingAs($mhs)
            ->post(
                route('mahasiswa.assignments.submissions.store', $assignment),
                ['file' => UploadedFile::fake()->create('revisi-lagi.pdf', 100, 'application/pdf')]
            )
            ->assertForbidden();

        $this->assertSame('revisi.pdf', $submission->fresh()->original_name);
        $this->assertSame(1, Submission::where('assignment_id', $assignment->id)->count());
    }

    public function test_mahasiswa_tidak_dapat_menghapus_submission_miliknya(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $submission = $this->makeSubmission($this->makeAssignment($course), $mhs);

        $this->actingAs($mhs)
            ->delete(route('mahasiswa.submissions.destroy', $submission))
            ->assertForbidden();

        $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
    }

    // ------------------------------------------------------------------
    // Pengubahan role lewat request
    // ------------------------------------------------------------------

    public function test_mahasiswa_tidak_dapat_menaikkan_role_dirinya_lewat_request(): void
    {
        $mhs = $this->makeUser('mahasiswa');

        $this->actingAs($mhs)
            ->put(route('admin.users.update', $mhs), [
                'name'  => $mhs->name,
                'email' => $mhs->email,
                'role'  => 'admin',
            ])
            ->assertForbidden();

        $this->assertSame('mahasiswa', $mhs->fresh()->role);
    }

    public function test_admin_dapat_membuka_daftar_mata_kuliah_admin(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('admin.courses.index'))
            ->assertOk();
    }

    public function test_mahasiswa_tidak_dapat_melihat_atau_mengumpulkan_tugas_draft(): void
    {
        Storage::fake('local');

        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $draftAssignment = Assignment::create([
            'course_id'    => $course->id,
            'created_by'   => $dosen->id,
            'title'        => 'Tugas Rahasia Draft',
            'instructions' => 'Belum siap.',
            'due_at'       => now()->addDays(5),
            'max_score'    => 100,
            'allow_late'   => true,
            'status'       => 'draft',
        ]);

        // Tidak boleh melihat detail tugas draft (Policy view)
        $this->actingAs($mhs)
            ->get(route('mahasiswa.assignments.show', $draftAssignment))
            ->assertForbidden();

        // Tidak muncul di index tugas mahasiswa
        $this->actingAs($mhs)
            ->get(route('mahasiswa.courses.assignments.index', $course))
            ->assertOk()
            ->assertDontSee('Tugas Rahasia Draft');

        // Tidak boleh mengumpulkan ke tugas draft (SubmissionPolicy create)
        $this->actingAs($mhs)
            ->post(
                route('mahasiswa.assignments.submissions.store', $draftAssignment),
                ['file' => UploadedFile::fake()->create('jawaban.pdf', 100, 'application/pdf')]
            )
            ->assertForbidden();

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_login_tidak_menerima_password_alternatif_bypass(): void
    {
        $user = $this->makeUser('mahasiswa', [
            'email'    => 'user-tetap@kampuslms.test',
            'password' => 'password',
        ]);

        // Coba login pakai '123456' padahal passwordnya 'password'
        $this->from('/login')
            ->post('/login', [
                'email'    => $user->email,
                'password' => '123456',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_policy_mengizinkan_admin_dan_menolak_non_admin_untuk_manajemen_user(): void
    {
        $admin = $this->makeUser('admin');
        $dosen = $this->makeUser('dosen');
        $mhs   = $this->makeUser('mahasiswa');

        // Admin boleh melihat daftar user
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();

        // Non-admin ditolak saat mencoba akses admin.users
        $this->actingAs($dosen)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($mhs)->get(route('admin.users.index'))->assertForbidden();

        // Admin boleh buat user
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name'     => 'User Baru',
            'email'    => 'baru@kampuslms.test',
            'password' => 'password123',
            'nim_nip'  => 'NIM9999',
            'role'     => 'mahasiswa',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'baru@kampuslms.test']);
    }
}

