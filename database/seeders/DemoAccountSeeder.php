<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder akun demo untuk penguji / dosen.
 *
 * Idempotent: aman dijalankan berulang kali (tidak membuat duplikat dan
 * selalu mengembalikan password ke 'password'). Bisa dijalankan sendiri:
 *
 *   php artisan db:seed --class=DemoAccountSeeder --force
 */
class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Admin Utama',  'email' => 'admin@kampuslms.test',     'role' => 'admin',     'nim_nip' => null],
            ['name' => 'Dosen 1',      'email' => 'dosen@kampuslms.test',     'role' => 'dosen',     'nim_nip' => 'NIP001'],
            ['name' => 'Mahasiswa 1',  'email' => 'mahasiswa@kampuslms.test', 'role' => 'mahasiswa', 'nim_nip' => 'NIM001'],
        ];

        foreach ($accounts as $account) {
            // withTrashed: jika akun pernah di-soft-delete, pulihkan.
            $user = User::withTrashed()->firstOrNew(['email' => $account['email']]);

            // 'role' sengaja tidak ada di $fillable (cegah mass assignment),
            // jadi di-set lewat forceFill hanya dari seeder yang terpercaya ini.
            $user->forceFill([
                'name'       => $account['name'],
                'role'       => $account['role'],
                'nim_nip'    => $account['nim_nip'],
                'password'   => 'password', // di-hash otomatis oleh cast 'hashed'
                'deleted_at' => null,
            ])->save();
        }
    }
}
