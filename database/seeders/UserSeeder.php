<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => '123456',
        ]);

        $admin->role = 'admin';
        $admin->save();


        // Dosen
        $dosen = User::create([
            'name' => 'Dosen Demo',
            'email' => 'dosen@test.com',
            'password' => '123456',
        ]);

        $dosen->role = 'dosen';
        $dosen->save();


        // Mahasiswa
        $mahasiswa = User::create([
            'name' => 'Mahasiswa Demo',
            'email' => 'mhs@test.com',
            'password' => '123456',
        ]);

        $mahasiswa->role = 'mahasiswa';
        $mahasiswa->save();
    }
}
