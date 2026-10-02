<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: akan diganti dengan UserPolicy di minggu 7
        return true;
    }

    public function rules(): array
    {
        // $this->user mengambil object User dari Route Model Binding
        $user = $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => 'required|in:admin,dosen,mahasiswa',
            // Note: Password di update dibikin opsional di kebanyakan sistem, 
            // tapi kita ikuti sesuai field yang Anda sediakan.
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi ya.',
            'email.required' => 'Alamat email tidak boleh kosong.',
            'email.email' => 'Format email sepertinya kurang tepat.',
            'email.unique' => 'Maaf, email ini sudah dipakai oleh pengguna lain.',
            'role.required' => 'Peran pengguna (role) harus dipastikan.',
            'role.in' => 'Pilihan peran tidak valid.',
        ];
    }
}
