<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Notifikasi in-app: tugas baru dipublikasikan.
 *
 * Payload hanya berisi ID dan judul. Tidak ada data sensitif, karena kolom
 * `data` di tabel notifications tidak terenkripsi.
 */
class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    /** Lewati job bila tugasnya sudah dihapus sebelum worker sempat memproses. */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Assignment $assignment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'assignment_published',
            'assignment_id' => $this->assignment->id,
            'course_id'     => $this->assignment->course_id,
            'course_name'   => $this->assignment->course->name,
            'title'         => $this->assignment->title,
            'due_at'        => $this->assignment->due_at?->toIso8601String(),
            'url'           => route('mahasiswa.assignments.show', $this->assignment, absolute: false),
        ];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Gagal menyimpan notifikasi tugas baru.', [
            'assignment_id' => $this->assignment->getKey(),
            'error'         => $exception->getMessage(),
        ]);
    }
}
