<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Flash message ditampilkan sekali oleh layout global.
 * Test ini mencegah halaman menampilkan pesan sukses ganda setelah CRUD.
 */
class FlashMessageTest extends TestCase
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

    private function assertTampilSekali(TestResponse $response, string $pesan): void
    {
        $response->assertOk();
        $this->assertSame(
            1,
            substr_count($response->getContent(), $pesan),
            "Pesan '{$pesan}' seharusnya tampil tepat 1 kali."
        );
    }

    public function test_pesan_pengguna_ditambahkan_tampil_sekali(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->followingRedirects()->post(route('admin.users.store'), [
            'name' => 'User Baru', 'email' => 'baru@kampuslms.test',
            'password' => 'password123', 'nim_nip' => 'NIM9001', 'role' => 'mahasiswa',
        ]);

        $this->assertTampilSekali($response, 'Pengguna berhasil ditambahkan.');
    }

    public function test_pesan_pengguna_diperbarui_dan_dihapus_tampil_sekali(): void
    {
        $admin  = $this->makeUser('admin');
        $target = $this->makeUser('mahasiswa');

        $response = $this->actingAs($admin)->followingRedirects()->put(route('admin.users.update', $target), [
            'name' => 'Nama Baru', 'email' => $target->email,
            'nim_nip' => 'NIM9002', 'role' => 'mahasiswa',
        ]);
        $this->assertTampilSekali($response, 'Data profil berhasil diperbarui.');

        $response = $this->actingAs($admin)->followingRedirects()->delete(route('admin.users.destroy', $target));
        $this->assertTampilSekali($response, 'Pengguna berhasil dihapus.');
    }

    public function test_pesan_mata_kuliah_dihapus_tampil_sekali(): void
    {
        $admin  = $this->makeUser('admin');
        $course = Course::factory()->create(['lecturer_id' => $this->makeUser('dosen')->id]);

        $response = $this->actingAs($admin)->followingRedirects()->delete(route('admin.courses.destroy', $course));

        $this->assertTampilSekali($response, 'Mata kuliah berhasil dihapus.');
    }

    public function test_pesan_materi_dan_tugas_ditambahkan_tampil_sekali(): void
    {
        $dosen  = $this->makeUser('dosen');
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);

        $response = $this->actingAs($dosen)->followingRedirects()->post(route('dosen.courses.materials.store', $course), [
            'title' => 'Materi Baru', 'type' => 'link', 'external_url' => 'https://example.com/m',
        ]);
        $this->assertTampilSekali($response, 'Materi berhasil ditambahkan.');

        $response = $this->actingAs($dosen)->followingRedirects()->post(route('dosen.courses.assignments.store', $course), [
            'title' => 'Tugas Baru', 'instructions' => 'Kerjakan.',
            'due_at' => now()->addWeek()->toDateTimeString(), 'status' => 'published',
        ]);
        $this->assertTampilSekali($response, 'Tugas berhasil ditambahkan.');
    }

    public function test_pesan_peserta_ditambahkan_dan_nilai_tersimpan_tampil_sekali(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);

        $response = $this->actingAs($dosen)
            ->from(route('dosen.courses.enrollments', $course))
            ->followingRedirects()
            ->post(route('dosen.courses.enrollments.store', $course), ['user_id' => $mhs->id]);
        $this->assertTampilSekali($response, 'berhasil ditambahkan ke mata kuliah');

        $assignment = Assignment::create([
            'course_id' => $course->id, 'created_by' => $dosen->id, 'title' => 'T',
            'instructions' => 'I', 'due_at' => now()->addDay(), 'max_score' => 100,
            'allow_late' => true, 'status' => 'published',
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id, 'user_id' => $mhs->id,
            'file_path' => 'submissions/x.pdf', 'original_name' => 'x.pdf',
            'file_size' => 1, 'submitted_at' => now(), 'is_late' => false,
        ]);

        $response = $this->actingAs($dosen)
            ->from(route('dosen.submissions.show', $submission))
            ->followingRedirects()
            ->put(route('dosen.submissions.grade', $submission), ['score' => 90]);
        $this->assertTampilSekali($response, 'Nilai dan umpan balik berhasil disimpan.');
    }
}
