<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: akan diganti dengan UserPolicy di minggu 7
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'nim_nip' => 'required|string|max:50|unique:users,nim_nip',
            'role' => 'required|in:admin,dosen,mahasiswa',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi ya.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email tidak boleh kosong.',
            'email.email' => 'Format email sepertinya kurang tepat.',
            'email.unique' => 'Maaf, email ini sudah terdaftar di sistem. Silakan gunakan email lain.',
            'password.required' => 'Kata sandi wajib diisi untuk pengguna baru.',
            'password.min' => 'Kata sandi minimal harus 8 karakter untuk keamanan.',
            'nim_nip.required' => 'NIM/NIP wajib diisi.',
            'nim_nip.unique' => 'NIM/NIP ini sudah terdaftar. Silakan periksa kembali.',
            'role.required' => 'Peran pengguna (role) harus dipilih.',
            'role.in' => 'Pilihan peran tidak valid.',
        ];
    }
}
