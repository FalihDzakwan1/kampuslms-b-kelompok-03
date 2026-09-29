<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Memeriksa apakah pengguna sudah login DAN memiliki salah satu
     * peran yang diizinkan. Mengembalikan 401 jika belum login,
     * atau 403 jika login tapi perannya tidak sesuai.
     *
     * Penggunaan di route: middleware('role:admin,dosen')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Satu atau lebih peran yang diizinkan
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Belum login → 401 Unauthorized
        if (! $request->user()) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        // 2. Sudah login tapi perannya tidak cocok → 403 Forbidden
        if (! in_array($request->user()->role, $roles, strict: true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
