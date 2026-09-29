<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;


class AssignmentController extends Controller
{


    public function index(Course $course)
    {
        // TODO: Akan direfaktor menjadi AssignmentPolicy@viewAny di minggu 7
        $authUser = auth()->user();
        abort_unless(
            $authUser->role === 'admin'
            || $course->lecturer_id === $authUser->id
            || $course->students()->where('users.id', $authUser->id)->exists(),
            403,
            'Anda tidak memiliki akses ke daftar tugas mata kuliah ini.'
        );

        return view('assignments.index', [
            'course' => $course,
            'assignments' => $course->assignments
        ]);
    }

    public function create(Course $course)
    {
        abort_unless(
            $course->lecturer_id === auth()->id(),
            403,
            'Hanya dosen pengampu yang dapat membuat tugas.'
        );

        return view('assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        abort_unless(
            $course->lecturer_id === auth()->id(),
            403,
            'Hanya dosen pengampu yang dapat membuat tugas.'
        );

        $course->assignments()->create(
            $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date'
            ])
        );

        return back()->with('success', 'Tugas berhasil ditambahkan.');
    }




    public function edit(Assignment $assignment)
    {
        abort_unless(
            $assignment->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki akses untuk mengedit tugas ini.'
        );

        return view('assignments.edit', compact('assignment'));
    }

    public function show(Assignment $assignment)
    {
        // TODO: Akan direfaktor menjadi AssignmentPolicy@view di minggu 7
        $authUser = auth()->user();
        abort_unless(
            $authUser->role === 'admin'
            || $assignment->course->lecturer_id === $authUser->id
            || $assignment->course->students()->where('users.id', $authUser->id)->exists(),
            403,
            'Anda tidak memiliki akses ke tugas ini.'
        );

        return view('assignments.show', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        abort_unless(
            $assignment->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki hak untuk mengubah tugas ini.'
        );

        $assignment->update(
            $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date'
            ])
        );

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment)
    {
        abort_unless(
            $assignment->course->lecturer_id === auth()->id(),
            403,
            'Anda tidak memiliki hak untuk menghapus tugas ini.'
        );

        $assignment->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}