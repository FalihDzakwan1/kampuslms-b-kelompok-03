<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_dosen_dapat_mengunggah_materi_format_pdf_pptx_docx_ke_disk_privat(): void
    {
        Storage::fake('local');

        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        $formats = [
            ['name' => 'modul-01.pdf', 'mime' => 'application/pdf', 'size' => 1024],
            ['name' => 'slide-presentasi.pptx', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'size' => 2048],
            ['name' => 'panduan-praktikum.docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'size' => 1536],
        ];

        foreach ($formats as $fmt) {
            $file = UploadedFile::fake()->create($fmt['name'], $fmt['size'], $fmt['mime']);

            $response = $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
                'title'       => 'Materi ' . $fmt['name'],
                'description' => 'Deskripsi untuk ' . $fmt['name'],
                'type'        => 'file',
                'file'        => $file,
            ]);

            $response->assertRedirect(route('dosen.courses.materials.index', $course));
            $response->assertSessionHas('success', 'Materi berhasil ditambahkan.');

            $material = Material::where('title', 'Materi ' . $fmt['name'])->first();
            $this->assertNotNull($material);
            $this->assertSame('file', $material->type);
            $this->assertSame($fmt['name'], $material->original_name);
            $this->assertNull($material->external_url);

            // Verifikasi berkas disimpan di disk privat ('local' -> storage/app/private)
            Storage::disk('local')->assertExists($material->file_path);
        }
    }

    public function test_unggah_materi_gagal_jika_ukuran_melebihi_20_mb(): void
    {
        Storage::fake('local');

        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        // 21 MB (21504 KB) > 20 MB batas maksimal
        $fileBesar = UploadedFile::fake()->create('materi-jumbo.pdf', 21504, 'application/pdf');

        $response = $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title'       => 'Materi Jumbo',
            'type'        => 'file',
            'file'        => $fileBesar,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('materials', ['title' => 'Materi Jumbo']);
    }

    public function test_unggah_materi_gagal_jika_format_bukan_pdf_pptx_docx(): void
    {
        Storage::fake('local');

        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        $fileIlegal = UploadedFile::fake()->create('arsip-berbahaya.zip', 500, 'application/zip');

        $response = $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title'       => 'Materi Terlarang',
            'type'        => 'file',
            'file'        => $fileIlegal,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('materials', ['title' => 'Materi Terlarang']);
    }

    public function test_unggah_materi_berkas_wajib_jika_tipe_file_dipilih(): void
    {
        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        $response = $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title' => 'Materi Tanpa Berkas',
            'type'  => 'file',
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('materials', ['title' => 'Materi Tanpa Berkas']);
    }

    public function test_unggah_materi_tautan_eksternal_wajib_valid_jika_tipe_link_dipilih(): void
    {
        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        // Link kosong
        $response = $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title'        => 'Materi Tautan Kosong',
            'type'         => 'link',
            'external_url' => '',
        ]);
        $response->assertSessionHasErrors('external_url');

        // Link tidak valid
        $response = $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title'        => 'Materi Tautan Tidak Valid',
            'type'         => 'link',
            'external_url' => 'bukan-url-resmi',
        ]);
        $response->assertSessionHasErrors('external_url');

        // Link valid berhasil
        $response = $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title'        => 'Materi Alternatif Link',
            'type'         => 'link',
            'external_url' => 'https://drive.google.com/file/d/123/view',
        ]);
        $response->assertRedirect(route('dosen.courses.materials.index', $course));
        $this->assertDatabaseHas('materials', [
            'title'        => 'Materi Alternatif Link',
            'type'         => 'link',
            'external_url' => 'https://drive.google.com/file/d/123/view',
            'file_path'    => null,
        ]);
    }

    public function test_dosen_dapat_memperbarui_materi_dan_mengganti_berkas_di_disk_privat(): void
    {
        Storage::fake('local');

        $dosen    = $this->makeUser('dosen');
        $course   = $this->makeCourse($dosen);
        $fileAwal = UploadedFile::fake()->create('versi-lama.pdf', 100, 'application/pdf');

        $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title' => 'Materi Versi Awal',
            'type'  => 'file',
            'file'  => $fileAwal,
        ]);

        $material = Material::where('title', 'Materi Versi Awal')->first();
        $pathAwal = $material->file_path;
        Storage::disk('local')->assertExists($pathAwal);

        // Ganti dengan berkas DOCX baru
        $fileBaru = UploadedFile::fake()->create('versi-revisi.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->actingAs($dosen)->put(route('dosen.materials.update', $material), [
            'title' => 'Materi Revisi',
            'type'  => 'file',
            'file'  => $fileBaru,
        ])->assertRedirect(route('dosen.materials.show', $material));

        $material->refresh();
        $this->assertSame('Materi Revisi', $material->title);
        $this->assertSame('versi-revisi.docx', $material->original_name);

        // Berkas lama dihapus dari disk privat dan berkas baru tersimpan
        Storage::disk('local')->assertMissing($pathAwal);
        Storage::disk('local')->assertExists($material->file_path);
    }

    public function test_menghapus_materi_juga_menghapus_berkas_pada_disk_privat(): void
    {
        Storage::fake('local');

        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);
        $file   = UploadedFile::fake()->create('materi-hapus.pptx', 300, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $this->actingAs($dosen)->post(route('dosen.courses.materials.store', $course), [
            'title' => 'Materi Akan Dihapus',
            'type'  => 'file',
            'file'  => $file,
        ]);

        $material = Material::where('title', 'Materi Akan Dihapus')->first();
        $filePath = $material->file_path;
        Storage::disk('local')->assertExists($filePath);

        $this->actingAs($dosen)->delete(route('dosen.materials.destroy', $material))
            ->assertRedirect(route('dosen.courses.materials.index', $course));

        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing($filePath);
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

    /** @test */
    public function unduh(): void
    {
        Storage::fake('local');
        Storage::put('materials/slide-kuliah.pptx', 'konten slide pptx');

        $dosen       = $this->makeUser('dosen');
        $dosenLain   = $this->makeUser('dosen');
        $admin       = $this->makeUser('admin');
        $mhsTerdaftar= $this->makeUser('mahasiswa');
        $mhsLuar     = $this->makeUser('mahasiswa');

        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhsTerdaftar);

        $material = $this->makeMaterial($course, [
            'type'          => 'file',
            'file_path'     => 'materials/slide-kuliah.pptx',
            'original_name' => 'slide-kuliah.pptx',
            'file_size'     => 1024,
            'external_url'  => null,
        ]);

        // 1. Mahasiswa terdaftar di MK: BERHASIL mengunduh (200 OK + header attachment berkas)
        $response = $this->actingAs($mhsTerdaftar)->get(route('materials.download', $material));
        $response->assertOk();
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition', ''), 'slide-kuliah.pptx'),
            'Header Content-Disposition harus menyertakan nama berkas asli.'
        );

        // 2. Mahasiswa TIDAK terdaftar di MK: DITOLAK (403 Forbidden via Policy)
        $this->actingAs($mhsLuar)->get(route('materials.download', $material))->assertForbidden();

        // 3. Dosen pengampu MK: BERHASIL mengunduh
        $this->actingAs($dosen)->get(route('materials.download', $material))->assertOk();

        // 4. Dosen lain di luar MK: DITOLAK (403 Forbidden via Policy)
        $this->actingAs($dosenLain)->get(route('materials.download', $material))->assertForbidden();

        // 5. Admin kampus: BERHASIL mengunduh
        $this->actingAs($admin)->get(route('materials.download', $material))->assertOk();

        // 6. Tamu (Guest tanpa login): DIARAHKAN ke halaman login (302)
        auth()->logout();
        $this->get(route('materials.download', $material))->assertRedirect('/login');
    }

    /** @test */
    public function pratinjau(): void
    {
        Storage::fake('local');
        Storage::put('materials/slide.pdf', 'dummy pdf content');

        $dosen       = $this->makeUser('dosen');
        $dosenLain   = $this->makeUser('dosen');
        $admin       = $this->makeUser('admin');
        $mhsTerdaftar= $this->makeUser('mahasiswa');
        $mhsLuar     = $this->makeUser('mahasiswa');

        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhsTerdaftar);

        $material = $this->makeMaterial($course, [
            'type'          => 'file',
            'file_path'     => 'materials/slide.pdf',
            'original_name' => 'slide.pdf',
            'file_size'     => 1024,
            'mime_type'     => 'application/pdf',
        ]);

        // 1. Mahasiswa terdaftar di MK: BERHASIL pratinjau (200 OK + inline header)
        $response = $this->actingAs($mhsTerdaftar)->get(route('materials.preview', $material));
        $response->assertOk();
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition', ''), 'inline'),
            'Header Content-Disposition harus inline untuk pratinjau.'
        );

        // 2. Mahasiswa luar MK: DITOLAK (403 Forbidden)
        $this->actingAs($mhsLuar)->get(route('materials.preview', $material))->assertForbidden();

        // 3. Dosen pengampu MK: BERHASIL pratinjau
        $this->actingAs($dosen)->get(route('materials.preview', $material))->assertOk();

        // 4. Dosen lain: DITOLAK (403 Forbidden)
        $this->actingAs($dosenLain)->get(route('materials.preview', $material))->assertForbidden();

        // 5. Admin: BERHASIL pratinjau
        $this->actingAs($admin)->get(route('materials.preview', $material))->assertOk();
    }

    public function test_unduh_materi_bertipe_link_mengembalikan_404(): void
    {
        $dosen = $this->makeUser('dosen');
        $mhs   = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $materialLink = $this->makeMaterial($course, [
            'type'         => 'link',
            'file_path'    => null,
            'external_url' => 'https://example.com',
        ]);

        $this->actingAs($mhs)->get(route('materials.download', $materialLink))->assertNotFound();
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
