<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    /**
     * Memeriksa apakah notifikasi dapat dilihat oleh pengguna yang bersangkutan.
     */
    public function view(User $user, DatabaseNotification $notification): bool
    {
        return (string) $notification->notifiable_id === (string) $user->id
            && $notification->notifiable_type === get_class($user);
    }

    /**
     * Memeriksa apakah notifikasi dapat ditandai telah dibaca oleh pengguna yang bersangkutan.
     */
    public function update(User $user, DatabaseNotification $notification): bool
    {
        return (string) $notification->notifiable_id === (string) $user->id
            && $notification->notifiable_type === get_class($user);
    }

    /**
     * Memeriksa apakah notifikasi dapat dihapus oleh pengguna yang bersangkutan.
     */
    public function delete(User $user, DatabaseNotification $notification): bool
    {
        return (string) $notification->notifiable_id === (string) $user->id
            && $notification->notifiable_type === get_class($user);
    }
}
