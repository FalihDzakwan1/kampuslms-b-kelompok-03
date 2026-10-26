<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    /**
     * Halaman daftar notifikasi pengguna yang sedang login.
     * Menyaring berdasarkan status: semua, belum dibaca, atau sudah dibaca.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');

        $query = $user->notifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'totalCount', 'filter'));
    }

    /**
     * [ACCPT-NOTIF-POLLING]
     * Mengambil jumlah notifikasi belum dibaca & daftar 5 notifikasi terbaru.
     */
    public function poll(Request $request)
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
     * Menandai satu notifikasi atau semua notifikasi sebagai telah dibaca.
     * DENGAN PEMERIKSAAN KEPEMILIKAN:
     * Jika notifikasi bukan milik pengguna yang sedang login, akses ditolak (403).
     */
    public function markAsRead(Request $request, ?string $id = null)
    {
        $user = $request->user();

        if ($id && $id !== 'all') {
            $notification = DatabaseNotification::findOrFail($id);

            // Pemeriksaan kepemilikan via Policy / Gate
            Gate::authorize('update', $notification);

            $notification->markAsRead();
            $message = 'Notifikasi berhasil ditandai sebagai telah dibaca.';
        } else {
            $user->unreadNotifications->markAsRead();
            $message = 'Semua notifikasi berhasil ditandai sebagai telah dibaca.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Membuka notifikasi: menandai sudah dibaca lalu mengarahkan ke URL tujuan.
     * DENGAN PEMERIKSAAN KEPEMILIKAN:
     * Jika notifikasi bukan milik pengguna yang sedang login, akses ditolak (403).
     */
    public function open(Request $request, string $id)
    {
        $notification = DatabaseNotification::findOrFail($id);

        // Pemeriksaan kepemilikan via Policy / Gate
        Gate::authorize('update', $notification);

        $notification->markAsRead();

        $targetUrl = $notification->data['url'] ?? route('dashboard');

        return redirect($targetUrl);
    }
}
