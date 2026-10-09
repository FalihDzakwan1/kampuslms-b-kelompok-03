<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;

class GradePolicy
{
    /**
     * Melihat daftar semua nilai dari satu tugas.
     * Mahasiswa dilarang — tidak boleh melihat nilai orang lain.
     *   Gate::authorize('viewAny', [Grade::class, $assignment])
     *
     * Eager-load 'course' pada $assignment di Controller.
     */
    public function viewAny(User $user, \App\Models\Assignment $assignment): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $assignment->course->lecturer_id === $user->id,
            default => false, // mahasiswa tidak boleh
        };
    }

    /**
     * Melihat satu nilai.
     * Admin      : selalu boleh.
     * Dosen      : hanya nilai dari MK miliknya.
     * Mahasiswa  : hanya nilainya sendiri (lewat submission miliknya).
     *
     * Eager-load 'submission.assignment.course' di Controller agar tidak N+1.
     */
    public function view(User $user, Grade $grade): bool
    {
        return match ($user->role) {
            'admin'     => true,
            'dosen'     => $grade->submission->assignment->course->lecturer_id === $user->id,
            'mahasiswa' => $grade->submission->user_id === $user->id,
            default     => false,
        };
    }

    /**
     * Memberi nilai pertama kali.
     * Admin tidak boleh memberi nilai (sesuai matriks dosen).
     * Dosen hanya boleh menilai submission dari MK miliknya.
     *
     *   Gate::authorize('create', [Grade::class, $submission])
     * Eager-load 'assignment.course' pada $submission di Controller.
     */
    public function create(User $user, Submission $submission): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        return $submission->assignment->course->lecturer_id === $user->id;
    }

    /**
     * Mengubah nilai yang sudah diberikan.
     * Dosen boleh mengubah nilai dari MK miliknya.
     *
     * Eager-load 'submission.assignment.course' di Controller agar tidak N+1.
     */
    public function update(User $user, Grade $grade): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        return $grade->submission->assignment->course->lecturer_id === $user->id;
    }

    /**
     * Menghapus nilai.
     * Dosen boleh menghapus nilai dari MK miliknya.
     */
    public function delete(User $user, Grade $grade): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        return $grade->submission->assignment->course->lecturer_id === $user->id;
    }
}
