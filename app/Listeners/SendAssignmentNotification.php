<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Mengirim notifikasi "tugas baru" ke seluruh mahasiswa di mata kuliah terkait.
 *
 * `implements ShouldQueue` membuat listener ini berjalan di latar belakang
 * (lewat worker), sehingga dosen tidak menunggu saat menekan "Simpan".
 * Tanpa worker (`php artisan queue:work`) job hanya menumpuk di tabel `jobs`
 * dan notifikasi tidak akan pernah terkirim, tanpa pesan error.
 */
class SendAssignmentNotification implements ShouldQueue
{
    /** Jumlah percobaan sebelum job dicatat di tabel failed_jobs. */
    public int $tries = 3;

    /** Jeda (detik) antar percobaan. */
    public int $backoff = 10;

    public function handle(AssignmentPublished $event): void
    {
        $assignment = $event->assignment->loadMissing('course');

        // Satu query untuk seluruh penerima (tanpa N+1). Mahasiswa yang
        // sudah di-soft-delete otomatis tidak ikut.
        $students = $assignment->course->students()->get();

        if ($students->isEmpty()) {
            return;
        }

        Notification::send($students, new NewAssignmentNotification($assignment));
    }

    public function failed(AssignmentPublished $event, Throwable $exception): void
    {
        Log::error('Gagal mengirim notifikasi tugas baru.', [
            'assignment_id' => $event->assignment->getKey(),
            'error'         => $exception->getMessage(),
        ]);
    }
}
