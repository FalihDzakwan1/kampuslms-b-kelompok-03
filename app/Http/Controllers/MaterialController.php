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

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'file' => 'nullable|file'
        ]);

        if($request->hasFile('file')){
            $validated['file'] = $request->file('file')->store('materials');
        }

        $course->materials()->create($validated);

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

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $material->update($validated);

        return back()->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material)
    {
        abort_unless(
            $material->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki hak untuk menghapus materi ini.'
        );

        $material->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}