<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications
     * Mengambil daftar notifikasi pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate($request->get('per_page', 15));

        return NotificationResource::collection($notifications);
    }

    /**
     * POST /api/v1/notifications/{id}/read
     * Menandai notifikasi sebagai telah dibaca.
     * Dilengkapi pemeriksaan kepemilikan untuk mencegah IDOR.
     */
    public function read(Request $request, $id)
    {
        $notification = DatabaseNotification::findOrFail($id);

        abort_unless(
            $notification->notifiable_type === get_class($request->user())
            && (string) $notification->notifiable_id === (string) $request->user()->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notifikasi berhasil ditandai telah dibaca.',
        ]);
    }
}
