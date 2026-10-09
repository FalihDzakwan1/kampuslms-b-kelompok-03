<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

class AssignmentPolicy
{
    /**
     * Melihat daftar tugas dalam satu MK.
     *   Gate::authorize('viewAny', [Assignment::class, $course])
     */
    public function viewAny(User $user, Course $course): bool
    {
        return match ($user->role) {
            'admin'     => true,
            'dosen'     => $course->lecturer_id === $user->id,
            'mahasiswa' => $course->students()
                               ->where('users.id', $user->id)
                               ->exists(),
            default     => false,
        };
    }

    /**
     * Melihat detail satu tugas.
     * Eager-load 'course' di Controller agar tidak N+1.
     */
    public function view(User $user, Assignment $assignment): bool
    {
        return match ($user->role) {
            'admin'     => true,
            'dosen'     => $assignment->course->lecturer_id === $user->id,
            'mahasiswa' => $assignment->course
                               ->students()
                               ->where('users.id', $user->id)
                               ->exists(),
            default     => false,
        };
    }

    /**
     * Membuat tugas baru dalam satu MK.
     *   Gate::authorize('create', [Assignment::class, $course])
     */
    public function create(User $user, Course $course): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $course->lecturer_id === $user->id,
            default => false,
        };
    }

    /**
     * Mengubah tugas yang sudah ada.
     * Eager-load 'course' di Controller agar tidak N+1.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $assignment->course->lecturer_id === $user->id,
            default => false,
        };
    }

    /** Menghapus tugas. Aturan sama dengan update. */
    public function delete(User $user, Assignment $assignment): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $assignment->course->lecturer_id === $user->id,
            default => false,
        };
    }
}
