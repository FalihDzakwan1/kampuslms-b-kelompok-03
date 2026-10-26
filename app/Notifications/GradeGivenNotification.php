<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Notifikasi in-app: nilai sebuah submission sudah diberikan/diperbarui.
 *
 * PENTING: payload TIDAK boleh memuat nilai (score) maupun isi feedback.
 * Kolom `data` tidak terenkripsi dan terbaca siapa pun yang bisa membuka
 * tabel notifications. Mahasiswa melihat nilainya lewat halaman detail
 * submission, yang dilindungi Policy.
 */
class GradeGivenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    /** Lewati job bila nilainya sudah dihapus sebelum worker sempat memproses. */
    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public Grade $grade,
        public bool $isUpdate = false,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $submission = $this->grade->submission;
        $assignment = $submission->assignment;

        return [
            'type'          => 'grade_given',
            'submission_id' => $submission->id,
            'assignment_id' => $assignment->id,
            'course_name'   => $assignment->course->name,
            'title'         => $assignment->title,
            'is_update'     => $this->isUpdate,
            'url'           => route('mahasiswa.submissions.show', $submission, absolute: false),
        ];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Gagal menyimpan notifikasi nilai.', [
            'grade_id' => $this->grade->getKey(),
            'error'    => $exception->getMessage(),
        ]);
    }
}
