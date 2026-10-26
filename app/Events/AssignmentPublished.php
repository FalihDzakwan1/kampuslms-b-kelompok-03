<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Diumumkan saat sebuah tugas berstatus "published" (dibuat langsung
 * published, atau diubah dari draft menjadi published).
 *
 * Event hanya membawa Assignment. Siapa yang diberi tahu dan lewat saluran
 * apa adalah urusan Listener, bukan Controller.
 */
class AssignmentPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public Assignment $assignment)
    {
    }
}
