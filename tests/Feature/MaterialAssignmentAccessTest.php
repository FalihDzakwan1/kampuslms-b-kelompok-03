<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Test untuk CRUD Materi/Tugas, unduh berkas, penilaian, dan API.
 * Melengkapi AuthorizationTest (minggu 7).
 */
class MaterialAssignmentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function makeCourse(User $lecturer): Course
    {
        return Course::factory()->create(['lecturer_id' => $lecturer->id]);
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

    private function makeMaterial(Course $course, array $attrs = []): Material
    {
        return Material::create(array_merge([
            'course_id'    => $course->id,
            'uploaded_by'  => $course->lecturer_id,
            'title'        => 'Materi Uji',
            'description'  => 'Deskripsi materi uji.',
            'type'         => 'link',
            'external_url' => 'https://example.com',
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

    // ------------------------------------------------------------------
    // Halaman daftar dirender dengan data (menguji @can di dalam loop)
    // ------------------------------------------------------------------

    public function test_dosen_melihat_daftar_materi_dan_tugas_mata_kuliahnya(): void
    {
        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);
        $this->makeMaterial($course);
        $this->makeAssignment($course);

        $this->actingAs($dosen)->get(route('dosen.courses.materials.index', $course))
            ->assertOk()->assertSee('Materi Uji');
        $this->actingAs($dosen)->get(route('dosen.courses.assignments.index', $course))
            ->assertOk()->assertSee('Tugas Uji');
        $this->actingAs($dosen)->get(route('dosen.courses.show', $course))->assertOk();
    }

    public function test_mahasiswa_terdaftar_melihat_materi_dan_tugas_tanpa_tombol_kelola(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $material   = $this->makeMaterial($course);
        $assignment = $this->makeAssignment($course);

        $this->actingAs($mhs)->get(route('mahasiswa.courses.materials.index', $course))
            ->assertOk()->assertSee('Materi Uji')->assertDontSee('Tambah Materi Baru');
        $this->actingAs($mhs)->get(route('mahasiswa.courses.assignments.index', $course))
            ->assertOk()->assertSee('Tugas Uji')->assertDontSee('Buat Tugas Baru');
        $this->actingAs($mhs)->get(route('mahasiswa.materials.show', $material))->assertOk();
        $this->actingAs($mhs)->get(route('mahasiswa.assignments.show', $assignment))->assertOk();
        $this->actingAs($mhs)->get(route('mahasiswa.courses.show', $course))->assertOk();
    }

    public function test_mahasiswa_tidak_terdaftar_tidak_dapat_melihat_materi_dan_tugas(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $material   = $this->makeMaterial($course);
        $assignment = $this->makeAssignment($course);

        $this->actingAs($mhs)->get(route('mahasiswa.courses.materials.index', $course))->assertForbidden();
        $this->actingAs($mhs)->get(route('mahasiswa.materials.show', $material))->assertForbidden();
        $this->actingAs($mhs)->get(route('mahasiswa.assignments.show', $assignment))->assertForbidden();
    }

    // ------------------------------------------------------------------
    // CRUD materi & tugas
    // ------------------------------------------------------------------

    public function test_dosen_dapat_membuat_materi_dan_tugas_di_mata_kuliahnya(): void
    {
        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title' => 'Materi Baru', 'type' => 'link', 'external_url' => 'https://example.com/m',
        ])->assertRedirect(route('dosen.courses.materials.index', $course));

        $this->actingAs($dosen)->post(route('dosen.courses.assignments.store', $course), [
            'title' => 'Tugas Baru', 'instructions' => 'Kerjakan.', 'due_at' => now()->addWeek()->toDateTimeString(),
            'status' => 'published',
        ])->assertRedirect(route('dosen.courses.assignments.index', $course));

        $this->assertDatabaseHas('materials', ['course_id' => $course->id, 'title' => 'Materi Baru']);
        $this->assertDatabaseHas('assignments', ['course_id' => $course->id, 'title' => 'Tugas Baru']);
    }

    public function test_dosen_tidak_dapat_mengubah_atau_menghapus_materi_dan_tugas_dosen_lain(): void
    {
        $dosenA     = $this->makeUser('dosen');
        $courseB    = $this->makeCourse($this->makeUser('dosen'));
        $material   = $this->makeMaterial($courseB);
        $assignment = $this->makeAssignment($courseB);

        $this->actingAs($dosenA)->put(route('dosen.materials.update', $material), [
            'title' => 'Diretas', 'type' => 'link', 'external_url' => 'https://evil.test',
        ])->assertForbidden();
        $this->actingAs($dosenA)->delete(route('dosen.materials.destroy', $material))->assertForbidden();
        $this->actingAs($dosenA)->delete(route('dosen.assignments.destroy', $assignment))->assertForbidden();

        $this->assertDatabaseHas('materials', ['id' => $material->id, 'title' => 'Materi Uji']);
        $this->assertDatabaseHas('assignments', ['id' => $assignment->id]);
    }

    public function test_mahasiswa_tidak_dapat_membuat_materi_lewat_request_langsung(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $this->actingAs($mhs)->post(route('dosen.courses.materials.store', $course), [
            'title' => 'X', 'type' => 'link', 'external_url' => 'https://example.com',
        ])->assertForbidden();

        $this->assertDatabaseCount('materials', 0);
    }

    public function test_admin_dapat_mengelola_materi_dan_tugas_sesuai_matriks(): void
    {
        $admin  = $this->makeUser('admin');
        $course = $this->makeCourse($this->makeUser('dosen'));
        $material = $this->makeMaterial($course);

        $this->actingAs($admin)->get(route('admin.courses.materials.create', $course))->assertOk();
        $this->actingAs($admin)->get(route('admin.courses.assignments.create', $course))->assertOk();
        $this->actingAs($admin)->get(route('admin.materials.edit', $material))->assertOk();
        $this->actingAs($admin)->get(route('admin.courses.show', $course))->assertOk();
    }

    // ------------------------------------------------------------------
    // Unduh berkas lewat Policy
    // ------------------------------------------------------------------

    public function test_pemilik_dapat_mengunduh_submission_tetapi_mahasiswa_lain_tidak(): void
    {
        Storage::fake('local');
        Storage::put('submissions/uji.pdf', 'isi berkas');

        $dosen  = $this->makeUser('dosen');
        $mhsA   = $this->makeUser('mahasiswa');
        $mhsB   = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhsA);
        $this->enroll($course, $mhsB);
        $submission = $this->makeSubmission($this->makeAssignment($course), $mhsA);

        $this->actingAs($mhsA)->get(route('submissions.download', $submission))->assertOk();
        $this->actingAs($dosen)->get(route('submissions.download', $submission))->assertOk();
        $this->actingAs($mhsB)->get(route('submissions.download', $submission))->assertForbidden();
    }

    public function test_materi_berkas_hanya_dapat_diunduh_oleh_yang_berhak(): void
    {
        Storage::fake('local');
        Storage::put('materials/modul.pdf', 'isi modul');

        $dosen  = $this->makeUser('dosen');
        $mhsIn  = $this->makeUser('mahasiswa');
        $mhsOut = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhsIn);
        $material = $this->makeMaterial($course, [
            'type' => 'file', 'external_url' => null,
            'file_path' => 'materials/modul.pdf', 'original_name' => 'modul.pdf',
        ]);

        $this->actingAs($mhsIn)->get(route('materials.download', $material))->assertOk();
        $this->actingAs($mhsOut)->get(route('materials.download', $material))->assertForbidden();
    }

    public function test_unduh_berkas_yang_tidak_ada_mengembalikan_404_bukan_error(): void
    {
        Storage::fake('local');

        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);
        $material = $this->makeMaterial($course, [
            'type' => 'file', 'external_url' => null,
            'file_path' => 'materials/tidak-ada.pdf', 'original_name' => 'x.pdf',
        ]);

        $this->actingAs($dosen)->get(route('materials.download', $material))->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Penilaian
    // ------------------------------------------------------------------

    public function test_dosen_dapat_menilai_submission_di_mata_kuliahnya_tetapi_tidak_di_mata_kuliah_lain(): void
    {
        $dosenA = $this->makeUser('dosen');
        $dosenB = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $courseA = $this->makeCourse($dosenA);
        $this->enroll($courseA, $mhs);
        $submission = $this->makeSubmission($this->makeAssignment($courseA), $mhs);

        $this->actingAs($dosenB)->put(route('dosen.submissions.grade', $submission), ['score' => 90])
            ->assertForbidden();
        $this->assertDatabaseCount('grades', 0);

        $this->actingAs($dosenA)->put(route('dosen.submissions.grade', $submission), ['score' => 90, 'feedback' => 'Bagus'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('grades', ['submission_id' => $submission->id, 'graded_by' => $dosenA->id]);

        // Dosen boleh mengubah nilai (aturan A5)
        $this->actingAs($dosenA)->put(route('dosen.submissions.grade', $submission), ['score' => 95])
            ->assertSessionHas('success');
        $this->assertSame(1, \App\Models\Grade::where('submission_id', $submission->id)->count());
    }

    public function test_mahasiswa_dan_admin_tidak_dapat_memberi_nilai(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $admin  = $this->makeUser('admin');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $submission = $this->makeSubmission($this->makeAssignment($course), $mhs);

        // Rute grade hanya ada untuk dosen: peran lain ditolak middleware role.
        $this->actingAs($mhs)->put(route('dosen.submissions.grade', $submission), ['score' => 100])->assertForbidden();
        $this->actingAs($admin)->put(route('dosen.submissions.grade', $submission), ['score' => 100])->assertForbidden();
        $this->assertDatabaseCount('grades', 0);
    }

    // ------------------------------------------------------------------
    // API
    // ------------------------------------------------------------------

    public function test_api_materi_mata_kuliah_hanya_untuk_yang_berhak(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhsIn  = $this->makeUser('mahasiswa');
        $mhsOut = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhsIn);
        $this->makeMaterial($course);

        Sanctum::actingAs($mhsIn);
        $this->getJson("/api/v1/courses/{$course->id}/materials")->assertOk();
        $this->getJson("/api/v1/courses/{$course->id}/assignments")->assertOk();

        Sanctum::actingAs($mhsOut);
        $this->getJson("/api/v1/courses/{$course->id}/materials")->assertForbidden();
        $this->getJson("/api/v1/courses/{$course->id}/assignments")->assertForbidden();
    }

    public function test_api_tanpa_token_ditolak_dengan_401(): void
    {
        $this->getJson('/api/v1/courses')->assertUnauthorized();
    }
}
