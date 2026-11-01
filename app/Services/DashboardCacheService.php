<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    public const CACHE_KEY_GLOBAL_STATS = 'dashboard_stats_global';
    public const CACHE_TTL_SECONDS = 3600; // 1 jam

    /**
     * Mengambil statistik global dashboard yang di-cache.
     * Mengeliminasi query agregasi berat berulang pada setiap pembukaan beranda.
     */
    public static function getGlobalStats(): array
    {
        return Cache::remember(self::CACHE_KEY_GLOBAL_STATS, self::CACHE_TTL_SECONDS, function () {
            return [
                'total_courses'     => Course::count(),
                'active_courses'    => Course::where('status', 'active')->count(),
                'total_lecturers'   => User::where('role', 'dosen')->count(),
                'total_students'    => User::where('role', 'mahasiswa')->count(),
                'total_materials'   => Material::count(),
                'total_assignments' => Assignment::count(),
                'cached_at'         => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Menghapus cache statistik global dashboard secara otomatis saat data bermutasi.
     */
    public static function clearGlobalStats(): void
    {
        Cache::forget(self::CACHE_KEY_GLOBAL_STATS);
    }
}
