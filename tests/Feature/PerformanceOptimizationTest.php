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

    /**
     * [ACCPT-PERF-REKAP-NILAI]
     * Target Checkpoint: Halaman rekap nilai turun menjadi di bawah 15 query.
     */
    public function test_halaman_rekap_nilai_berjalan_di_bawah_15_query(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mhs   = User::factory()->create(['role' => 'mahasiswa']);

        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $course->students()->attach($mhs->id, ['enrolled_at' => now()]);

        // Buat 5 tugas beserta submission dan nilai
        for ($i = 1; $i <= 5; $i++) {
            $assignment = Assignment::create([
                'course_id'   => $course->id,
                'created_by'  => $dosen->id,
                'title'       => "Tugas $i",
                'instructions'=> "Instruksi pengerjaan tugas $i",
                'due_at'      => now()->addDays(3),
                'max_score'   => 100,
                'status'      => 'published',
            ]);

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id'       => $mhs->id,
                'file_path'     => "submissions/test_$i.pdf",
                'original_name' => "test_$i.pdf",
                'file_size'     => 1024,
                'submitted_at'  => now(),
                'is_late'       => false,
            ]);

            Grade::create([
                'submission_id' => $submission->id,
                'graded_by'     => $dosen->id,
                'score'         => 80 + ($i * 2),
                'graded_at'     => now(),
            ]);
        }

        // 1. Pengujian Rekap Nilai untuk Dosen (Target: < 15 Query)
        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseDosen = $this->actingAs($dosen)->get(route('dosen.courses.show', $course));
        $responseDosen->assertStatus(200);

        $dosenQueryCount = count(DB::getQueryLog());
        $this->assertLessThan(
            15,
            $dosenQueryCount,
            "Query rekap nilai dosen melebihi batas target 15 query (tercatat: {$dosenQueryCount} query)."
        );

        // 2. Pengujian Rekap Nilai untuk Mahasiswa (Target: < 15 Query)
        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseMhs = $this->actingAs($mhs)->get(route('mahasiswa.courses.show', $course));
        $responseMhs->assertStatus(200);

        $mhsQueryCount = count(DB::getQueryLog());
        $this->assertLessThan(
            15,
            $mhsQueryCount,
            "Query rekap nilai mahasiswa melebihi batas target 15 query (tercatat: {$mhsQueryCount} query)."
        );
    }

    /**
     * [ACCPT-PERF-NO-ENV-OUTSIDE-CONFIG]
     * Target Checkpoint: Seluruh pemanggilan env() di luar config/ dihapus, diganti config().
     */
    public function test_tidak_ada_pemanggilan_env_di_luar_direktori_config(): void
    {
        $scanPaths = [
            app_path(),
            base_path('routes'),
            resource_path('views'),
            database_path(),
        ];

        $violations = [];

        foreach ($scanPaths as $baseDir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseDir));
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $content = file_get_contents($file->getPathname());
                if (preg_match_all('/(?:\b|\\\\)env\s*\(/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                        $violations[] = $file->getPathname() . ':' . $line;
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Ditemukan pemanggilan env() di luar direktori config/: \n" . implode("\n", $violations)
        );
    }
}
