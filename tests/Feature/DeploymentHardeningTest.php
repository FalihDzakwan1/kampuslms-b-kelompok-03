<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pengerasan deployment (Minggu 9-12):
 * - login web dibatasi (throttle),
 * - batas unggah submission 5 MB (max:5120, satuan KB),
 * - route:cache berhasil (dipakai docker/start.sh di produksi).
 */
class DeploymentHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_login_web_dibatasi_setelah_lima_percobaan_gagal(): void
    {
        RateLimiter::clear('salah@kampuslms.test|127.0.0.1');

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => 'salah@kampuslms.test', 'password' => 'bukan-sandi'])
                ->assertStatus(302);
        }

        $this->post('/login', ['email' => 'salah@kampuslms.test', 'password' => 'bukan-sandi'])
            ->assertStatus(429);
    }

    private function siapkanTugas(): array
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $course->students()->attach($mahasiswa->id, ['enrolled_at' => now()]);

        $assignment = Assignment::create([
            'course_id'    => $course->id,
            'created_by'   => $dosen->id,
            'title'        => 'Tugas Batas Unggah',
            'instructions' => 'Kumpulkan berkas.',
            'due_at'       => now()->addDays(3),
            'max_score'    => 100,
            'allow_late'   => true,
            'status'       => 'published',
        ]);

        return [$mahasiswa, $assignment];
    }

    public function test_submission_3_mb_diterima(): void
    {
        Storage::fake('local');
        [$mahasiswa, $assignment] = $this->siapkanTugas();

        $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.assignments.submissions.store', $assignment), [
                'file' => UploadedFile::fake()->create('tugas.pdf', 3000, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $mahasiswa->id,
        ]);
    }

    public function test_submission_6_mb_ditolak(): void
    {
        Storage::fake('local');
        [$mahasiswa, $assignment] = $this->siapkanTugas();

        $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.assignments.submissions.store', $assignment), [
                'file' => UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $mahasiswa->id,
        ]);
    }

    public function test_route_cache_berhasil_dijalankan(): void
    {
        // docker/start.sh memakai `set -e`: bila route:cache gagal, kontainer produksi tidak mau start.
        try {
            $exitCode = Artisan::call('route:cache');
            $this->assertSame(0, $exitCode, Artisan::output());
        } finally {
            Artisan::call('route:clear');
        }
    }
}
