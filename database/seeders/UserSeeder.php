<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::withTrashed()->firstOrNew(['email' => 'admin@test.com']);
        $admin->forceFill([
            'name'       => 'Admin',
            'role'       => 'admin',
            'password'   => '123456',
            'deleted_at' => null,
        ])->save();

        // Dosen
        $dosen = User::withTrashed()->firstOrNew(['email' => 'dosen@test.com']);
        $dosen->forceFill([
            'name'       => 'Dosen Demo',
            'role'       => 'dosen',
            'password'   => '123456',
            'deleted_at' => null,
        ])->save();

        // Mahasiswa
        $mahasiswa = User::withTrashed()->firstOrNew(['email' => 'mhs@test.com']);
        $mahasiswa->forceFill([
            'name'       => 'Mahasiswa Demo',
            'role'       => 'mahasiswa',
            'password'   => '123456',
            'deleted_at' => null,
        ])->save();
    }
}
