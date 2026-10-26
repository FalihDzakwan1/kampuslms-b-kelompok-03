<?php

namespace App\Listeners;

use App\Events\GradeGiven;
use App\Notifications\GradeGivenNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Memberi tahu mahasiswa pemilik submission bahwa nilainya sudah keluar.
 * Penerima ditentukan dari submission, bukan dari input request.
 */
class SendGradeNotification implements ShouldQueue
{
    /** Jumlah percobaan sebelum job dicatat di tabel failed_jobs. */
    public int $tries = 3;

    /** Jeda (detik) antar percobaan. */
    public int $backoff = 10;

    public function handle(GradeGiven $event): void
    {
        $grade = $event->grade->loadMissing('submission.assignment.course', 'submission.student');

        $student = $grade->submission->student;

        if (! $student) {
            return;
        }

        $student->notify(new GradeGivenNotification($grade, $event->isUpdate));
    }

    public function failed(GradeGiven $event, Throwable $exception): void
    {
        Log::error('Gagal mengirim notifikasi nilai.', [
            'grade_id' => $event->grade->getKey(),
            'error'    => $exception->getMessage(),
        ]);
    }
}
