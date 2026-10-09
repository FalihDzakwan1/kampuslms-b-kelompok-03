<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    /**
     * Daftar tugas dalam satu MK.
     * AssignmentPolicy@viewAny: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa terdaftar ✅
     */

    public function index(Course $course)
    {
        Gate::authorize('viewAny', [Assignment::class, $course]);

        return view('assignments.index', [
            'course'        => $course,
            'assignments'   => $course->assignments,
        ]);
    }

    /**
     * Form tambah tugas.
     * AssignmentPolicy@create: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa ❌
     */

    public function create(Course $course)
    {
        Gate::authorize('create', [Assignment::class, $course]);

        return view('assignments.create', compact('course'));
    }

    /**
     * Simpan tugas baru.
     * AssignmentPolicy@create: sama seperti create().
     */

    public function store(Request $request, Course $course)
    {
        Gate::authorize('create', [Assignment::class, $course]);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'instructions' => 'required|string',
            'due_at'       => 'required|date',
            'max_score'    => 'nullable|integer|between:1,100',
            'status'       => 'required|in:draft,published',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['max_score']  = $validated['max_score'] ?? 100;

        $course->assignments()->create($validated);

        return redirect()
            ->route('dosen.courses.assignments.index', $course)
            ->with('success', 'Tugas berhasil ditambahkan.');
    }


    /**
     * Detail satu tugas.
     * AssignmentPolicy@view: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa terdaftar ✅
     *
     * Eager-load 'course' terlebih dahulu agar Policy tidak memicu N+1.
     */
    public function show(Assignment $assignment)
    {
        $assignment->loadMissing('course');

        Gate::authorize('view', $assignment);

        $assignment->load(['course.lecturer', 'submissions.student', 'submissions.grade']); 
    
    
    return view('assignments.show', compact('assignment'));
    }

    /**
     * Form edit tugas.
     * AssignmentPolicy@update: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa ❌
     */
    public function edit(Assignment $assignment)
    {
        $assignment->loadMissing('course');

        Gate::authorize('update', $assignment);

        return view('assignments.edit', compact('assignment'));
    }

    /**
     * Simpan perubahan tugas.
     * AssignmentPolicy@update: sama seperti edit().
     */
    public function update(Request $request, Assignment $assignment)
    {
        $assignment->loadMissing('course');

        Gate::authorize('update', $assignment);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'instructions' => 'required|string',
            'due_at'       => 'required|date',
            'max_score'    => 'nullable|integer|between:1,100',
            'status'       => 'required|in:draft,published',
        ]);

        $assignment->update($validated);

        return redirect()
            ->route('dosen.assignments.show', $assignment)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Hapus tugas.
     * AssignmentPolicy@delete: Admin ✅ | Dosen MK sendiri ✅ | Mahasiswa ❌
     */
    public function destroy(Assignment $assignment)
    {
        $assignment->loadMissing('course');

        Gate::authorize('delete', $assignment);

        $course = $assignment->course;
        $assignment->delete();

        return redirect()
            ->route('dosen.courses.assignments.index', $course)
            ->with('success', 'Tugas berhasil dihapus.');
    }
}