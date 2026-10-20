<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    /**
     * Melihat daftar materi dalam satu MK.
     * Course diteruskan sebagai argumen kedua dari Controller:
     *   Gate::authorize('viewAny', [Material::class, $course])
     *
     * Admin      : selalu boleh.
     * Dosen      : hanya MK miliknya.
     * Mahasiswa  : hanya MK yang sudah diikutinya.
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
     * Melihat detail DAN mengunduh file materi (satu method untuk keduanya).
     * Controller download memanggil Gate::authorize('view', $material).
     *
     * Pastikan Controller sudah eager-load 'course' sebelum memanggil Policy
     * agar tidak terjadi N+1: Material::with('course')->findOrFail($id)
     */
    public function view(User $user, Material $material): bool
    {
        return match ($user->role) {
            'admin'     => true,
            'dosen'     => $material->course->lecturer_id === $user->id,
            'mahasiswa' => $material->course
                               ->students()
                               ->where('users.id', $user->id)
                               ->exists(),
            default     => false,
        };
    }

    /**
     * Mengunduh berkas materi dari disk privat.
     * Menggunakan aturan yang sama dengan view:
     * - Admin      : selalu diizinkan
     * - Dosen      : hanya pengampu mata kuliah terkait
     * - Mahasiswa  : hanya mahasiswa yang terdaftar di mata kuliah tersebut
     */
    public function download(User $user, Material $material): bool
    {
        return $this->view($user, $material);
    }

    /**
     * Mengunggah materi baru ke dalam satu MK.
     *   Gate::authorize('create', [Material::class, $course])
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
     * Mengubah materi yang sudah ada.
     * Eager-load 'course' di Controller agar tidak N+1.
     */
    public function update(User $user, Material $material): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $material->course->lecturer_id === $user->id,
            default => false,
        };
    }

    /** Menghapus materi. Aturan sama dengan update. */
    public function delete(User $user, Material $material): bool
    {
        return match ($user->role) {
            'admin' => true,
            'dosen' => $material->course->lecturer_id === $user->id,
            default => false,
        };
    }
}
