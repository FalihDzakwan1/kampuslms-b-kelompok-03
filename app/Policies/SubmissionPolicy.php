<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Melihat daftar semua submission dari satu tugas.
     * Mahasiswa dilarang — tidak boleh melihat submission orang lain.
     *   Gate::authorize('viewAny', [Submission::class, $assignment])
     *
     * Eager-load 'course' pada $assignment di Controller.
     */
    public function viewAny(User $user, Assignment $assignment): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $assignment->course->lecturer_id === $user->id,
            default => false, // mahasiswa tidak boleh
        };
    }

    /**
     * Melihat detail satu submission.
     * Admin      : selalu boleh.
     * Dosen      : hanya submission dari MK miliknya.
     * Mahasiswa  : hanya submission miliknya sendiri (IDOR protection).
     *
     * Eager-load 'assignment.course' di Controller agar tidak N+1.
     */
    public function view(User $user, Submission $submission): bool
    {
        return match ($user->role) {
            'admin'     => true,
            'dosen'     => $submission->assignment->course->lecturer_id === $user->id,
            'mahasiswa' => $submission->user_id === $user->id,
            default     => false,
        };
    }

    /**
     * Mengumpulkan tugas (submit pertama kali).
     * Dua syarat HARUS terpenuhi sekaligus:
     *   1. Mahasiswa sudah terdaftar di MK tempat tugas berada.
     *   2. Mahasiswa belum pernah submit di tugas ini sebelumnya.
     *
     *   Gate::authorize('create', [Submission::class, $assignment])
     * Eager-load 'course' pada $assignment di Controller.
     */
    public function create(User $user, Assignment $assignment): bool
    {
        if ($user->role !== 'mahasiswa') {
            return false;
        }

        // Syarat 1: terdaftar di MK
        $isEnrolled = $assignment->course
            ->students()
            ->where('users.id', $user->id)
            ->exists();

        if (! $isEnrolled) {
            return false;
        }

        // Syarat 2: belum pernah submit di tugas ini
        $alreadySubmitted = $assignment->submissions()
            ->where('user_id', $user->id)
            ->exists();

        return ! $alreadySubmitted;
    }

    /**
     * Mengubah submission yang sudah dikumpulkan.
     * Dilarang untuk semua peran — submission bersifat permanen.
     */
    public function update(User $user, Submission $submission): bool
    {
        return false;
    }

    /**
     * Menarik kembali / menghapus submission.
     * Dilarang untuk semua peran — submission bersifat permanen.
     */
    public function delete(User $user, Submission $submission): bool
    {
        return false;
    }
}
