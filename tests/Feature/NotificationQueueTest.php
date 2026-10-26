<?php

namespace Tests\Feature;

use App\Events\AssignmentPublished;
use App\Events\GradeGiven;
use App\Listeners\SendAssignmentNotification;
use App\Listeners\SendGradeNotification;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradeGivenNotification;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

/**
 * Minggu 10 — Event + Listener + Notification (tugas baru & nilai diberikan).
 *
 * phpunit.xml memakai QUEUE_CONNECTION=sync, sehingga job berjalan langsung
 * dan hasilnya bisa dibaca di tabel notifications. Untuk membuktikan bahwa
 * listener benar-benar DITITIPKAN ke queue, dipakai Queue::fake().
 */
class NotificationQueueTest extends TestCase
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

    private function makeAssignment(Course $course, string $status = 'published'): Assignment
    {
        return Assignment::create([
            'course_id'    => $course->id,
            'created_by'   => $course->lecturer_id,
            'title'        => 'Tugas Notifikasi',
            'instructions' => 'Kerjakan.',
            'due_at'       => now()->addDays(3),
            'max_score'    => 100,
            'allow_late'   => true,
            'status'       => $status,
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

    private function assignmentPayload(string $status): array
    {
        return [
            'title'        => 'Tugas Baru Notifikasi',
            'instructions' => 'Kerjakan soal.',
            'due_at'       => now()->addWeek()->toDateTimeString(),
            'status'       => $status,
        ];
    }

    // ------------------------------------------------------------------
    // Peristiwa 1: tugas baru dipublikasikan
    // ------------------------------------------------------------------

    public function test_tugas_published_mengirim_notifikasi_ke_semua_mahasiswa_mk_saja(): void
    {
        $dosen   = $this->makeUser('dosen');
        $course  = $this->makeCourse($dosen);
        $mhsA    = $this->makeUser('mahasiswa');
        $mhsB    = $this->makeUser('mahasiswa');
        $mhsLuar = $this->makeUser('mahasiswa');
        $this->enroll($course, $mhsA);
        $this->enroll($course, $mhsB);

        $this->actingAs($dosen)
            ->post(route('dosen.courses.assignments.store', $course), $this->assignmentPayload('published'))
            ->assertRedirect();

        $this->assertSame(1, $mhsA->notifications()->count());
        $this->assertSame(1, $mhsB->notifications()->count());
        $this->assertSame(0, $mhsLuar->notifications()->count(), 'Mahasiswa di luar MK tidak boleh diberi tahu.');
        $this->assertSame(0, $dosen->notifications()->count(), 'Dosen pembuat tidak perlu diberi tahu.');

        $data = $mhsA->notifications()->first()->data;
        $assignment = Assignment::where('title', 'Tugas Baru Notifikasi')->first();

        $this->assertSame('assignment_published', $data['type']);
        $this->assertSame($assignment->id, $data['assignment_id']);
        $this->assertSame($assignment->title, $data['title']);
        $this->assertSame($course->name, $data['course_name']);
        $this->assertStringStartsWith('/', $data['url'], 'URL disimpan relatif agar tidak terikat host.');
    }

    public function test_tugas_draft_tidak_mengirim_notifikasi_sampai_dipublikasikan(): void
    {
        Event::fake([AssignmentPublished::class]);

        $dosen  = $this->makeUser('dosen');
        $course = $this->makeCourse($dosen);

        // 1. Dibuat sebagai draft: tidak ada event.
        $this->actingAs($dosen)
            ->post(route('dosen.courses.assignments.store', $course), $this->assignmentPayload('draft'));
        Event::assertNotDispatched(AssignmentPublished::class);

        $assignment = Assignment::where('title', 'Tugas Baru Notifikasi')->first();
        $this->assertSame('draft', $assignment->status);

        // 2. Diubah draft -> published: event terpicu satu kali.
        $this->actingAs($dosen)
            ->put(route('dosen.assignments.update', $assignment), $this->assignmentPayload('published'));
        Event::assertDispatchedTimes(AssignmentPublished::class, 1);

        // 3. Diedit lagi saat sudah published: TIDAK memicu event lagi (anti-spam).
        $this->actingAs($dosen)
            ->put(route('dosen.assignments.update', $assignment), array_merge($this->assignmentPayload('published'), [
                'title' => 'Judul Diedit',
            ]));
        Event::assertDispatchedTimes(AssignmentPublished::class, 1);
    }

    public function test_listener_tugas_baru_dititipkan_ke_queue_bukan_dijalankan_di_request(): void
    {
        Queue::fake();

        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        $this->actingAs($dosen)
            ->post(route('dosen.courses.assignments.store', $course), $this->assignmentPayload('published'));

        Queue::assertPushed(
            CallQueuedListener::class,
            fn ($job) => $job->class === SendAssignmentNotification::class
        );

        // Belum ada worker yang memproses: belum ada notifikasi sama sekali.
        $this->assertSame(0, $mhs->notifications()->count());
    }

    // ------------------------------------------------------------------
    // Peristiwa 2: nilai diberikan
    // ------------------------------------------------------------------

    public function test_pemberian_nilai_mengirim_notifikasi_hanya_ke_pemilik_submission(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhsA   = $this->makeUser('mahasiswa');
        $mhsB   = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhsA);
        $this->enroll($course, $mhsB);
        $assignment = $this->makeAssignment($course);
        $submission = $this->makeSubmission($assignment, $mhsA);
        $this->makeSubmission($assignment, $mhsB);

        $this->actingAs($dosen)
            ->put(route('dosen.submissions.grade', $submission), ['score' => 87.5, 'feedback' => 'RAHASIA-FEEDBACK-XYZ'])
            ->assertSessionHas('success');

        $this->assertSame(1, $mhsA->notifications()->count());
        $this->assertSame(0, $mhsB->notifications()->count(), 'Mahasiswa lain tidak boleh menerima notifikasi nilai orang lain.');

        $data = $mhsA->notifications()->first()->data;
        $this->assertSame('grade_given', $data['type']);
        $this->assertSame($submission->id, $data['submission_id']);
        $this->assertSame($assignment->title, $data['title']);
        $this->assertFalse($data['is_update']);
    }

    public function test_payload_notifikasi_nilai_tidak_memuat_nilai_atau_feedback(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $submission = $this->makeSubmission($this->makeAssignment($course), $mhs);

        $this->actingAs($dosen)
            ->put(route('dosen.submissions.grade', $submission), ['score' => 87.5, 'feedback' => 'RAHASIA-FEEDBACK-XYZ']);

        // Baca mentah dari tabel, seperti admin yang membuka database.
        $raw = (string) \DB::table('notifications')->where('notifiable_id', $mhs->id)->value('data');

        $this->assertNotSame('', $raw);
        $this->assertStringNotContainsString('RAHASIA-FEEDBACK-XYZ', $raw);
        $this->assertStringNotContainsString('87.5', $raw);
        $this->assertStringNotContainsString('score', $raw);
        $this->assertStringNotContainsString('feedback', $raw);
    }

    public function test_memperbarui_nilai_mengirim_notifikasi_dengan_penanda_pembaruan(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $submission = $this->makeSubmission($this->makeAssignment($course), $mhs);

        $this->actingAs($dosen)->put(route('dosen.submissions.grade', $submission), ['score' => 80]);
        $this->actingAs($dosen)->put(route('dosen.submissions.grade', $submission), ['score' => 90]);

        $this->assertSame(1, Grade::where('submission_id', $submission->id)->count());
        $this->assertSame(2, $mhs->notifications()->count());
        $this->assertTrue($mhs->notifications()->latest()->get()->contains(fn ($n) => $n->data['is_update'] === true));
    }

    public function test_listener_nilai_dititipkan_ke_queue(): void
    {
        Queue::fake();

        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $submission = $this->makeSubmission($this->makeAssignment($course), $mhs);

        $this->actingAs($dosen)->put(route('dosen.submissions.grade', $submission), ['score' => 90]);

        Queue::assertPushed(
            CallQueuedListener::class,
            fn ($job) => $job->class === SendGradeNotification::class
        );
        $this->assertSame(0, $mhs->notifications()->count());
    }

    public function test_dosen_lain_ditolak_dan_tidak_memicu_notifikasi_nilai(): void
    {
        Event::fake([GradeGiven::class]);

        $dosen      = $this->makeUser('dosen');
        $dosenLain  = $this->makeUser('dosen');
        $mhs        = $this->makeUser('mahasiswa');
        $course     = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);
        $submission = $this->makeSubmission($this->makeAssignment($course), $mhs);

        $this->actingAs($dosenLain)
            ->put(route('dosen.submissions.grade', $submission), ['score' => 100])
            ->assertForbidden();

        Event::assertNotDispatched(GradeGiven::class);
    }

    // ------------------------------------------------------------------
    // Jalur API ikut memicu event yang sama
    // ------------------------------------------------------------------

    public function test_api_membuat_tugas_published_dan_memberi_nilai_memicu_notifikasi(): void
    {
        $dosen  = $this->makeUser('dosen');
        $mhs    = $this->makeUser('mahasiswa');
        $course = $this->makeCourse($dosen);
        $this->enroll($course, $mhs);

        Sanctum::actingAs($dosen);

        $this->postJson('/api/v1/assignments', [
            'course_id'    => $course->id,
            'title'        => 'Tugas Lewat API',
            'instructions' => 'Kerjakan.',
            'due_at'       => now()->addWeek()->toDateTimeString(),
            'status'       => 'published',
        ])->assertCreated();

        $this->assertSame(1, $mhs->notifications()->count());

        $assignment = Assignment::where('title', 'Tugas Lewat API')->first();
        $submission = $this->makeSubmission($assignment, $mhs);

        $this->putJson("/api/v1/submissions/{$submission->id}/grade", ['score' => 70, 'feedback' => 'Cukup'])
            ->assertCreated();

        $this->assertSame(2, $mhs->notifications()->count());
    }

    // ------------------------------------------------------------------
    // Ketahanan: ShouldQueue, tries/backoff, failed()
    // ------------------------------------------------------------------

    public function test_listener_dan_notification_berjalan_asinkron_dengan_tries_dan_backoff(): void
    {
        $assignment = new Assignment();
        $grade      = new Grade();

        $objects = [
            new SendAssignmentNotification(),
            new SendGradeNotification(),
            new NewAssignmentNotification($assignment),
            new GradeGivenNotification($grade),
        ];

        foreach ($objects as $object) {
            $this->assertInstanceOf(ShouldQueue::class, $object, get_class($object) . ' harus implements ShouldQueue.');
            $this->assertSame(3, $object->tries);
            $this->assertSame(10, $object->backoff);
        }
    }

    public function test_method_failed_mencatat_log_saat_job_gagal(): void
    {
        $dosen      = $this->makeUser('dosen');
        $course     = $this->makeCourse($dosen);
        $assignment = $this->makeAssignment($course);

        Log::shouldReceive('error')
            ->once()
            ->with('Gagal mengirim notifikasi tugas baru.', \Mockery::on(
                fn ($ctx) => $ctx['assignment_id'] === $assignment->id && $ctx['error'] === 'Boom'
            ));

        (new SendAssignmentNotification())->failed(
            new AssignmentPublished($assignment),
            new RuntimeException('Boom')
        );
    }
}
