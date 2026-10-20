<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    /**
     * Daftar materi dalam satu MK.
     * MaterialPolicy@viewAny: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa terdaftar ✅
     */
    public function index(Course $course)
    {
        Gate::authorize('viewAny', [Material::class, $course]);

        return view('materials.index', [
            'course'    => $course,
            'materials' => $course->materials()->with('course', 'uploader')->get(),
        ]);
    }

    /**
     * Form tambah materi.
     * MaterialPolicy@create: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa ❌
     */
    public function create(Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);

        return view('materials.create', compact('course'));
    }

    /**
     * Simpan materi baru.
     * Mendukung unggah berkas (PDF/PPTX/DOCX, maks 20 MB, disk privat)
     * atau alternatif berupa tautan eksternal.
     */
    public function store(Request $request, Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);

        $isFile = $request->type === 'file';
        $isLink = $request->type === 'link';

        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:file,link',
            'file'         => [
                Rule::requiredIf($isFile),
                Rule::excludeIf($isLink),
                'file',
                'mimes:pdf,pptx,docx',
                'extensions:pdf,pptx,docx',
                'max:20480',
            ],
            'external_url' => [
                Rule::requiredIf($isLink),
                Rule::excludeIf($isFile),
                'url',
                'max:2048',
            ],
        ], [
            'title.required'        => 'Judul materi wajib diisi.',
            'type.required'         => 'Tipe materi wajib dipilih.',
            'type.in'               => 'Tipe materi harus berupa berkas atau tautan eksternal.',
            'file.required'         => 'Berkas materi wajib diunggah untuk tipe berkas.',
            'file.file'             => 'Berkas materi yang diunggah tidak valid.',
            'file.mimes'            => 'Format berkas harus berupa PDF, PPTX, atau DOCX.',
            'file.extensions'       => 'Ekstensi berkas harus berupa .pdf, .pptx, atau .docx.',
            'file.max'              => 'Ukuran berkas materi maksimal 20 MB.',
            'external_url.required' => 'Tautan eksternal wajib diisi untuk tipe tautan.',
            'external_url.url'      => 'Format tautan eksternal harus berupa URL yang valid (contoh: https://...).',
            'external_url.max'      => 'Tautan eksternal maksimal 2048 karakter.',
        ]);

        $data = [
            'course_id'    => $course->id,
            'uploaded_by'  => auth()->id(),
            'title'        => $request->title,
            'description'  => $request->description ?? '',
            'type'         => $request->type,
            'external_url' => $isFile ? null : $request->external_url,
        ];

        if ($isFile && $request->hasFile('file')) {
            $file                  = $request->file('file');
            // Simpan secara aman di disk privat ('local' -> storage/app/private)
            $data['file_path']     = $file->store('materials', 'local');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size']     = $file->getSize();
            $data['mime_type']     = $file->getMimeType();
        }

        $course->materials()->create($data);

        return redirect()
            ->route(auth()->user()->role . '.courses.materials.index', $course)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Detail materi (juga dipakai sebagai otorisasi tombol Unduh).
     * MaterialPolicy@view: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa terdaftar ✅
     */
    public function show(Material $material)
    {
        $material->loadMissing('course');

        Gate::authorize('view', $material);

        $material->load('uploader');

        return view('materials.show', compact('material'));
    }

    /**
     * Form edit materi.
     * MaterialPolicy@update: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa ❌
     */
    public function edit(Material $material)
    {
        $material->loadMissing('course');

        Gate::authorize('update', $material);

        return view('materials.edit', compact('material'));
    }

    /**
     * Simpan perubahan materi.
     * Mendukung pembaruan berkas (PDF/PPTX/DOCX, maks 20 MB, disk privat)
     * atau beralih ke tautan eksternal.
     */
    public function update(Request $request, Material $material)
    {
        $material->loadMissing('course');

        Gate::authorize('update', $material);

        $isFile = $request->type === 'file';
        $isLink = $request->type === 'link';
        $hasExistingFile = !empty($material->file_path);

        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:file,link',
            'file'         => [
                Rule::requiredIf($isFile && !$hasExistingFile),
                Rule::excludeIf($isLink),
                'nullable',
                'file',
                'mimes:pdf,pptx,docx',
                'extensions:pdf,pptx,docx',
                'max:20480',
            ],
            'external_url' => [
                Rule::requiredIf($isLink),
                Rule::excludeIf($isFile),
                'url',
                'max:2048',
            ],
        ], [
            'title.required'        => 'Judul materi wajib diisi.',
            'type.required'         => 'Tipe materi wajib dipilih.',
            'type.in'               => 'Tipe materi harus berupa berkas atau tautan eksternal.',
            'file.required'         => 'Berkas materi wajib diunggah untuk tipe berkas.',
            'file.file'             => 'Berkas materi yang diunggah tidak valid.',
            'file.mimes'            => 'Format berkas harus berupa PDF, PPTX, atau DOCX.',
            'file.extensions'       => 'Ekstensi berkas harus berupa .pdf, .pptx, atau .docx.',
            'file.max'              => 'Ukuran berkas materi maksimal 20 MB.',
            'external_url.required' => 'Tautan eksternal wajib diisi untuk tipe tautan.',
            'external_url.url'      => 'Format tautan eksternal harus berupa URL yang valid (contoh: https://...).',
            'external_url.max'      => 'Tautan eksternal maksimal 2048 karakter.',
        ]);

        $data = [
            'title'        => $request->title,
            'description'  => $request->description ?? '',
            'type'         => $request->type,
        ];

        if ($isFile) {
            $data['external_url'] = null;
            if ($request->hasFile('file')) {
                // Hapus berkas lama dari disk privat jika ada
                if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
                    Storage::disk('local')->delete($material->file_path);
                }

                $file                  = $request->file('file');
                $data['file_path']     = $file->store('materials', 'local');
                $data['original_name'] = $file->getClientOriginalName();
                $data['file_size']     = $file->getSize();
                $data['mime_type']     = $file->getMimeType();
            }
        } else {
            // Pengalihan ke tautan eksternal: bersihkan berkas di disk privat
            if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
                Storage::disk('local')->delete($material->file_path);
            }
            $data['file_path']     = null;
            $data['original_name'] = null;
            $data['file_size']     = null;
            $data['mime_type']     = null;
            $data['external_url']  = $request->external_url;
        }

        $material->update($data);

        return redirect()
            ->route(auth()->user()->role . '.materials.show', $material)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    /**
     * Hapus materi beserta berkas fisiknya di disk privat.
     * MaterialPolicy@delete: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa ❌
     */
    public function destroy(Material $material)
    {
        $material->loadMissing('course');

        Gate::authorize('delete', $material);

        if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
            Storage::disk('local')->delete($material->file_path);
        }

        $course = $material->course;
        $material->delete();

        return redirect()
            ->route(auth()->user()->role . '.courses.materials.index', $course)
            ->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * Unduh berkas materi dari disk privat.
     * Menggunakan otorisasi MaterialPolicy@download (atau @view):
     * - Mahasiswa yang terdaftar pada MK: Boleh ✅
     * - Mahasiswa di luar MK: Ditolak (403 Forbidden) ❌
     * - Dosen pengampu MK: Boleh ✅
     * - Dosen pengampu MK lain: Ditolak (403 Forbidden) ❌
     * - Admin: Boleh ✅
     * Berkas disimpan di disk privat (storage/app/private), tidak pernah lewat URL publik.
     */
    public function download(Material $material)
    {
        $material->loadMissing('course');

        Gate::authorize('download', $material);

        abort_unless(
            $material->type === 'file' && $material->file_path && Storage::disk('local')->exists($material->file_path),
            404,
            'Berkas materi tidak ditemukan di penyimpanan server.'
        );

        return Storage::disk('local')->download($material->file_path, $material->original_name);
    }
}