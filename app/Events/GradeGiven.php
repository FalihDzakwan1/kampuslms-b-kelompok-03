<?php

namespace App\Events;

use App\Models\Grade;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Diumumkan saat dosen menyimpan nilai pada sebuah submission
 * (pertama kali maupun saat nilai diperbarui).
 */
class GradeGiven
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Grade $grade,
        public bool $isUpdate = false,
    ) {
    }
}
