<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Minggu 9 — BUILD poin 7:
 * original_name dan ukuran berkas ditampilkan dalam format terbaca manusia.
 */
class FileSizeDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_format_ukuran_berkas_otomatis_memilih_satuan(): void
    {
        $this->assertSame('0 B', Material::humanFileSize(0));
        $this->assertSame('512 B', Material::humanFileSize(512));
        $this->assertSame('1.0 KB', Material::humanFileSize(1024));
        $this->assertSame('320.5 KB', Material::humanFileSize(328192));
        $this->assertSame('1.00 MB', Material::humanFileSize(1048576));
        $this->assertSame('4.77 MB', Material::humanFileSize(5000000));
        $this->assertNull(Material::humanFileSize(null));
    }

    public function test_accessor_formatted_file_size_pada_material_dan_submission(): void
    {
        $this->assertSame('2.00 MB', (new Material(['file_size' => 2097152]))->formatted_file_size);
        $this->assertSame('90.0 KB', (new Submission(['file_size' => 92160]))->formatted_file_size);
        $this->assertNull((new Material())->formatted_file_size);
    }

    public function test_halaman_materi_menampilkan_nama_asli_dan_ukuran_terbaca(): void
    {
        $dosen  = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $material = Material::create([
            'course_id'     => $course->id,
            'uploaded_by'   => $dosen->id,
            'title'         => 'Modul Uji',
            'description'   => 'Deskripsi.',
            'type'          => 'file',
            'file_path'     => 'materials/acak123.pdf',
            'original_name' => 'Modul Pertemuan 1.pdf',
            'file_size'     => 1572864, // 1.5 MB
        ]);

        $this->actingAs($dosen)->get(route('dosen.courses.materials.index', $course))
            ->assertOk()
            ->assertSee('Modul Pertemuan 1.pdf')
            ->assertSee('1.50 MB');

        $this->actingAs($dosen)->get(route('dosen.materials.show', $material))
            ->assertOk()
            ->assertSee('Modul Pertemuan 1.pdf')
            ->assertSee('1.50 MB');

        $this->actingAs($dosen)->get(route('dosen.courses.show', $course))
            ->assertOk()
            ->assertSee('Modul Pertemuan 1.pdf')
            ->assertSee('1.50 MB');
    }

    public function test_halaman_tugas_dan_submission_menampilkan_ukuran_terbaca(): void
    {
        $dosen  = User::factory()->create(['role' => 'dosen']);
        $mhs    = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $course->students()->attach($mhs->id, ['enrolled_at' => now()]);

        $assignment = Assignment::create([
            'course_id'    => $course->id,
            'created_by'   => $dosen->id,
            'title'        => 'Tugas Uji',
            'instructions' => 'Kerjakan.',
            'due_at'       => now()->addDays(3),
            'max_score'    => 100,
            'allow_late'   => true,
            'status'       => 'published',
        ]);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $mhs->id,
            'file_path'     => 'submissions/acak456.pdf',
            'original_name' => 'Laporan Budi.pdf',
            'file_size'     => 92160, // 90 KB
            'submitted_at'  => now(),
            'is_late'       => false,
        ]);

        // Dosen: tabel pengumpulan + detail submission
        $this->actingAs($dosen)->get(route('dosen.assignments.show', $assignment))
            ->assertOk()->assertSee('Laporan Budi.pdf')->assertSee('90.0 KB');
        $this->actingAs($dosen)->get(route('dosen.submissions.show', $submission))
            ->assertOk()->assertSee('Laporan Budi.pdf')->assertSee('90.0 KB');

        // Mahasiswa pemilik: kartu "Tugas Sudah Dikumpulkan" & halaman riwayat pengumpulan
        $this->actingAs($mhs)->get(route('mahasiswa.assignments.show', $assignment))
            ->assertOk()->assertSee('Laporan Budi.pdf')->assertSee('90.0 KB');
        $this->actingAs($mhs)->get(route('mahasiswa.submissions.index'))
            ->assertOk()->assertSee('Laporan Budi.pdf')->assertSee('90.0 KB');
    }

    public function test_api_submission_menyimpan_berkas_di_disk_privat_bukan_publik(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');

        $dosen  = User::factory()->create(['role' => 'dosen']);
        $mhs    = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $course->students()->attach($mhs->id, ['enrolled_at' => now()]);

        $assignment = Assignment::create([
            'course_id'    => $course->id,
            'created_by'   => $dosen->id,
            'title'        => 'Tugas API Privat',
            'instructions' => 'Kerjakan.',
            'due_at'       => now()->addDays(3),
            'max_score'    => 100,
            'allow_late'   => true,
            'status'       => 'published',
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($mhs);

        $file = \Illuminate\Http\UploadedFile::fake()->create('jawaban-api.pdf', 120, 'application/pdf');

        $response = $this->postJson("/api/v1/assignments/{$assignment->id}/submissions", [
            'file' => $file,
            'note' => 'Dikirim lewat API',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.original_name', 'jawaban-api.pdf');

        $submission = Submission::first();
        $this->assertNotNull($submission);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($submission->file_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($submission->file_path);
    }
}
