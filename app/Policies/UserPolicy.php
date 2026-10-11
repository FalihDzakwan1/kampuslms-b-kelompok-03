<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Melihat daftar seluruh pengguna.
     * Hanya Admin yang memiliki akses.
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->role === 'admin';
    }

    /**
     * Melihat detail pengguna.
     * Admin boleh melihat siapa saja; Pengguna biasa hanya boleh melihat profilnya sendiri.
     */
    public function view(User $authUser, User $user): bool
    {
        return $authUser->role === 'admin' || $authUser->id === $user->id;
    }

    /**
     * Membuat pengguna baru.
     * Hanya Admin yang boleh mendaftarkan pengguna baru ke sistem.
     */
    public function create(User $authUser): bool
    {
        return $authUser->role === 'admin';
    }

    /**
     * Memperbarui data pengguna.
     * Admin boleh memperbarui data siapa saja; Pengguna biasa hanya boleh mengubah profilnya sendiri.
     */
    public function update(User $authUser, User $user): bool
    {
        return $authUser->role === 'admin' || $authUser->id === $user->id;
    }

    /**
     * Menghapus pengguna.
     * Hanya Admin yang boleh menghapus akun pengguna.
     */
    public function delete(User $authUser, User $user): bool
    {
        return $authUser->role === 'admin';
    }
}
