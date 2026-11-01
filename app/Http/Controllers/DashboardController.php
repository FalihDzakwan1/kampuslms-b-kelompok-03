<?php

namespace App\Http\Controllers;

use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan beranda / dashboard aplikasi.
     * Menggunakan statistik ter-cache untuk bagian metrik ringkasan platform.
     */
    public function index(Request $request)
    {
        $stats = DashboardCacheService::getGlobalStats();

        return view('dashboard', compact('stats'));
    }
}
