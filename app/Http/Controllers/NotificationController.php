<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * [ACCPT-NOTIF-POLLING]
     * Mengambil jumlah notifikasi belum dibaca & daftar 5 notifikasi terbaru.
     */
    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread_count'  => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($n) => [
                    'id'         => $n->id,
                    'read_at'    => $n->read_at,
                    'created_at' => $n->created_at->diffForHumans(),
                    'data'       => $n->data,
                ]),
        ]);
    }

    /**
     * [ACCPT-NOTIF-MARK-READ]
     * Menandai semua atau salah satu notifikasi sebagai telah dibaca.
     */
    public function markAsRead(Request $request, ?string $id = null): JsonResponse
    {
        $user = $request->user();

        if ($id && $id !== 'all') {
            $user->notifications()->find($id)?->markAsRead();
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }
}
