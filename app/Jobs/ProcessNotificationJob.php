<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * [ACCPT-JOB-QUEUE]
 * Job untuk memproses notifikasi latar belakang.
 */
class ProcessNotificationJob implements ShouldQueue
{
    use Queueable;

    // [ACCPT-JOB-TRIES-BACKOFF] Percobaan ulang 3x dengan jeda 10 detik
    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public array $payload = []
    ) {}

    public function handle(): void
    {
        Log::info('Memproses notifikasi job di background.', $this->payload);
    }

    /**
     * [ACCPT-JOB-FAILED]
     * Dipanggil otomatis saat job gagal setelah 3x percobaan,
     * sebelum dicatat ke tabel failed_jobs.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Job ProcessNotificationJob gagal dan dicatat ke failed_jobs.', [
            'payload' => $this->payload,
            'error'   => $exception?->getMessage(),
        ]);
    }
}
