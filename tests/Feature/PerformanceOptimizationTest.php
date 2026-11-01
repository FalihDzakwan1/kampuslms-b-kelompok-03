<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * [ACCPT-PERF-LAZY-LOADING]
     * Memastikan Model::preventLazyLoading() aktif di non-produksi (testing/local).
     */
    public function test_model_prevent_lazy_loading_aktif_di_lingkungan_non_produksi(): void
    {
        $this->assertTrue(
            Model::preventsLazyLoading(),
            'Model::preventLazyLoading() harus aktif ketika lingkungan aplikasi bukan produksi.'
        );
    }

    /**
     * Pengujian seluruh halaman utama: berjalan sukses (200 OK)
     * tanpa melanggar preventLazyLoading.
     */
    public function test_seluruh_halaman_utama_berjalan_tanpa_melanggar_lazy_loading(): void
    {
        // 1. Data Dummy
        $admin = User::factory()->create(['role' => 'admin']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mhs   = User::factory()->create(['role' => 'mahasiswa']);

        $courses = Course::factory()->count(5)->create(['lecturer_id' => $dosen->id]);
        $mainCourse = $courses->first();
        $mainCourse->students()->attach($mhs->id, ['enrolled_at' => now()]);

        $material = Material::create([
            'course_id'   => $mainCourse->id,
            'uploaded_by' => $dosen->id,
            'title'       => 'Materi Pengujian',
            'description' => 'Deskripsi materi pengujian',
            'type'        => 'link',
            'external_url'=> 'https://example.com',
        ]);

        $assignment = Assignment::create([
            'course_id'   => $mainCourse->id,
            'created_by'  => $dosen->id,
            'title'       => 'Tugas Pengujian',
            'instructions'=> 'Kerjakan soal ini.',
            'due_at'      => now()->addDays(5),
            'max_score'   => 100,
            'status'      => 'published',
        ]);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $mhs->id,
            'file_path'     => 'submissions/test.pdf',
            'original_name' => 'test.pdf',
            'file_size'     => 1024,
            'submitted_at'  => now(),
            'is_late'       => false,
        ]);

        Grade::create([
            'submission_id' => $submission->id,
            'graded_by'     => $dosen->id,
            'score'         => 95,
            'graded_at'     => now(),
        ]);

        // 2. Daftar endpoint halaman utama
        $endpoints = [
            // Dashboard
            ['user' => $mhs,   'url' => route('dashboard')],
            ['user' => $dosen, 'url' => route('dashboard')],
            ['user' => $admin, 'url' => route('dashboard')],

            // Daftar Mata Kuliah
            ['user' => $admin, 'url' => route('admin.courses.index')],
            ['user' => $dosen, 'url' => route('dosen.courses.index')],
            ['user' => $mhs,   'url' => route('mahasiswa.courses.index')],

            // Detail Mata Kuliah
            ['user' => $dosen, 'url' => route('dosen.courses.show', $mainCourse)],
            ['user' => $mhs,   'url' => route('mahasiswa.courses.show', $mainCourse)],

            // Kelola Peserta
            ['user' => $dosen, 'url' => route('dosen.courses.enrollments', $mainCourse)],

            // Materi Perkuliahan
            ['user' => $dosen, 'url' => route('dosen.courses.materials.index', $mainCourse)],
            ['user' => $mhs,   'url' => route('mahasiswa.courses.materials.index', $mainCourse)],

            // Tugas Perkuliahan
            ['user' => $dosen, 'url' => route('dosen.courses.assignments.index', $mainCourse)],
            ['user' => $mhs,   'url' => route('mahasiswa.courses.assignments.index', $mainCourse)],

            // Detail Tugas
            ['user' => $dosen, 'url' => route('dosen.assignments.show', $assignment)],
            ['user' => $mhs,   'url' => route('mahasiswa.assignments.show', $assignment)],

            // Riwayat Submissions Mahasiswa
            ['user' => $mhs,   'url' => route('mahasiswa.submissions.index')],

            // Detail Submission
            ['user' => $dosen, 'url' => route('dosen.submissions.show', $submission)],
            ['user' => $mhs,   'url' => route('mahasiswa.submissions.show', $submission)],

            // Manajemen Pengguna (Admin)
            ['user' => $admin, 'url' => route('admin.users.index')],

            // Notifikasi Web
            ['user' => $mhs,   'url' => route('notifications.index')],
        ];

        foreach ($endpoints as $ep) {
            $response = $this->actingAs($ep['user'])->get($ep['url']);
            $this->assertSame(
                200,
                $response->status(),
                "Halaman {$ep['url']} gagal diakses dengan status {$response->status()}"
            );
        }
    }

    /**
     * Membuktikan bahwa query pada courses.index tidak bertambah banyak (N+1)
     * saat jumlah mata kuliah bertambah banyak.
     */
    public function test_courses_index_bebas_dari_masalah_n_plus_satu_query(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dosen = User::factory()->create(['role' => 'dosen']);

        // Buat 10 mata kuliah
        $courses = Course::factory()->count(10)->create(['lecturer_id' => $dosen->id]);
        foreach ($courses as $c) {
            Material::create([
                'course_id'   => $c->id,
                'uploaded_by' => $dosen->id,
                'title'       => 'Materi ' . $c->id,
                'description' => 'Deskripsi ' . $c->id,
                'type'        => 'link',
                'external_url'=> 'https://example.com',
            ]);
            Assignment::create([
                'course_id'   => $c->id,
                'created_by'  => $dosen->id,
                'title'       => 'Tugas ' . $c->id,
                'instructions'=> 'Instruksi',
                'due_at'      => now()->addDays(2),
                'status'      => 'published',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($admin)->get(route('admin.courses.index'));
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Jika ada N+1 query: 1 query count paginator + 1 query courses + 10 count materials + 10 count assignments = minimal 22 query.
        // Dengan optimasi (withCount + with): hanya 3 query (count paginator, courses with subquery count, lecturer eager load).
        $this->assertLessThanOrEqual(
            5,
            $queryCount,
            "Query courses.index melebihi batas toleransi N+1 (tercatat: {$queryCount} query)."
        );
    }
}
