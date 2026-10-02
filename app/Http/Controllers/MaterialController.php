<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Course;
use Illuminate\Http\Request;


class MaterialController extends Controller
{


    public function index(Course $course)
    {
        // TODO: Akan direfaktor menjadi MaterialPolicy@viewAny di minggu 7
        $authUser = auth()->user();
        abort_unless(
            $authUser->role === 'admin'
            || $course->lecturer_id === $authUser->id
            || $course->students()->where('users.id', $authUser->id)->exists(),
            403,
            'Anda tidak memiliki akses ke materi mata kuliah ini.'
        );

        return view('materials.index', [
            'course' => $course,
            'materials' => $course->materials
        ]);
    }

    public function create(Course $course)
    {
        abort_unless(
            $course->lecturer_id === auth()->id(),
            403,
            'Hanya dosen pengampu yang dapat menambah materi.'
        );

        return view('materials.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        abort_unless(
            $course->lecturer_id === auth()->id(),
            403,
            'Hanya dosen pengampu yang dapat menambah materi.'
        );

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
            $file = $request->file('file');
            $data['file_path']     = $file->store('materials');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size']     = $file->getSize();
            $data['mime_type']     = $file->getMimeType();
        }

        $course->materials()->create($data);

        return redirect()->route('dosen.courses.materials.index', $course)
                         ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function show(Material $material)
    {
        // TODO: Akan direfaktor menjadi MaterialPolicy@view di minggu 7
        $authUser = auth()->user();
        abort_unless(
            $authUser->role === 'admin'
            || $material->course->lecturer_id === $authUser->id
            || $material->course->students()->where('users.id', $authUser->id)->exists(),
            403,
            'Anda tidak memiliki akses ke materi ini.'
        );

        $material->load(['uploader', 'course']);

        return view('materials.show', compact('material'));
    }

    public function edit(Material $material)
    {
        abort_unless(
            $material->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki akses untuk mengedit materi ini.'
        );

        return view('materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        abort_unless(
            $material->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki hak untuk mengubah materi ini.'
        );

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
            $file = $request->file('file');
            $data['file_path']     = $file->store('materials');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size']     = $file->getSize();
            $data['mime_type']     = $file->getMimeType();
        }

        $material->update($data);

        return redirect()->route('dosen.materials.show', $material)
                         ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material)
    {
        abort_unless(
            $material->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki hak untuk menghapus materi ini.'
        );

        $course = $material->course;
        $material->delete();

        return redirect()->route('dosen.courses.materials.index', $course)
                         ->with('success', 'Materi berhasil dihapus.');
    }
}