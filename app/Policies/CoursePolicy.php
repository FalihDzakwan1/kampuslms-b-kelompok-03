<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Semua role yang terautentikasi boleh mengakses list.
     * Filter data aktual (hanya MK sendiri / diikuti) dilakukan
     * di Controller via query, bukan di sini.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa']);
    }

    /**
     * Admin      : selalu boleh.
     * Dosen      : hanya jika course->lecturer_id === user->id.
     * Mahasiswa  : hanya jika sudah terdaftar di MK (pivot course_user).
     *
     * Catatan: exists() dipakai agar tidak menarik seluruh koleksi students.
     */
    public function view(User $user, Course $course): bool
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

    /** Hanya Admin yang boleh membuat mata kuliah baru. */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /** Hanya Admin yang boleh mengubah mata kuliah. */
    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    /** Hanya Admin yang boleh menghapus mata kuliah. */
    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}
