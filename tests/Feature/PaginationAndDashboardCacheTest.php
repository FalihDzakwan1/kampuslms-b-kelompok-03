<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Services\DashboardCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PaginationAndDashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::flush();
    }

    /**
     * [ACCPT-PERF-PAGINATION]
     * Memastikan seluruh halaman daftar menggunakan pagination, tidak ada yang memuat seluruh tabel.
     */
    public function test_seluruh_halaman_daftar_menggunakan_paginasi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mhs   = User::factory()->create(['role' => 'mahasiswa']);

        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $course->students()->attach($mhs->id, ['enrolled_at' => now()]);

        // 1. Daftar Mata Kuliah (courses.index)
        $respCourses = $this->actingAs($admin)->get(route('admin.courses.index'));
        $respCourses->assertOk();
        $this->assertInstanceOf(
            LengthAwarePaginator::class,
            $respCourses->viewData('courses'),
            'Daftar mata kuliah harus menggunakan LengthAwarePaginator.'
        );

        // 2. Daftar Pengguna (users.index)
        $respUsers = $this->actingAs($admin)->get(route('admin.users.index'));
        $respUsers->assertOk();
        $this->assertInstanceOf(
            LengthAwarePaginator::class,
            $respUsers->viewData('users'),
            'Daftar pengguna harus menggunakan LengthAwarePaginator.'
        );

        // 3. Daftar Notifikasi (notifications.index)
        $respNotif = $this->actingAs($mhs)->get(route('notifications.index'));
        $respNotif->assertOk();
        $this->assertInstanceOf(
            LengthAwarePaginator::class,
            $respNotif->viewData('notifications'),
            'Daftar notifikasi harus menggunakan LengthAwarePaginator.'
        );

        // 4. Daftar Materi (materials.index)
        Material::create([
            'course_id'   => $course->id,
            'uploaded_by' => $dosen->id,
            'title'       => 'Materi Uji',
            'description' => 'Deskripsi materi uji',
            'type'        => 'link',
            'external_url'=> 'https://example.com',
        ]);
        $respMaterials = $this->actingAs($dosen)->get(route('dosen.courses.materials.index', $course));
        $respMaterials->assertOk();
        $this->assertInstanceOf(
            LengthAwarePaginator::class,
            $respMaterials->viewData('materials'),
            'Daftar materi kuliah harus menggunakan LengthAwarePaginator.'
        );

        // 5. Daftar Tugas (assignments.index)
        $assignment = Assignment::create([
            'course_id'   => $course->id,
            'created_by'  => $dosen->id,
            'title'       => 'Tugas Uji',
            'instructions'=> 'Kerjakan.',
            'due_at'      => now()->addDays(3),
            'status'      => 'published',
        ]);
        $respAssignments = $this->actingAs($dosen)->get(route('dosen.courses.assignments.index', $course));
        $respAssignments->assertOk();
        $this->assertInstanceOf(
            LengthAwarePaginator::class,
            $respAssignments->viewData('assignments'),
            'Daftar tugas kuliah harus menggunakan LengthAwarePaginator.'
        );

        // 6. Riwayat Pengumpulan Tugas (submissions.index)
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $mhs->id,
            'file_path'     => 'submissions/test.pdf',
            'original_name' => 'test.pdf',
            'file_size'     => 1024,
            'submitted_at'  => now(),
            'is_late'       => false,
        ]);
        $respSubmissions = $this->actingAs($mhs)->get(route('mahasiswa.submissions.index'));
        $respSubmissions->assertOk();
        $this->assertInstanceOf(
            LengthAwarePaginator::class,
            $respSubmissions->viewData('submissions'),
            'Daftar submission tugas mahasiswa harus menggunakan LengthAwarePaginator.'
        );

        // 7. Kelola Peserta Mata Kuliah (courses.enrollments)
        $respEnrollments = $this->actingAs($dosen)->get(route('dosen.courses.enrollments', $course));
        $respEnrollments->assertOk();
        $this->assertInstanceOf(
            LengthAwarePaginator::class,
            $respEnrollments->viewData('students'),
            'Daftar mahasiswa terdaftar pada kelola enrollments harus menggunakan LengthAwarePaginator.'
        );
    }

    /**
     * [ACCPT-PERF-CACHE]
     * Memastikan bagian metrik statistik dashboard di-cache pada pemanggilan berulang.
     */
    public function test_dashboard_menggunakan_cache_statistik_global(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $admin = User::factory()->create(['role' => 'admin']);

        Course::factory()->count(3)->create([
            'lecturer_id' => $dosen->id,
            'status'      => 'active',
        ]);

        $this->assertFalse(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Ringkasan Sistem Akademik');
        $response->assertSee('Cached • Ringkasan Cepat');

        $this->assertTrue(
            Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS),
            'Statistik dashboard harus tersimpan di dalam cache.'
        );

        $cachedData = Cache::get(DashboardCacheService::CACHE_KEY_GLOBAL_STATS);
        $this->assertIsArray($cachedData);
        $this->assertSame(3, $cachedData['active_courses']);
    }

    /**
     * [ACCPT-PERF-CACHE-CLEAR]
     * Memastikan cache dashboard dibersihkan secara otomatis saat data mata kuliah berubah.
     */
    public function test_cache_dashboard_dibersihkan_otomatis_saat_course_berubah(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        // 1. Populasi awal cache
        DashboardCacheService::getGlobalStats();
        $this->assertTrue(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        // 2. Buat course baru -> cache harus otomatis terhapus
        $course = Course::create([
            'code'        => 'IF101',
            'name'        => 'Pemrograman Web',
            'description' => 'Mata kuliah web.',
            'sks'         => 3,
            'lecturer_id' => $dosen->id,
            'status'      => 'active',
        ]);
        $this->assertFalse(
            Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS),
            'Cache harus otomatis dibersihkan saat Course baru dibuat.'
        );

        // 3. Populasi ulang cache
        DashboardCacheService::getGlobalStats();
        $this->assertTrue(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        // 4. Update course -> cache harus otomatis terhapus
        $course->update(['status' => 'archived']);
        $this->assertFalse(
            Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS),
            'Cache harus otomatis dibersihkan saat Course diupdate.'
        );

        // 5. Populasi ulang cache
        DashboardCacheService::getGlobalStats();
        $this->assertTrue(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        // 6. Delete course -> cache harus otomatis terhapus
        $course->delete();
        $this->assertFalse(
            Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS),
            'Cache harus otomatis dibersihkan saat Course dihapus.'
        );
    }

    /**
     * [ACCPT-PERF-CACHE-CLEAR]
     * Memastikan cache dashboard dibersihkan secara otomatis saat data materi atau penugasan berubah.
     */
    public function test_cache_dashboard_dibersihkan_otomatis_saat_material_atau_assignment_berubah(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);

        // Caching awal
        DashboardCacheService::getGlobalStats();
        $this->assertTrue(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        // Buat materi -> cache terhapus
        $mat = Material::create([
            'course_id'   => $course->id,
            'uploaded_by' => $dosen->id,
            'title'       => 'Materi Cache Invalidation',
            'description' => 'Deskripsi materi pengujian',
            'type'        => 'link',
            'external_url'=> 'https://example.com',
        ]);
        $this->assertFalse(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        // Caching ulang
        DashboardCacheService::getGlobalStats();
        $this->assertTrue(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        // Buat assignment -> cache terhapus
        $ass = Assignment::create([
            'course_id'   => $course->id,
            'created_by'  => $dosen->id,
            'title'       => 'Tugas Cache Invalidation',
            'instructions'=> 'Kerjakan soal.',
            'due_at'      => now()->addDays(4),
            'status'      => 'published',
        ]);
        $this->assertFalse(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        // Caching ulang & hapus materi -> cache terhapus
        DashboardCacheService::getGlobalStats();
        $mat->delete();
        $this->assertFalse(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));

        // Caching ulang & hapus tugas -> cache terhapus
        DashboardCacheService::getGlobalStats();
        $ass->delete();
        $this->assertFalse(Cache::has(DashboardCacheService::CACHE_KEY_GLOBAL_STATS));
    }
}
