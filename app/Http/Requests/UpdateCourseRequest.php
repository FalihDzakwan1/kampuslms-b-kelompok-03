<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO: Akan diganti dengan pengecekan Policy di minggu 7
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('courses', 'code')->ignore($this->route('course'))
            ],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sks'         => ['required', 'integer', 'min:1', 'max:6'],
            'lecturer_id' => ['required', 'exists:users,id'],
            'status'      => ['required', 'in:draft,active,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'      => 'Kode mata kuliah tidak boleh kosong saat diedit.',
            'code.unique'        => 'Kode mata kuliah ini sudah dipakai mata kuliah lain.',
            'code.max'           => 'Kode mata kuliah maksimal 50 karakter.',
            'name.required'      => 'Nama mata kuliah wajib diisi.',
            'sks.required'       => 'SKS mata kuliah harus diisi.',
            'sks.min'            => 'Jumlah SKS minimal adalah 1.',
            'sks.max'            => 'Jumlah SKS tidak boleh lebih dari 6.',
            'lecturer_id.exists' => 'Dosen yang dipilih tidak valid atau tidak terdaftar di sistem.',
            'status.in'          => 'Status yang dipilih tidak sesuai dengan pilihan yang tersedia.',
        ];
    }
}