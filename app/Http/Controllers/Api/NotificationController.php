<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
     */
    public function read(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notifikasi berhasil ditandai telah dibaca.',
        ]);
    }
}
