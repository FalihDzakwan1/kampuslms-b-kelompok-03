<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            'materials' => $course->materials,
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
     * MaterialPolicy@create: sama seperti create().
     */
    public function store(Request $request, Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);

        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:file,link',
            'file'         => 'nullable|file|max:10240',
            'external_url' => 'nullable|url|max:255',
        ]);

        $data = [
            'course_id'    => $course->id,
            'uploaded_by'  => auth()->id(),
            'title'        => $request->title,
            'description'  => $request->description ?? '',
            'type'         => $request->type,
            'external_url' => $request->external_url,
        ];

        if ($request->hasFile('file') && $request->type === 'file') {
            $file                  = $request->file('file');
            $data['file_path']     = $file->store('materials');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size']     = $file->getSize();
            $data['mime_type']     = $file->getMimeType();
        }

        $course->materials()->create($data);

        return redirect()
            ->route('dosen.courses.materials.index', $course)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Detail materi (juga dipakai sebagai otorisasi tombol Unduh).
     * MaterialPolicy@view: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa terdaftar ✅
     *
     * Eager-load 'course' terlebih dahulu agar Policy tidak memicu N+1.
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
     * MaterialPolicy@update: sama seperti edit().
     */
    public function update(Request $request, Material $material)
    {
        $material->loadMissing('course');

        Gate::authorize('update', $material);

        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:file,link',
            'file'         => 'nullable|file|max:10240',
            'external_url' => 'nullable|url|max:255',
        ]);

        $data = [
            'title'        => $request->title,
            'description'  => $request->description ?? '',
            'type'         => $request->type,
            'external_url' => $request->external_url,
        ];

        if ($request->hasFile('file') && $request->type === 'file') {
            $file                  = $request->file('file');
            $data['file_path']     = $file->store('materials');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size']     = $file->getSize();
            $data['mime_type']     = $file->getMimeType();
        }

        $material->update($data);

        return redirect()
            ->route('dosen.materials.show', $material)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    /**
     * Hapus materi.
     * MaterialPolicy@delete: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa ❌
     */
    public function destroy(Material $material)
    {
        $material->loadMissing('course');

        Gate::authorize('delete', $material);

        $course = $material->course;
        $material->delete();

        return redirect()
            ->route('dosen.courses.materials.index', $course)
            ->with('success', 'Materi berhasil dihapus.');
    }
}